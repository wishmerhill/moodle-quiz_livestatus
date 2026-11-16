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
 * @copyright 2026 wishmerhill
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_quiz\local\reports\report_base;
use mod_quiz\quiz_attempt;

/**
 * The live status report class.
 */
class quiz_livestatus_report extends report_base {

    /** @var int seconds between automatic page refreshes. */
    const REFRESH_SECONDS = 10;

    /** @var float minimum fraction for an answer to count as correct. */
    const CORRECT_FRACTION = 0.99;

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

        $reporturl = new moodle_url('/mod/quiz/report.php', ['id' => $cm->id, 'mode' => 'livestatus']);
        $PAGE->requires->js_init_code(
            'setTimeout(function() { window.location.reload(); }, ' . (self::REFRESH_SECONDS * 1000) . ');'
        );

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
            [quiz_attempt::IN_PROGRESS, quiz_attempt::FINISHED], SQL_PARAMS_NAMED, 'state');
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
     * @return array counts: total, answered, correct, partial, wrong, unanswered, lastsaved.
     */
    protected function summarise_attempt(stdClass $attempt): array {
        $counts = ['total' => 0, 'answered' => 0, 'correct' => 0, 'partial' => 0, 'wrong' => 0,
                'unanswered' => 0, 'lastsaved' => (int) $attempt->timestart];

        $quba = \question_engine::load_questions_usage_by_activity($attempt->uniqueid);
        foreach ($quba->get_slots() as $slot) {
            $qa = $quba->get_question_attempt($slot);
            $question = $qa->get_question();

            // Descriptions and manually graded questions (e.g. essay) cannot be judged automatically.
            if (!$question instanceof \question_automatically_gradable || $qa->get_max_mark() <= 0) {
                continue;
            }
            $counts['total']++;

            // Includes the autosaved step, if any, so not-yet-submitted answers are visible.
            $response = $qa->get_last_qt_data();
            $counts['lastsaved'] = max($counts['lastsaved'], (int) $qa->get_last_step()->get_timecreated());

            if (empty($response) || !$question->is_gradable_response($response)) {
                $counts['unanswered']++;
                continue;
            }
            $counts['answered']++;

            try {
                [$fraction] = $question->grade_response($response);
            } catch (\Throwable $e) {
                debugging('quiz_livestatus: cannot grade slot ' . $slot . ' of attempt ' . $attempt->id .
                        ': ' . $e->getMessage(), DEBUG_DEVELOPER);
                continue;
            }

            if ($fraction >= self::CORRECT_FRACTION) {
                $counts['correct']++;
            } else if ($fraction > 0) {
                $counts['partial']++;
            } else {
                $counts['wrong']++;
            }
        }
        return $counts;
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
        $table->attributes['class'] = 'generaltable table table-striped table-hover table-sm';
        $table->head = array_map(fn($k) => get_string($k, 'quiz_livestatus'), [
            'col_student', 'col_attempt', 'col_state', 'col_answered', 'col_correct', 'col_partial',
            'col_wrong', 'col_unanswered', 'col_percentage', 'col_lastactivity',
        ]);
        $table->align = ['left', 'center', 'left', 'center', 'center', 'center', 'center', 'center', 'right', 'left'];

        foreach ($attempts as $attempt) {
            $c = $this->summarise_attempt($attempt);
            $percentage = $c['total'] ? round(100 * $c['correct'] / $c['total']) : 0;

            $inprogress = $attempt->state === quiz_attempt::IN_PROGRESS;
            $state = html_writer::span(
                get_string($inprogress ? 'state_inprogress' : 'state_finished', 'quiz_livestatus'),
                'badge ' . ($inprogress ? 'bg-warning text-dark' : 'bg-success'));

            $name = html_writer::link(
                new moodle_url('/user/view.php', ['id' => $attempt->userid, 'course' => $course->id]),
                fullname($attempt));
            $review = html_writer::link(
                new moodle_url('/mod/quiz/review.php', ['attempt' => $attempt->id]), $attempt->attempt);

            $table->data[] = [
                $name,
                $review,
                $state,
                $c['answered'] . ' / ' . $c['total'],
                html_writer::span($c['correct'], 'text-success fw-bold'),
                $c['partial'],
                html_writer::span($c['wrong'], 'text-danger fw-bold'),
                $c['unanswered'],
                $percentage . '%',
                userdate($c['lastsaved'], get_string('strftimedatetimeshort', 'langconfig')),
            ];
        }
        return $table;
    }
}
