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
 * Allows the download of the list of certificates found.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_meccertbulkdownload\meccertbulkdownload;

require('../../config.php');
require_once($CFG->libdir.'/formslib.php');

// If the new \core\dataformat class (with download_data() method) does not exist, loads the old
// dataformatlib.php with the equivalent function download_as_dataformat().
// Function download_as_dataformat() is deprecated since Moodle 3.9 and removed since Moodle 4.4.
if (!class_exists('\core\dataformat')) {
    require_once($CFG->libdir.'/dataformatlib.php');
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/meccertbulkdownload/seltemplates.php');

require_login();

require_capability('local/meccertbulkdownload:searchcertificates', $context);

$dataformat = optional_param('dataformat', '', PARAM_ALPHA)
    ? optional_param('dataformat', '', PARAM_ALPHA)
    : optional_param('dataformat2', '', PARAM_ALPHA);

$fromform = optional_param('fromform', '', PARAM_RAW);
$fromform = json_decode($fromform);

$nomefile = date('Y-m-d_H-i') . '_certificates_list';

$columns = meccertbulkdownload::get_certificates_fields();

// Obtains parameters from the form data and creates the where part of the query.
$where = meccertbulkdownload::get_certificates_params($fromform);
// Derives the query, adds the where part and executes it.
$recs = $DB->get_recordset_sql(
    meccertbulkdownload::get_certificates_download_query() . $where['string'],
    $where['params']
);

$downloadCallback = function($record) {

    if ($record->certcreation) {
        $certcreationtmp = new DateTime('', core_date::get_user_timezone_object());
        $certcreationtmp->setTimestamp($record->certcreation);
        $certcreationtmp = userdate($certcreationtmp->getTimestamp(),
            get_string('strftimedatetimeshort', 'core_langconfig'));
    } else {
        $certcreationtmp = "";
    }

    if ($record->coursecompletion) {
        $coursecompletiontmp = new DateTime('', core_date::get_user_timezone_object());
        $coursecompletiontmp->setTimestamp($record->coursecompletion);
        $coursecompletiontmp = userdate($coursecompletiontmp->getTimestamp(),
            get_string('strftimedatetimeshort', 'core_langconfig'));
    } else {
        $coursecompletiontmp = "";
    }

    $record->certcreation = $certcreationtmp;
    $record->coursecompletion = $coursecompletiontmp;

    return $record;
};

// If the new \core\dataformat class (with download_data() method) does not exist, loads the old
// dataformatlib.php with the equivalent function download_as_dataformat().
// Function download_as_dataformat() is deprecated since Moodle 3.9 and removed since Moodle 4.4.
if (class_exists('\core\dataformat')) {
    \core\dataformat::download_data($nomefile, $dataformat, $columns, $recs, $downloadCallback);
} else {
    download_as_dataformat($nomefile, $dataformat, $columns, $recs, $downloadCallback);
}

$recs->close();
