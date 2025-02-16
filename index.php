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
 * Allows searching for certificates and creating certificate archives.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_meccertbulkdownload\meccertbulkdownload;
use local_meccertbulkdownload\form\filters_form;
use local_meccertbulkdownload\form\filters_hidden_form;
use stdClass;

require(__DIR__ . '/../../config.php');
require_once('lib.php');
require_once(__DIR__ . '/../../cohort/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/setuplib.php');
require_once($CFG->libdir.'/grouplib.php');

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/meccertbulkdownload/index.php');
$PAGE->set_title(get_string('pluginname', 'local_meccertbulkdownload'));
$PAGE->set_heading(get_string('pluginname', 'local_meccertbulkdownload'));

require_login();

require_capability('local/meccertbulkdownload:searchcertificates', $context);

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 25, PARAM_INT);
$submit = optional_param('submitbuttonn', '', PARAM_TEXT);


// PREPARE THE FORM FOR SELECTION OF FILTERS.

$courses = ['no' => get_string('all', 'local_meccertbulkdownload')];
foreach (get_courses("all", "c.sortorder ASC", "c.id, c.fullname") as $course) {
    $courses[$course->id] = $course->fullname;
}
asort($courses);

$cohorts = ['no' => get_string('all', 'local_meccertbulkdownload')];
$cohortsfromdb = cohort_get_all_cohorts(0, 10000);
if ($cohortsfromdb) {
    foreach ($cohortsfromdb['cohorts'] as $cohort) {
        $cohorts[$cohort->id] = $cohort->name;
    }
}

$coursegroups = ['no' => get_string('all', 'local_meccertbulkdownload')];
$courseid = optional_param('corso', 'no', PARAM_RAW);
if ($submit && $courseid !== 'no') {
    $groups = groups_get_all_groups($courseid, 0, 0, 'g.id, g.name');
    foreach ($groups as $group) {
        $coursegroups[$group->id] = $group->name;
    }
}

$fform = new filters_form(null, [
    'courses' => $courses,
    'cohorts' => $cohorts,
    'coursegroups' => $coursegroups,
]);

$resultstable = null;
$resultstablematerial = null;
$fhform = null;
$output = $PAGE->get_renderer('local_meccertbulkdownload');


// Comes from this same page after submitting the form
// or after clicking on the page link in the pagination bar.
if ( ($fromform = $fform->get_data()) || $submit) {

    // Comes from clicking on the page number in the pagination bar:
    // no POST data of the form but same data in query string.
    if (!$fromform) {
        $fromform = new stdClass();
        $fromform->courseorcertificate = meccertbulkdownload::get_verified_courseorcertificate(false);
        $fromform->datefrom = optional_param('datefrom', null, PARAM_INT);
        $fromform->dateto = optional_param('dateto', null, PARAM_INT);
        $fromform->submitbuttonn = $submit;
    }

    // Obtains parameters from the form and creates the where part of the query.
    $where = meccertbulkdownload::get_certificates_params($fromform);

    // Obtains the total number of records (without LIMITS) useful for pagination.
    $resultscountobj = $DB->get_record_sql(
        meccertbulkdownload::get_certificates_query(true)
            . $where['string'],
        $where['params']
    );
    $resultscount = isset($resultscountobj->quanti) ? $resultscountobj->quanti : 0;

    // Obtains the query, adds the where part and executes it.
    $recs = $DB->get_recordset_sql(
        meccertbulkdownload::get_certificates_query()
            . $where['string']
            . " LIMIT " . $perpage
            . " OFFSET " . ($page * $perpage),
        $where['params']
    );
    $resultstable = $output->results_table($recs);
    $recs->close();

    // Puts the data back into the form so that the values decided by the user
    // always remain selected; the user then clicks the other submit button to
    // go to the page for selecting templates and booking the task.
    $fform->set_data($fromform);
}


// If the results table should be displayed.
if (isset($resultstable)) {

    // Prepare parameters for pagination bar.
    $params = ['page' => $page, 'perpage' => $perpage];
    if ($fromform) {
        $params = array_merge((array) $fromform, $params);
    }
    $baseurl = new moodle_url('/local/meccertbulkdownload/index.php', $params);

    // Prepare parameters for selecting how many per page.
    $params = ['page' => 0];
    if ($fromform) {
        $params = array_merge((array) $fromform, $params);
    }
    $baseurl2 = new moodle_url('/local/meccertbulkdownload/index.php', $params);

    // Prepare infos on records in the table
    $from = ($perpage * $page) + 1;
    $to = ($perpage * $page) + $perpage;
    if ($to > $resultscount) {
        $to = $resultscount;
    }
    if ($resultscount == 0) {
        $from = 0;
    }

    $fhform = new filters_hidden_form('seltemplates.php', [
        'courseorcertificate' => $fromform->courseorcertificate,
        'datefrom' => $fromform->datefrom,
        'dateto' => $fromform->dateto,
        'estimatedarchivesize' => meccertbulkdownload::get_estimatedarchivesize($resultscount),
    ]);

    $recordsstatus = str_replace(
        ['{{from}}', '{{to}}', '{{count}}'],
        [$from, $to, $resultscount],
        get_string('tablerecordscount', 'local_meccertbulkdownload')
    );

    $perpageoptions = [
        (object) ['value' => 2, 'selected' => ($perpage == 2 ? true : false)],
        (object) ['value' => 10, 'selected' => ($perpage == 10 ? true : false)],
        (object) ['value' => 25, 'selected' => ($perpage == 25 ? true : false)],
        (object) ['value' => 50, 'selected' => ($perpage == 50 ? true : false)],
        (object) ['value' => 100, 'selected' => ($perpage == 100 ? true : false)],
    ];

    $resultstablematerial = new stdClass();
    $resultstablematerial->resultscount = $resultscount;
    $resultstablematerial->page = $page;
    $resultstablematerial->perpage = $perpage;
    $resultstablematerial->baseurl = $baseurl;
    $resultstablematerial->baseurl2 = $baseurl2;
    $resultstablematerial->recordsstatus = $recordsstatus;
    $resultstablematerial->paginationurl = html_entity_decode($baseurl2->out() . '&perpage=');
    $resultstablematerial->perpageoptions = $perpageoptions;
}


echo $output->index_page($fform, $fromform, $fhform, $resultstable, $resultstablematerial);
