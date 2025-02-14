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
 * Allows selection of templates for PDF and archives names.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_meccertbulkdownload\meccertbulkdownload;
use local_meccertbulkdownload\form\templates_form;
use local_meccertbulkdownload\task\pack_certificates_task;

require('../../config.php');
require_once($CFG->libdir.'/formslib.php');

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/meccertbulkdownload/seltemplates.php');
$PAGE->set_title(get_string('pluginname', 'local_meccertbulkdownload'));
$PAGE->set_heading(get_string('pluginname', 'local_meccertbulkdownload'));

require_login();

if (!has_capability('local/meccertbulkdownload:createarchives', $context)) {
    die();
}

$backurl = new moodle_url('/local/meccertbulkdownload/index.php');
$okkurl = new moodle_url('/local/meccertbulkdownload/list.php');
$output = $PAGE->get_renderer('local_meccertbulkdownload');


// FORM FOR SELECTING TEMPLATES FOR PDF AND PACKAGE NAMES.

$pdftamplates = meccertbulkdownload::get_pdf_templates(true);
$packtemplates = meccertbulkdownload::get_pack_templates(true);

if (empty($pdftamplates) || empty($packtemplates)) {
    $output->notification_page(get_string('errornotemplate', 'local_meccertbulkdownload'));
}

$tform = new templates_form(null, [
    'pdftamplates' => $pdftamplates,
    'packtemplates' => $packtemplates,
    'fromfilterform' => '',
]);


// Comes from this same file after pressing cancel or submit in form for template.
if ($tform->is_cancelled()) {  // Pressed cancel in form.

    redirect($backurl);

} else if ($fromform = $tform->get_data()) {  // Normal form submission for template.

    if (false === isset($fromform->templatepdf)) {
        $output->notification_page(get_string('errornotemplateparameter', 'local_meccertbulkdownload'));
    }

    if (false === isset($fromform->templatepack)) {
        $output->notification_page(get_string('errornotemplateparameter', 'local_meccertbulkdownload'));
    }

    // Create the instance.
    $mytask = new pack_certificates_task();

    // Set some custom data.
    $mytask->set_custom_data([
        'templatepdf' => $fromform->templatepdf,
        'templatepack' => $fromform->templatepack,
        'fromfilterform' => unserialize($fromform->fromfilterform),
    ]);

    // Queue the task.
    \core\task\manager::queue_adhoc_task($mytask);

    // Return to start page with success message.
    redirect(
        $okkurl,
        get_string('queuetasksuccess', 'local_meccertbulkdownload'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );

} else {
    // First opening of the file (comes from the previous page with filter form)
    // or from this same page after the confirmation window appears.

    // OBTAIN THE DATA FROM THE FILTERS SELECTED ON THE HOME PAGE AND USED TO
    // SHOW CERTIFICATES IN TABLE. THE DATA ACTUALLY COMES FROM THE FORM
    // HIDDEN WITH AN ORANGE BUTTON TO RESERVE DOWNLOAD AND WITH HIDDEN FIELDS
    // THAT SHOW THE FILTERS SELECTED IN THE FORM FOR FILTERS.
    // IF CONFIRMATION IS ALREADY DISPLAYED (SEE BELOW), THE SAME DATA WILL
    // RETURN VIA GET.
    // If course and cohort are not set, the parameters contain the string "no".
    $fromfilterform = new stdClass();
    $fromfilterform->courseorcertificate = required_param('courseorcertificate', PARAM_TEXT);
    $fromfilterform->datefrom = required_param('datefrom', PARAM_INT);
    $fromfilterform->dateto = required_param('dateto', PARAM_INT);
    $fromfilterform->estimatedarchivesize = optional_param('estimatedarchivesize', 0, PARAM_FLOAT);


    // IF REQUESTED, BEFORE SELECTING PDF NAMES AND ZIP, SHOW PAGE
    // WITH EXPECTED ZIP SIZE AND REQUESTS CONFIRMATION TO PROCEED.
    if (meccertbulkdownload::ASK_DOWNLOAD_CONFIRMATION) {
        $confirm = optional_param('confirm', 0, PARAM_INT);
        // If not yet seen page for confirmation.
        if ($confirm === 0) {
            echo $output->size_confirmation_page($fromfilterform);
            exit();
        }
    }


    // FORM FOR SELECTING TEMPLATES FOR PDF AND PACKAGE NAMES.

    $pdftamplates = meccertbulkdownload::get_pdf_templates(true);
    $packtemplates = meccertbulkdownload::get_pack_templates(true);

    if (empty($pdftamplates) || empty($packtemplates)) {
        $output->notification_page(get_string('errornotemplate', 'local_meccertbulkdownload'));
    }

    $tform = new templates_form(null, [
        'pdftamplates' => $pdftamplates,
        'packtemplates' => $packtemplates,
        'fromfilterform' => serialize($fromfilterform),
    ]);
}


echo $output->seltemplate_page($tform);
