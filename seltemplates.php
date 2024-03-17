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
use \local_meccertbulkdownload\form\templates_form;
use \local_meccertbulkdownload\task\pack_certificates_task;

require('../../config.php');
require_once($CFG->libdir.'/formslib.php');

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/meccertbulkdownload/seltemplates.php');
$PAGE->set_title(get_string('pluginname', 'local_meccertbulkdownload'));
$PAGE->set_heading(get_string('pluginname', 'local_meccertbulkdownload'));

require_login();

if (!has_capability('mod/customcert:viewallcertificates', $context)) {
    die();
}

$backurl = new moodle_url('/local/meccertbulkdownload/index.php');
$okkurl = new moodle_url('/local/meccertbulkdownload/list.php');


// FORM FOR SELECTING TEMPLATES FOR PDF AND PACKAGE NAMES

$pdftamplates = meccertbulkdownload::get_pdf_templates(true);
$packtemplates = meccertbulkdownload::get_pack_templates(true);

if (empty($pdftamplates) || empty($packtemplates)) {
    echo $OUTPUT->header();
    \core\notification::error(get_string('errornotemplate', 'local_meccertbulkdownload'));
    echo $OUTPUT->footer();
    die;
}

$tform = new templates_form(null, [
    'pdftamplates' => $pdftamplates,
    'packtemplates' => $packtemplates,
    'from_filter_form' => ''
]);


// comes from this same file after pressing cancel or submit in form for template
if ($tform->is_cancelled()) {  // pressed cancel in form

    redirect($backurl);

} else if ($fromform = $tform->get_data()) {  // normal form submission for template

    if (false === isset($fromform->templatepdf)) {
        echo $OUTPUT->header();
        \core\notification::error(get_string('errornotemplateparameter', 'local_meccertbulkdownload'));
        echo $OUTPUT->footer();
        die;
    }

    if (false === isset($fromform->templatepack)) {
        echo $OUTPUT->header();
        \core\notification::error(get_string('errornotemplateparameter', 'local_meccertbulkdownload'));
        echo $OUTPUT->footer();
        die;
    }

    // Create the instance
    $mytask = new pack_certificates_task();

    // Set some custom data.
    $mytask->set_custom_data([
        'templatepdf' => $fromform->templatepdf,
        'templatepack' => $fromform->templatepack,
        'fromfilterform' => unserialize($fromform->from_filter_form)
    ]);

    // Queue the task.
    \core\task\manager::queue_adhoc_task($mytask);

    // return to start page with success message
    redirect(
        $okkurl, 
        get_string('queuetasksuccess', 'local_meccertbulkdownload'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );

} else {
    // first opening of the file (comes from the previous page with filter form)
    // or from this same page after the confirmation window appears

    // OBTAIN THE DATA FROM THE FILTERS SELECTED ON THE HOME PAGE AND USED TO
    // SHOW CERTIFICATES IN TABLE. THE DATA ACTUALLY COMES FROM THE FORM
    // HIDDEN WITH AN ORANGE BUTTON TO RESERVE DOWNLOAD AND WITH HIDDEN FIELDS
    // THAT SHOW THE FILTERS SELECTED IN THE FORM FOR FILTERS.
    // IF CONFIRMATION IS ALREADY DISPLAYED (SEE BELOW), THE SAME DATA WILL 
    // RETURN VIA GET.
    // if course and cohort are not set, the parameters contain the string "no"
    $from_filter_form = new stdClass;
    $from_filter_form->courseorcertificate = required_param('courseorcertificate', PARAM_TEXT);
    $from_filter_form->datefrom = required_param('datefrom', PARAM_INT);
    $from_filter_form->dateto = required_param('dateto', PARAM_INT);
    $from_filter_form->estimatedarchivesize = optional_param('estimatedarchivesize', 0, PARAM_FLOAT);


    // IF REQUESTED, BEFORE SELECTING PDF NAMES AND ZIP, SHOW PAGE
    // WITH EXPECTED ZIP SIZE AND REQUESTS CONFIRMATION TO PROCEED
    if (meccertbulkdownload::ASK_DOWNLOAD_CONFIRMATION) {

        $msgVersioneLeggera = '';
        if (meccertbulkdownload::LVNC) {
            $msgVersioneLeggera = meccertbulkdownload::LVNC;
            $msgVersioneLeggera = str_replace(
                '{QUANTI_CERT}',
                meccertbulkdownload::LVNC,
                get_string('bookconfirmmsglightversion', 'local_meccertbulkdownload')
            );
            $msgVersioneLeggera = '<p style="font-size: 0.8rem; text-align: center; color: rgb(88, 21, 28); background-color: rgb(248, 215, 218); margin: 28px -16px -32px -16px; padding: 10px 16px;">'
                . $msgVersioneLeggera
                . '</p>';
        }

        $confirm = optional_param('confirm', 0, PARAM_INT);
        if ($confirm === 0) {  // not yet seen page for confirmation
            // obtains free space on the disk
            $free_space = meccertbulkdownload::get_free_disk_space();
            // prepares any message of insufficient space on the server
            if ( ($from_filter_form->estimatedarchivesize * 2) > $free_space ) {
                $not_enough_space = '<p style="color:red"><strong>'
                    . get_string('bookconfirmmsgnotenoughspace', 'local_meccertbulkdownload')
                    . '</strong></p>';
            } else {
                $not_enough_space = '';
            }
            // create the confirmation page
            echo $OUTPUT->header();
            $nourl = new moodle_url('/local/meccertbulkdownload/index.php');
            $yesurl = new moodle_url('/local/meccertbulkdownload/seltemplates.php',
                array(
                    'courseorcertificate' => $from_filter_form->courseorcertificate,
                    'datefrom' => $from_filter_form->datefrom,
                    'dateto' => $from_filter_form->dateto,
                    'confirm' => 1,
                )
            );
            echo $OUTPUT->confirm(
                    '<p>'
                    . get_string('bookconfirmmsg', 'local_meccertbulkdownload')
                    . ' <strong>'
                    . $from_filter_form->estimatedarchivesize
                    . ' MB</strong>.</p>'
                    . '<p>'
                    . get_string('bookconfirmmsgserver', 'local_meccertbulkdownload')
                    . ' <strong>'
                    . $from_filter_form->estimatedarchivesize * 2
                    . ' MB</strong>.</p>'
                    . '<p>'
                    . get_string('bookconfirmmsgfreespace', 'local_meccertbulkdownload')
                    . ' <strong>'
                    . $free_space
                    . ' MB</strong>.</p>'
                    . $not_enough_space
                    . '<br>'
                    . '<small>'
                    . get_string('bookconfirmmsgnb', 'local_meccertbulkdownload')
                    . '</small>'
                    . $msgVersioneLeggera,
                $yesurl,
                $nourl
            );
            echo $OUTPUT->footer();
            exit();
        }

    } // if it displays confirmation


    // FORM FOR SELECTING TEMPLATES FOR PDF AND PACKAGE NAMES

    $pdftamplates = meccertbulkdownload::get_pdf_templates(true);
    $packtemplates = meccertbulkdownload::get_pack_templates(true);

    if (empty($pdftamplates) || empty($packtemplates)) {
        echo $OUTPUT->header();
        \core\notification::error(get_string('errornotemplate', 'local_meccertbulkdownload'));
        echo $OUTPUT->footer();
        die;
    }

    $tform = new templates_form(null, [
        'pdftamplates' => $pdftamplates,
        'packtemplates' => $packtemplates,
        'from_filter_form' => serialize($from_filter_form)
    ]);
}

echo $OUTPUT->header();
echo '<p style="margin-top: 25px; margin-bottom: 25px;">' . get_string('introseltemplate', 'local_meccertbulkdownload') . '</p>';
$tform->display();
echo $OUTPUT->footer();
