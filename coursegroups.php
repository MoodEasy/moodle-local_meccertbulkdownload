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
 * Prints the groups of the given course.
 * 
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir.'/grouplib.php');

$courseid = required_param('cid', PARAM_INT);
$context = context_system::instance();

require_login();

if (!has_capability('local/meccertbulkdownload:searchcertificates', $context)) {
    die();
}

if ($courseid == 0) {
    echo json_encode(array());
    die();
}

$groups = groups_get_all_groups($courseid, 0, 0, 'g.id, g.name');

$filteredGroups = [];
foreach($groups as $group) {
    $tmpGroup = new stdClass;
    $tmpGroup->id = $group->id;
    $tmpGroup->name = $group->name;
    $filteredGroups[] = $tmpGroup;
}

echo json_encode($filteredGroups);