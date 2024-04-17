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
 * @package    local_meccertbulkdownload
 * @author     MoodEasy.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \local_meccertbulkdownload\meccertbulkdownload;
use \local_meccertbulkdownload\form\filters_form;
use \local_meccertbulkdownload\form\filters_hidden_form;

require('../../config.php');
require_once('lib.php');
require_once('../../cohort/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/setuplib.php');
require_once($CFG->libdir.'/grouplib.php');

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/meccertbulkdownload/index.php');
$PAGE->set_title(get_string('pluginname', 'local_meccertbulkdownload'));
$PAGE->set_heading(get_string('pluginname', 'local_meccertbulkdownload'));

// must be after $PAGE->set_url()
require_login();

if (!has_capability('mod/customcert:viewallcertificates', $context)) {
    die();
}

$PAGE->requires->js_call_amd('local_meccertbulkdownload/coursegroups_selector', 'init');

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 25, PARAM_INT);
$submit = optional_param('submitbuttonn', '', PARAM_TEXT);


// PREPARE THE FORM FOR SELECTION OF FILTERS

$corsi = ['no' => get_string('all', 'local_meccertbulkdownload')];
foreach(get_courses("all", "c.sortorder ASC", "c.id, c.fullname") as $corso) {
    $corsi[$corso->id] = $corso->fullname;
}
asort($corsi);

$coorti = ['no' => get_string('all', 'local_meccertbulkdownload')];
$cohorts = cohort_get_all_cohorts(0, 10000);
if ($cohorts) {
    foreach($cohorts['cohorts'] as $coorte) {
        $coorti[$coorte->id] = $coorte->name;
    }
}
// asort($coorti);

$gruppocorso = ['no' => get_string('all', 'local_meccertbulkdownload')];
$courseid = optional_param('corso', 'no', PARAM_RAW);
if ($submit && $courseid !== 'no') {
    $groups = groups_get_all_groups($courseid, 0, 0, 'g.id, g.name');
    foreach($groups as $group) {
        $gruppocorso[$group->id] = $group->name;
    }
}

$fform = new filters_form(null, [
    'corsi' => $corsi,
    'coorti' => $coorti,
    'gruppocorso' => $gruppocorso
]);


// comes from this same page after submitting the form
// or after clicking on the page link in the pagination bar
if ( ($fromform = $fform->get_data()) || $submit) {

    // comes from clicking on the page number in the pagination bar:
    // no POST data of the form but same data in query string
    if (!$fromform) {
        $fromform = new stdClass;
        $fromform->courseorcertificate = optional_param('courseorcertificate', null, PARAM_RAW);
        $fromform->datefrom = optional_param('datefrom', null, PARAM_RAW);
        $fromform->dateto = optional_param('dateto', null, PARAM_RAW);
        $fromform->submitbuttonn = $submit;
    }

    // obtains parameters from the form and creates the where part of the query
    $where = meccertbulkdownload::get_certificates_params($fromform);

    // obtains the total number of records (without LIMITS) useful for pagination
    $recsCountObj = $DB->get_record_sql(
        meccertbulkdownload::get_certificates_query(true)
            . $where['string'],
        $where['params']
    );
    $recsCount = isset($recsCountObj->quanti) ? $recsCountObj->quanti : 0;

    // obtains the query, adds the where part and executes it
    $recs = $DB->get_recordset_sql(
        meccertbulkdownload::get_certificates_query()
            . $where['string']
            . " LIMIT " . $perpage
            . " OFFSET " . ($page * $perpage),
        $where['params']
    );

    // https://github.com/moodle/moodle/blob/master/lib/outputcomponents.php
    $table = new html_table();
    $table->align = array('left', 'left', 'left', 'left', 'right', 'right');
    $table->head = meccertbulkdownload::get_certificates_fields();
    $i = 0;

    // if there are results...
    if ($recs->valid()) {
        foreach ($recs as $cert) {

            if ($cert->certcreation) {
                $certcreationTmp = new DateTime('', core_date::get_user_timezone_object());
                $certcreationTmp->setTimestamp($cert->certcreation);
                $certcreationTmp = userdate($certcreationTmp->getTimestamp(), get_string('strftimedatetimeshort', 'core_langconfig'));
            } else {
                $certcreationTmp = "";
            }

            if ($cert->coursecompletion) {
                $coursecompletionTmp = new DateTime('', core_date::get_user_timezone_object());
                $coursecompletionTmp->setTimestamp($cert->coursecompletion);
                $coursecompletionTmp = userdate($coursecompletionTmp->getTimestamp(), get_string('strftimedatetimeshort', 'core_langconfig'));
            } else {
                $coursecompletionTmp = "";
            }

            $table->data[$i][0] = $cert->username;
            $table->data[$i][1] = $cert->firstname . " " . $cert->lastname;
            $table->data[$i][2] = $cert->cohortname;
            $table->data[$i][3] = $cert->coursename;
            $table->data[$i][4] = $certcreationTmp;
            $table->data[$i][5] = $coursecompletionTmp;
            $i++;
        }
    } else {
        $table->align = array('center');
        $table->head = [""];
        $table->data[0][0] = '<p style="margin: 30px auto">' . get_string('nocertificatesfound', 'local_meccertbulkdownload') . '</p>';
    }

    $recs->close();

    // puts the data back into the form so that the values decided by the user
    // always remain selected; the user then clicks the other submit button to
    // go to the page for selecting templates and booking the task
    $fform->set_data($fromform);
}

// prepare parameters for pagination bar
$params = array('page' => $page, 'perpage' => $perpage);
if ($fromform) $params = array_merge((array) $fromform, $params);
$baseurl = new moodle_url('/local/meccertbulkdownload/index.php', $params);

// prepare parameters for selecting how many per page
$params = array('page' => 0);
if ($fromform) $params = array_merge((array) $fromform, $params);
$baseurl2 = new moodle_url('/local/meccertbulkdownload/index.php', $params);


// =============================================================================


echo $OUTPUT->header();

echo '
<script>
    window.alltxt = "' . get_string('all', 'local_meccertbulkdownload') . '";
</script>
';

echo '
<style>
    nav.pagination {
        justify-content: right!important;
    }
</style>
';

echo '
<div>&nbsp;</div>
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="index.php">' . get_string('packscreate', 'local_meccertbulkdownload') . '</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="list.php">' . get_string('packsdownload', 'local_meccertbulkdownload') . '</a>
    </li>
</ul>';

$fform->display();

if (isset($table)) {

    $from = ($perpage * $page) + 1;
    $to = ($perpage * $page) + $perpage;
    if ($to > $recsCount) $to = $recsCount;
    if ($recsCount == 0) $from = 0;

    $fhform = new filters_hidden_form('seltemplates.php', [
        'courseorcertificate' => $fromform->courseorcertificate,
        'datefrom' => $fromform->datefrom,
        'dateto' => $fromform->dateto,
        'estimatedarchivesize' => meccertbulkdownload::get_estimatedarchivesize($recsCount)
    ]);

    // draw the table
    echo '<div style="text-align: center; margin-top: -10px;">';

        // if there is data in the table, display the zip creation button
        if ($recsCount > 0) {
            echo '<div style="float: right;">';
            $fhform->set_display_vertical();
            $fhform->display();
            echo '</div>';
        } else {
            echo '<div style="height: 2rem;">&nbsp;</div>';
        }

        echo html_writer::table($table);
        echo '<div style="display: table; width: 100%; margin-top: 8px;">';
            echo '<div style="display: table-cell; text-align: left;">';
                echo str_replace(
                    ['{{from}}', '{{to}}', '{{count}}'],
                    [$from, $to, $recsCount],
                    get_string('tablerecordscount', 'local_meccertbulkdownload')
                );
                echo '<select class="custom-select" onChange="window.location.href=\'' . $baseurl2 . '&perpage=\' + this.value">
                    <option value="10"' . ($perpage == 10 ? ' selected' : '') . '>10</option>
                    <option value="25"' . ($perpage == 25 ? ' selected' : '') . '>25</option>
                    <option value="50"' . ($perpage == 50 ? ' selected' : '') . '>50</option>
                    <option value="100"' . ($perpage == 100 ? ' selected' : '') . '>100</option>
                </select>';
            echo '</div>';
            echo '<div style="display: table-cell; text-align: right; justify-content: right !important;">';
                echo $OUTPUT->paging_bar($recsCount, $page, $perpage, $baseurl);
            echo "</div>";
        echo "</div>";
    echo "</div>";
    echo '<div style="text-align: center">';
        echo '<div style="display: inline-block;">';

            // inserts function to download table as CSV, Excel, etc.
            // https://docs.moodle.org/dev/Data_formats
            echo $OUTPUT->download_dataformat_selector(
                get_string('download'),
                'download.php',
                'dataformat',
                ['fromform' => serialize($fromform)]
            );

        echo "</div>";
    echo "</div>";
    
}

echo $OUTPUT->footer();
