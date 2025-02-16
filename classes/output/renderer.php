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
 * Defines the renderer for the local_meccertbulkdownload module.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_meccertbulkdownload\output;

use plugin_renderer_base;
use html_table;
use html_writer;
use context_system;
use DateTime;
use core_date;
use stdClass;
use tabobject;
use moodle_url;
use stored_file;
use local_meccertbulkdownload\meccertbulkdownload;
use local_meccertbulkdownload\form\filters_form;
use local_meccertbulkdownload\form\filters_hidden_form;
use local_meccertbulkdownload\form\templates_form;
use local_meccertbulkdownload\output\results_table_section;
use local_meccertbulkdownload\output\table_pagination;

/**
 * The renderer for the local_meccertbulkdownload module.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Create the index page.
     *
     * @param filters_form $fform Filter form
     * @param stdClass $fromform Params from filter form
     * @param filters_hidden_form $fhform Hidden filter form
     * @param string $resultstable HTML of the results table
     * @param stdClass $resultstablematerial Material for the results table
     * @return string HTML of the page
     */
    public function index_page(
        filters_form $fform,
        ?stdClass $fromform,
        ?filters_hidden_form $fhform,
        ?string $resultstable,
        ?stdClass $resultstablematerial
    ) {
        $output = $this->header();

        $output .= $this->container('&nbsp;');

        $tabs = [];
        if (has_capability('local/meccertbulkdownload:searchcertificates', context_system::instance())) {
            $tabs[] = new tabobject('index', 'index.php', get_string('packscreate', 'local_meccertbulkdownload'), '', true);
        }
        if (has_capability('local/meccertbulkdownload:viewarchives', context_system::instance())) {
            $tabs[] = new tabobject('list', 'list.php', get_string('packsdownload', 'local_meccertbulkdownload'), '', true);
        }
        $output .= $this->tabtree($tabs, 'index', false);

        $output .= $fform->render();

        if($resultstable) {
            $fhform->set_display_vertical();
            $createarchivescapability = has_capability('local/meccertbulkdownload:createarchives', context_system::instance());
            $resultstablesection = new results_table_section(
                (($resultstablematerial->resultscount > 0) && $createarchivescapability),
                $fhform->render(),
                (int) $resultstablematerial->resultscount,
                $resultstable,
                $resultstablematerial->recordsstatus,
                $resultstablematerial->paginationurl,
                $resultstablematerial->perpageoptions,
                $this->paging_bar(
                    $resultstablematerial->resultscount,
                    $resultstablematerial->page,
                    $resultstablematerial->perpage,
                    $resultstablematerial->baseurl
                ),
                $this->download_dataformat_selector(
                    get_string('download'),
                    'download.php',
                    'dataformat',
                    ['fromform' => json_encode($fromform)]
                )
            );
            $output .= $this->render($resultstablesection);
        }

        $output .= $this->footer();

        return $output;
    }

    /**
     * Create the page with expected zip size and the requests for
     * confirmation to proceed.
     *
     * @param stdClass $fromfilterform Params from filter form
     * @return string HTML of the size confirmation page
     */
    public function size_confirmation_page(stdClass $fromfilterform) {

        $bookconfirmmsg = $this->output->container(
            get_string('bookconfirmmsg', 'local_meccertbulkdownload')
                . ' '
                . html_writer::start_span('font-weight-bold')
                . $fromfilterform->estimatedarchivesize . ' MB.'
                . html_writer::end_span(),
            'mb-3'
        );

        $bookconfirmmsgserver = $this->output->container(
            get_string('bookconfirmmsgserver', 'local_meccertbulkdownload')
                . ' '
                . html_writer::start_span('font-weight-bold')
                . ($fromfilterform->estimatedarchivesize * 2) . ' MB.'
                . html_writer::end_span(),
            'mb-3'
        );

        // Obtains free space on the disk.
        $freespace = meccertbulkdownload::get_free_disk_space();
        if ($freespace > 0) {
            // Prepares any message of insufficient space on the server.
            if ( ($fromfilterform->estimatedarchivesize * 2) > $freespace ) {
                $notenoughspace = $this->output->container(
                    get_string('bookconfirmmsgnotenoughspace', 'local_meccertbulkdownload'),
                    'font-weight-bold text-danger mt-3'
                );
            } else {
                $notenoughspace = '';
            }
            // Prepares the message of free space (with any insufficient space message)
            $freespacemsg = $this->output->container(
                get_string('bookconfirmmsgfreespace', 'local_meccertbulkdownload')
                    . html_writer::start_span('font-weight-bold') . " $freespace MB." . html_writer::end_span()
                    . $notenoughspace,
                'mb-3'
            );
        } else {
            $freespacemsg = "";
        }

        $bookconfirmmsgnb = $this->output->container(
            get_string('bookconfirmmsgnb', 'local_meccertbulkdownload'),
            'small mb-3'
        );

        $lightversionmsg = '';

        if (meccertbulkdownload::LVNC > 0) {
            $lightversionmsg = meccertbulkdownload::LVNC;
            $lightversionmsg = str_replace(
                '{HOW MANY_CERT}',
                meccertbulkdownload::LVNC,
                get_string('bookconfirmmsglightversion', 'local_meccertbulkdownload')
            );
            $lightversionmsg = $this->output->container($lightversionmsg, 'confirmation-lightversionmsg text-center');
        }

        $nourl = new moodle_url('/local/meccertbulkdownload/index.php');
        $yesurl = new moodle_url('/local/meccertbulkdownload/seltemplates.php',
                [
                    'courseorcertificate' => $fromfilterform->courseorcertificate,
                    'datefrom' => $fromfilterform->datefrom,
                    'dateto' => $fromfilterform->dateto,
                    'confirm' => 1,
                ]
            );

        $output = $this->header();
        $output .= $this->confirm(
            $bookconfirmmsg . $bookconfirmmsgserver . $freespacemsg . $bookconfirmmsgnb . $lightversionmsg,
            $yesurl,
            $nourl
        );
        $output .= $this->footer();

        return $output;
    }

    /**
     * Create the template selection page for the pdf and zip file names.
     *
     * @param templates_form $tform Template selection form
     * @return string HTML of the template selection page
     */
    public function seltemplate_page(templates_form $tform) {
        $output = $this->header();
        $output .= $this->output->container(
            get_string('introseltemplate', 'local_meccertbulkdownload'),
            'my-4'
        );
        $output .= $tform->render();
        $output .= $this->footer();
        return $output;
    }

    /**
     * Create a page with only the passed notification and a button to come back
     * to initial plugin page.
     *
     * @param string $message The text of the notification
     * @param string $messagetype The type of the notification ('success', 'info', 'error', 'warning')
     * @return void
     */
    public function notification_page(string $message, string $messagetype = 'error') {
        $output = $this->header();
        $output .= $this->notification($message, $messagetype, false);
        $output .= $this->continue_button(new moodle_url('/local/meccertbulkdownload/index.php'));
        $output .= $this->footer();
        echo $output;
        exit();
    }

    /**
     * Create the page with the list of created certificate archives.
     *
     * @param int $page Current pagination page
     * @param int $perpage Number of archives per page
     * @param int $from First package to display (considering pagination)
     * @param int $to Last package to display (considering pagination)
     * @param int $recscount Total number of archives
     * @param string $recordsstatus Information about the records displayed
     * @param moodle_url $baseurl Base URL for the paging_bar renderable/template
     * @param string $paginationurl URL to chenge the records displayed per page
     * @param stored_file[] $files Array with the created certificate archives
     * @param bool $deletearchives If the user has the capability to delete archives
     * @param stdClass[] $perpageoptions Options for the per page selector
     * @return string HTML of the page
     */
    public function archives_page(
        int $page,
        int $perpage,
        int $from,
        int $to,
        int $recscount,
        string $recordsstatus,
        moodle_url $baseurl,
        string $paginationurl,
        array $files,
        bool $deletearchives,
        array $perpageoptions
    ) {
        $output = $this->header();

        $output .= $this->container('&nbsp;');

        $tabs = [];
        if (has_capability('local/meccertbulkdownload:searchcertificates', context_system::instance())) {
            $tabs[] = new tabobject('index', 'index.php', get_string('packscreate', 'local_meccertbulkdownload'), '', true);
        }
        if (has_capability('local/meccertbulkdownload:viewarchives', context_system::instance())) {
            $tabs[] = new tabobject('list', 'list.php', get_string('packsdownload', 'local_meccertbulkdownload'), '', true);
        }
        $output .= $this->tabtree($tabs, 'list', false);

        $output .= $this->container('&nbsp;');

        $output .= $this->archives_table($files, $from, $to, $deletearchives);

        if(count($files) > 0) {
            $tablepagination = new table_pagination(
                $recordsstatus,
                $paginationurl,
                $perpageoptions,
                $this->paging_bar(
                    $recscount,
                    $page,
                    $perpage,
                    $baseurl
                )
            );
            $output .= $this->render($tablepagination);
        }

        $output .= $this->footer();

        return $output;
    }

    /**
     * Create confirmation page for archives file deletion.
     *
     * @param stored_file $file File to delete
     * @param int $actionid ID of the file to delete
     * @return void
     */
    public function archives_deletion_confirmation_page(stored_file $file, int $actionid) {

        $nourl = new moodle_url('/local/meccertbulkdownload/list.php');
        $yesurl = new moodle_url('/local/meccertbulkdownload/list.php',
            [
                'action' => 'del',
                'aid' => $actionid,
                'confirm' => 1,
            ]
        );

        $message = $this->container(
            get_string('deleteconfirmmsg', 'local_meccertbulkdownload'),
            'mb-3'
        );
        $message .= $this->container(
            $file->get_filename(),
            'font-weight-bold'
        );

        $output = $this->header();
        $output .= $this->confirm($message, $yesurl, $nourl);
        $output .= $this->footer();
        echo $output;

        exit();
    }

    /**
     * Create the table with the results of the certificate search in the database.
     *
     * @param $results Results of the search in the database
     * @return string The table
     */
    public function results_table($results) {

        $table = new html_table();
        $table->align = ['left', 'left', 'left', 'left', 'right', 'right'];
        $table->head = meccertbulkdownload::get_certificates_fields();
        $i = 0;

        // If there are results...
        if ($results->valid()) {
            foreach ($results as $cert) {

                if ($cert->certcreation) {
                    $certcreationtmp = new DateTime('', core_date::get_user_timezone_object());
                    $certcreationtmp->setTimestamp($cert->certcreation);
                    $certcreationtmp = userdate($certcreationtmp->getTimestamp(),
                        get_string('strftimedatetimeshort', 'core_langconfig'));
                } else {
                    $certcreationtmp = "";
                }

                if ($cert->coursecompletion) {
                    $coursecompletiontmp = new DateTime('', core_date::get_user_timezone_object());
                    $coursecompletiontmp->setTimestamp($cert->coursecompletion);
                    $coursecompletiontmp = userdate($coursecompletiontmp->getTimestamp(),
                        get_string('strftimedatetimeshort', 'core_langconfig'));
                } else {
                    $coursecompletiontmp = "";
                }

                $table->data[$i][0] = $cert->username;
                $table->data[$i][1] = $cert->firstname . " " . $cert->lastname;
                $table->data[$i][2] = $cert->cohortname;
                $table->data[$i][3] = $cert->coursename;
                $table->data[$i][4] = $certcreationtmp;
                $table->data[$i][5] = $coursecompletiontmp;
                $i++;
            }
        } else {
            $table->align = ['center'];
            $table->head = [""];
            $table->data[0][0] = $this->container(
                get_string('nocertificatesfound', 'local_meccertbulkdownload'),
                'my-5 mx-auto py-2'
            );
        }

        return html_writer::table($table);
    }

    /**
     * Create the table with the list of created certificate archives.
     *
     * @param stored_file[] $files Array with the created certificate archives
     * @param int $from First package to display (considering pagination)
     * @param int $to Last package to display (considering pagination)
     * @param bool $deletearchives If the user has the capability to delete archives
     * @return string The table
     */
    public function archives_table(array $files, int $from, int $to, bool $deletearchives) {

        $table = new html_table();
        $table->align = ['left', 'left', 'left', 'right'];
        $table->head = [get_string('file'), get_string('size'), get_string('date'), get_string('actions')];
        $i = 0;

        if (count($files) > 0) {
            foreach ($files as $file) {
                if ( (($i + 1) < $from) && (($i + 1) > $to) ) {
                    continue;
                }

                $url = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false  // Do not force download of the file.
                );

                $urldelete = new moodle_url('/local/meccertbulkdownload/list.php',
                    [
                        'action' => 'del',
                        'aid' => $file->get_id(),
                    ]
                );

                $table->data[$i][0] = html_writer::link($url, $file->get_filename());
                $table->data[$i][1] = meccertbulkdownload::format_bytes($file->get_filesize());
                $table->data[$i][2] = date('d/m/Y H:i:s', $file->get_timecreated());
                $table->data[$i][3] = html_writer::link($url, get_string('download'));

                if ($deletearchives) {
                    $table->data[$i][3] .= ' | ' . html_writer::link($urldelete, get_string('delete'), ['class' => 'text-danger']);
                }

                $i++;
            }
        } else {
            $table->align = ['center'];
            $table->head = [""];
            $table->data[0][0] = $this->container(get_string('none'), 'my-5 mx-auto py-4');
        }

        return html_writer::table($table);
    }
}
