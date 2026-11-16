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
 * @copyright 2026 wishmerhill
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Live status';
$string['livestatus'] = 'Live status';
$string['privacy:metadata'] = 'The quiz live status report only displays existing quiz attempt data and does not store any personal data.';

$string['autosavenote'] = 'Answers appear here once the student\'s browser has saved them: on page change or by autosave (every {$a}). Students do not see whether their answers are right; this only depends on the quiz behaviour (e.g. deferred feedback).';
$string['autosavedisabled'] = 'Quiz autosave is disabled: answers appear here only when the student changes page or submits.';
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
$string['lastupdate'] = 'Updated at {$a->time}, automatic refresh every {$a->seconds} seconds.';
$string['noattempts'] = 'No attempts in progress or finished yet.';
$string['refreshnow'] = 'Refresh now';
$string['state_finished'] = 'Finished';
$string['state_inprogress'] = 'In progress';
