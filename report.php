<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Quiz live status report: shows, while students are still working, how many
 * of their saved answers are right or wrong.
 *
 * Nothing is written back to the attempts: responses are only re-graded in memory,
 * so the quiz behaviour (e.g. deferred feedback) is unaffected for the students.
 *
 * @package   quiz_livestatus
 * @copyright 2026 Alberto Azzalini <alberto.azzalini@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_quiz\local\reports\report_base;
use mod_quiz\quiz_attempt;

/**
 * The live status report class.
 */
class quiz_livestatus_report extends report_base {
    /** @var int seconds between automatic page refreshes. */
    public const REFRESH_SECONDS = 10;

    /** @var float minimum fraction for an answer to count as correct. */
    public const CORRECT_FRACTION = 0.99;

    /** @var string progress bar with one segment per result. */
    public const BAR_SEGMENTS = 'segments';

    /** @var string progress bar in a single colour: good or bad. */
    public const BAR_BINARY = 'binary';

    /** @var string[] symbol shown in the details for each result. */
    public const SYMBOLS = ['correct' => '✔', 'partial' => '~', 'wrong' => '✘', 'unanswered' => '·', 'error' => '?'];

    /** @var string progress bar style, one of the BAR_ constants. */
    protected $barstyle = self::BAR_SEGMENTS;

    /** @var bool whether rows can be expanded to show the result of each question. */
    protected $showdetails = true;

    /**
     * Display the report.
     *
     * @param stdClass $quiz the quiz settings.
     * @param stdClass $cm the course_module information.
     * @param stdClass $course the course settings.
     * @return bool true on success.
     */
    public function display($quiz, $cm, $course) {
        global $OUTPUT, $PAGE;

        $context = context_module::instance($cm->id);
        require_capability('mod/quiz:viewreports', $context);

        $config = get_config('quiz_livestatus');
        if (($config->barstyle ?? '') === self::BAR_BINARY) {
            $this->barstyle = self::BAR_BINARY;
        }
        $this->showdetails = !isset($config->showdetails) || !empty($config->showdetails);

        $reporturl = new moodle_url('/mod/quiz/report.php', ['id' => $cm->id, 'mode' => 'livestatus']);
        $PAGE->requires->js_call_amd('quiz_livestatus/refresh', 'init', [(int) $cm->id, self::REFRESH_SECONDS]);

        $this->print_header_and_tabs($cm, $course, $quiz, 'livestatus');

        $currentgroup = $this->get_current_group($cm, $course, $context);
        groups_print_activity_menu($cm, $reporturl);

        echo html_writer::start_div('d-flex flex-wrap align-items-center gap-3 my-3');
        echo $OUTPUT->single_button($reporturl, get_string('refreshnow', 'quiz_livestatus'), 'get');
        echo html_writer::span(get_string('lastupdate', 'quiz_livestatus', (object) [
            'time' => userdate(time(), get_string('strftimetime', 'langconfig')),
            'seconds' => self::REFRESH_SECONDS,
        ]), 'text-muted');
        echo html_writer::end_div();

        $autosave = (int) get_config('quiz', 'autosaveperiod');
        echo $OUTPUT->notification($autosave > 0
            ? get_string('autosavenote', 'quiz_livestatus', format_time($autosave))
            : get_string('autosavedisabled', 'quiz_livestatus'), 'info', false);

        if ($currentgroup == self::NO_GROUPS_ALLOWED) {
            echo $OUTPUT->notification(get_string('notingroup'));
            echo $OUTPUT->footer();
            return true;
        }

        $attempts = $this->get_attempts($quiz, $currentgroup);
        if (!$attempts) {
            echo $OUTPUT->notification(get_string('noattempts', 'quiz_livestatus'), 'info', false);
            echo $OUTPUT->footer();
            return true;
        }

        echo html_writer::table($this->build_table($attempts, $course));
        echo $OUTPUT->footer();
        return true;
    }

    /**
     * Load the in-progress and finished attempts (no previews) with user name fields.
     *
     * @param stdClass $quiz the quiz settings.
     * @param int $currentgroup group to restrict to, 0 for everyone.
     * @return stdClass[] the attempts.
     */
    protected function get_attempts($quiz, int $currentgroup): array {
        global $DB;

        $userfields = \core_user\fields::for_name()->get_sql('u', true);
        [$statesql, $stateparams] = $DB->get_in_or_equal(
            [quiz_attempt::IN_PROGRESS, quiz_attempt::FINISHED],
            SQL_PARAMS_NAMED,
            'state'
        );
        $params = ['quizid' => $quiz->id] + $stateparams + $userfields->params;

        $groupjoin = '';
        if ($currentgroup > 0) {
            $groupjoin = 'JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = :groupid';
            $params['groupid'] = $currentgroup;
        }

        $sql = "SELECT qa.id, qa.uniqueid, qa.userid, qa.attempt, qa.state, qa.timestart {$userfields->selects}
                  FROM {quiz_attempts} qa
                  JOIN {user} u ON u.id = qa.userid
                  {$groupjoin}
                 WHERE qa.quiz = :quizid
                   AND qa.preview = 0
                   AND qa.state {$statesql}
              ORDER BY u.lastname, u.firstname, qa.attempt";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Re-grade, in memory only, the last saved response of every question of an attempt.
     *
     * @param stdClass $attempt the attempt row.
     * @return array counts: total, answered, correct, partial, wrong, unanswered, lastsaved,
     *      plus 'slots': list of ['number' => displayed question number, 'result' => key of SYMBOLS].
     */
    protected function summarise_attempt(stdClass $attempt): array {
        $counts = ['total' => 0, 'answered' => 0, 'correct' => 0, 'partial' => 0, 'wrong' => 0,
                'unanswered' => 0, 'lastsaved' => (int) $attempt->timestart, 'slots' => []];

        $attemptobj = quiz_attempt::create($attempt->id);
        foreach ($attemptobj->get_slots() as $slot) {
            $qa = $attemptobj->get_question_attempt($slot);
            $question = $qa->get_question();

            // Descriptions and manually graded questions (e.g. essay) cannot be judged automatically.
            if (!$question instanceof \question_automatically_gradable || $qa->get_max_mark() <= 0) {
                continue;
            }
            $counts['total']++;
            $result = $this->grade_slot($qa, $attempt, $slot);
            if ($result !== 'unanswered') {
                $counts['answered']++;
            }
            if ($result !== 'error') {
                $counts[$result]++;
            }
            $counts['slots'][] = ['number' => $attemptobj->get_question_number($slot), 'result' => $result];

            // Includes the autosaved step, if any, so not-yet-submitted answers are visible.
            $counts['lastsaved'] = max($counts['lastsaved'], (int) $qa->get_last_step()->get_timecreated());
        }
        return $counts;
    }

    /**
     * Grade, in memory only, the last saved response of one question.
     *
     * @param question_attempt $qa the question attempt.
     * @param stdClass $attempt the attempt row, for error messages.
     * @param int $slot the slot, for error messages.
     * @return string correct, partial, wrong, unanswered or error.
     */
    protected function grade_slot(\question_attempt $qa, stdClass $attempt, int $slot): string {
        $question = $qa->get_question();
        // Includes the autosaved step, if any.
        $response = $qa->get_last_qt_data();
        // Variables starting with '_' are set by the question itself when the attempt starts (e.g. the shuffled
        // order): they are not an answer. Some types (e.g. ordering) treat any response as gradable, so without
        // this check a question the student has not touched yet would be graded on its initial state.
        $given = array_filter(array_keys($response), fn($name) => !str_starts_with($name, '_'));
        if (!$given || !$question->is_gradable_response($response)) {
            return 'unanswered';
        }
        try {
            [$fraction] = $question->grade_response($response);
        } catch (\Throwable $e) {
            debugging('quiz_livestatus: cannot grade slot ' . $slot . ' of attempt ' . $attempt->id .
                    ': ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 'error';
        }
        if ($fraction >= self::CORRECT_FRACTION) {
            return 'correct';
        }
        return $fraction > 0 ? 'partial' : 'wrong';
    }

    /**
     * Render the coloured progress bar with the "answered / total" text.
     *
     * Widths are fractions of all gradable questions; the grey background is what is still unanswered.
     *
     * @param array $c the counts from summarise_attempt().
     * @return string HTML.
     */
    protected function render_progress_bar(array $c): string {
        $width = fn(int $n): string => 'width: ' . ($c['total'] ? round(100 * $n / $c['total'], 2) : 0) . '%';

        $segments = '';
        if ($this->barstyle === self::BAR_BINARY) {
            $graded = $c['correct'] + $c['partial'] + $c['wrong'];
            if ($graded) {
                $good = ($c['correct'] + 0.5 * $c['partial']) / $graded > 0.5;
                $segments = html_writer::span('', $good ? 'qls-good' : 'qls-bad', ['style' => $width($graded)]);
            }
        } else {
            foreach (['correct', 'partial', 'wrong'] as $result) {
                if ($c[$result]) {
                    $segments .= html_writer::span('', 'qls-' . $result, ['style' => $width($c[$result])]);
                }
            }
        }

        $summary = get_string('bar_summary', 'quiz_livestatus', (object) $c);
        return html_writer::div(
            html_writer::div($segments, 'qls-bar', ['role' => 'img', 'aria-label' => $summary, 'title' => $summary]) .
            html_writer::span($c['answered'] . ' / ' . $c['total'], 'qls-count'),
            'qls-progress'
        );
    }

    /**
     * Render the result of each question as small badges, e.g. "3 ✔".
     *
     * @param array $slots the 'slots' list from summarise_attempt().
     * @return string HTML.
     */
    protected function render_details(array $slots): string {
        $badges = '';
        foreach ($slots as $s) {
            $label = get_string('detail_question', 'quiz_livestatus', (object) [
                'number' => $s['number'],
                'result' => get_string('detail_' . $s['result'], 'quiz_livestatus'),
            ]);
            $badges .= html_writer::span(
                s($s['number']) . ' ' . html_writer::span(self::SYMBOLS[$s['result']], '', ['aria-hidden' => 'true']) .
                html_writer::span($label, 'visually-hidden'),
                'qls-badge qls-' . $s['result'],
                ['title' => $label]
            );
        }
        return html_writer::div($badges, 'qls-badges');
    }

    /**
     * Build the Bootstrap table.
     *
     * @param stdClass[] $attempts the attempts.
     * @param stdClass $course the course.
     * @return html_table the table.
     */
    protected function build_table(array $attempts, $course): html_table {
        $table = new html_table();
        // No table-striped: the hidden detail rows would break the alternation.
        $table->attributes['class'] = 'generaltable table table-hover table-sm quiz-livestatus-table';
        $table->head = array_map(fn($k) => get_string($k, 'quiz_livestatus'), [
            'col_student', 'col_attempt', 'col_state', 'col_answered', 'col_correct', 'col_partial',
            'col_wrong', 'col_unanswered', 'col_percentage', 'col_lastactivity',
        ]);
        $table->align = ['left', 'center', 'left', 'left', 'center', 'center', 'center', 'center', 'right', 'left'];

        foreach ($attempts as $attempt) {
            $c = $this->summarise_attempt($attempt);
            $percentage = $c['total'] ? round(100 * $c['correct'] / $c['total']) : 0;

            $inprogress = $attempt->state === quiz_attempt::IN_PROGRESS;
            $state = html_writer::span(
                get_string($inprogress ? 'state_inprogress' : 'state_finished', 'quiz_livestatus'),
                'badge ' . ($inprogress ? 'bg-warning text-dark' : 'bg-success')
            );

            $name = html_writer::link(
                new moodle_url('/user/view.php', ['id' => $attempt->userid, 'course' => $course->id]),
                fullname($attempt)
            );
            if ($this->showdetails) {
                $name = html_writer::span('▸', 'qls-caret', ['aria-hidden' => 'true']) . ' ' . $name;
            }
            $review = html_writer::link(
                new moodle_url('/mod/quiz/review.php', ['attempt' => $attempt->id]),
                $attempt->attempt
            );

            $row = new html_table_row([
                $name,
                $review,
                $state,
                $this->render_progress_bar($c),
                html_writer::span($c['correct'], 'text-success fw-bold'),
                $c['partial'],
                html_writer::span($c['wrong'], 'text-danger fw-bold'),
                $c['unanswered'],
                $percentage . '%',
                userdate($c['lastsaved'], get_string('strftimedatetimeshort', 'langconfig')),
            ]);
            if (!$this->showdetails) {
                $table->data[] = $row;
                continue;
            }

            $row->attributes = [
                'class' => 'qls-toggle',
                'data-attemptid' => $attempt->id,
                'tabindex' => '0',
                'aria-expanded' => 'false',
                'title' => get_string('toggle_details', 'quiz_livestatus'),
            ];
            $table->data[] = $row;

            $cell = new html_table_cell($this->render_details($c['slots']));
            $cell->colspan = count($table->head);
            $detail = new html_table_row([$cell]);
            $detail->attributes = ['class' => 'qls-detail d-none', 'data-attemptid' => $attempt->id];
            $table->data[] = $detail;
        }
        return $table;
    }
}
