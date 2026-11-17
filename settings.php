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
 * Site settings for the quiz live status report.
 *
 * Included by mod/quiz/settings.php, which provides $settings.
 *
 * @package   quiz_livestatus
 * @copyright 2026 Alberto Azzalini <alberto.azzalini@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configselect(
        'quiz_livestatus/barstyle',
        get_string('barstyle', 'quiz_livestatus'),
        get_string('barstyle_desc', 'quiz_livestatus'),
        'segments',
        [
            'segments' => get_string('barstyle_segments', 'quiz_livestatus'),
            'binary' => get_string('barstyle_binary', 'quiz_livestatus'),
        ]
    ));
    $settings->add(new admin_setting_configcheckbox(
        'quiz_livestatus/showdetails',
        get_string('showdetails', 'quiz_livestatus'),
        get_string('showdetails_desc', 'quiz_livestatus'),
        1
    ));
}
