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
 * English strings for the quiz live status report.
 *
 * @package   quiz_livestatus
 * @copyright 2026 Alberto Azzalini <alberto.azzalini@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['autosavedisabled'] = 'Quiz autosave is disabled: answers appear here only when the student changes page or submits.';
$string['autosavenote'] = 'Answers appear here once the student\'s browser has saved them: on page change or by autosave (every {$a}). Students do not see whether their answers are right; this only depends on the quiz behaviour (e.g. deferred feedback).';
$string['bar_summary'] = 'Correct: {$a->correct}, partial: {$a->partial}, wrong: {$a->wrong}, not answered: {$a->unanswered}';
$string['barstyle'] = 'Progress bar colours';
$string['barstyle_binary'] = 'Single colour (green or red)';
$string['barstyle_desc'] = 'How the answered part of the progress bar is coloured. Segments: green for correct, yellow for partial, red for wrong answers. Single colour: green if more than half of the answers given so far are correct (a partial answer counts as half), red otherwise.';
$string['barstyle_segments'] = 'Segments (correct / partial / wrong)';
$string['col_answered'] = 'Answered';
$string['col_attempt'] = 'Attempt';
$string['col_correct'] = 'Correct';
$string['col_lastactivity'] = 'Last saved';
$string['col_partial'] = 'Partial';
$string['col_percentage'] = 'Percentage';
$string['col_state'] = 'State';
$string['col_student'] = 'Student';
$string['col_unanswered'] = 'Not answered';
$string['col_wrong'] = 'Wrong';
$string['detail_correct'] = 'correct';
$string['detail_error'] = 'cannot be graded';
$string['detail_partial'] = 'partially correct';
$string['detail_question'] = 'Question {$a->number}: {$a->result}';
$string['detail_unanswered'] = 'not answered';
$string['detail_wrong'] = 'wrong';
$string['lastupdate'] = 'Updated at {$a->time}, automatic refresh every {$a->seconds} seconds.';
$string['livestatus'] = 'Live status';
$string['livestatusreport'] = 'Live status';
$string['noattempts'] = 'No attempts in progress or finished yet.';
$string['pluginname'] = 'Live status';
$string['privacy:metadata'] = 'The quiz live status report only displays existing quiz attempt data and does not store any personal data.';

$string['refreshnow'] = 'Refresh now';
$string['showdetails'] = 'Show answers per question';
$string['showdetails_desc'] = 'Teachers can click a row to see which questions the student answered correctly or wrongly (only right/wrong, not the answers themselves).';
$string['state_finished'] = 'Finished';
$string['state_inprogress'] = 'In progress';
$string['toggle_details'] = 'Click to show or hide the result of each question';
