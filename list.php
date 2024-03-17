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

require('../../config.php');

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/meccertbulkdownload/list.php');
$PAGE->set_title(get_string('pluginname', 'local_meccertbulkdownload'));
$PAGE->set_heading(get_string('pluginname', 'local_meccertbulkdownload'));

require_login();

if (!has_capability('mod/customcert:viewallcertificates', $context)) {
    die();
}

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 25, PARAM_INT);

$fs = get_file_storage();



// ========= MANAGES THE POSSIBLE DELETION OF A COMPRESSED FILE ============


$action = optional_param('action', '', PARAM_ALPHA);
if ($action) {
    $actionid = required_param('aid', PARAM_INT);
}
$confirm = optional_param('confirm', 0, PARAM_INT);

// checks if it comes from the same page after clicking Delete related to a file
if ($action && $action === 'del') {
    // check if user has already seen the confirmation request
    if ($confirm) {  // confirmation seen
        if ($confirm == 1) {
            if ($actionid) {
                
                // after the operation it redirects to avoid problems if the user reloads the page
                $backurl = new moodle_url('/local/meccertbulkdownload/list.php');
                
                $file = $fs->get_file_by_id($actionid);
                if ($file->delete()) {
                    redirect($backurl, get_string('deletesuccess', 'local_meccertbulkdownload'), null, \core\output\notification::NOTIFY_SUCCESS);
                } else {
                    redirect($backurl, get_string('deleteerror', 'local_meccertbulkdownload'), null, \core\output\notification::NOTIFY_ERROR);
                }

            } else {  // between the parameters the id of the file to be deleted is not present: it does nothing and proceeds by displaying the page
                \core\notification::error(get_string('deletenoparam', 'local_meccertbulkdownload'));
            }
        } // if he cancelled, he doesn't do anything and proceeds to view the page
    } else {  // has yet to see the confirmation request
        $file = $fs->get_file_by_id($actionid);
        echo $OUTPUT->header();
        $nourl = new moodle_url('/local/meccertbulkdownload/list.php');
        $yesurl = new moodle_url('/local/meccertbulkdownload/list.php',
            array(
                'action' => 'del',
                'aid' => $actionid,
                'confirm' => 1,
            )
        );
        echo $OUTPUT->confirm(
            get_string('deleteconfirmmsg', 'local_meccertbulkdownload') . ' <br><br><strong>' . $file->get_filename() . '</strong>',
            $yesurl,
            $nourl
        );
        echo $OUTPUT->footer();
        exit();
    }
}


// =============================================================================



// obtains the list of files saved in the meccertbulkdownload_issues area of ​​Moodle
$files = $fs->get_area_files($context->id, 'local_meccertbulkdownload', 'meccertbulkdownload_issues', 0);

// recreate the array with the id in the key so you can sort it chronologically
// (does not use the creation date as key because if multiple files had the same
// creation date, only the last one would be kept)
$files2 = [];
foreach ($files as $file) {
    $files2[$file->get_id()] = $file;
}
if ($files2) {
    krsort($files2, SORT_NUMERIC);
}
$files = $files2;
unset($files2);


// pagination parameters
$recsCount = count($files);
$from = ($perpage * $page) + 1;
$to = ($perpage * $page) + $perpage;
if ($to > $recsCount) $to = $recsCount;

// filter the list based on pagination
$files = array_slice($files, $from - 1, $perpage, true);


// https://github.com/moodle/moodle/blob/master/lib/outputcomponents.php
$table = new html_table();
$table->align = array('left', 'left', 'left', 'right');
$table->head = ['File', get_string('size'), get_string('date'), get_string('actions')];
$i = 0;

foreach ($files as $file) {
    if ( (($i+1) < $from) && (($i+1) > $to) ) continue;

    $url = \moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename(),
        false                     // Do not force download of the file.
    );

    $urldelete = new moodle_url('/local/meccertbulkdownload/list.php',
        array(
            'action' => 'del',
            'aid' => $file->get_id(),
        )
    );

    $table->data[$i][0] = '<a href="' . $url . '">' . $file->get_filename()  . '</a>';
    $table->data[$i][1] = meccertbulkdownload::formatBytes($file->get_filesize());
    $table->data[$i][2] = date('d/m/Y H:i:s', $file->get_timecreated());
    $table->data[$i][3] = '
        <a href="' . $url . '">' . get_string('download') . '</a>'
        . ' | '
        . '<a style="color: red;" href="' . $urldelete . '">' . get_string('delete') . '</a>';

    $i++;
}


// prepare parameters for pagination bar
$params = array('page' => $page, 'perpage' => $perpage);
$baseurl = new moodle_url('/local/meccertbulkdownload/list.php', $params);

// prepare parameters for selecting how many per page
$params = array('page' => 0);
$baseurl2 = new moodle_url('/local/meccertbulkdownload/list.php', $params);


echo $OUTPUT->header();

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
        <a class="nav-link" href="index.php">' . get_string('packscreate', 'local_meccertbulkdownload') . '</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="list.php">' . get_string('packsdownload', 'local_meccertbulkdownload') . '</a>
    </li>
</ul>
<div>&nbsp;</div>';

echo '<div style="text-align: center;">';
    echo html_writer::table($table);
    echo '<div style="display: table; width: 100%; margin-top: 8px;">';
        echo '<div style="display: table-cell; text-align: left;">';
            echo "Record da $from a $to di $recsCount - Per pagina: ";
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

echo $OUTPUT->footer();
