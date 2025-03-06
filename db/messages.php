<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines message providers (types of messages being sent).
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// See {@link https://docs.moodle.org/dev/Messaging_2.0}.
// See {@link https://docs.moodle.org/dev/Message_API}.
// See {@link https://github.com/moodle/moodle/blob/b5f4e0ce3dde78633696f892a0844574d43af4d0/lib/upgrade.txt#L224}.
$messageproviders = [

    // Confirms to the user that the compressed file with pdfs is ready (end of background task).
    'confirmation' => [
        'capability'  => 'local/meccertbulkdownload:notifyarchivecreated',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED,
            'anyotheroutput' => MESSAGE_PERMITTED,
        ],
    ],

];
