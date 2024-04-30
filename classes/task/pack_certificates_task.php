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
 * Ad hoc task for creating zip archives of certificates.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_meccertbulkdownload\task;

use core_user;
use local_meccertbulkdownload\meccertbulkdownload;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for the ad hoc task for creating zip archives of certificates.
 *
 * Creates zip archives of certificates and saves them in the Moodle file area.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pack_certificates_task extends \core\task\adhoc_task {

    /**
     * Execute the task.
     */
    public function execute() {

        global $DB, $CFG, $USER;

        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->libdir . '/grouplib.php');
        require_once($CFG->libdir . '/moodlelib.php');

        mtrace('Task per ' . get_string('pluginname', 'local_meccertbulkdownload') . ' iniziato');

        // Set up the user for the cron.
        // cron_setup_user($user);

        // Get the custom data.
        $customdata = $this->get_custom_data();

        $filesforzipping = array();
        $fs = get_file_storage();

        // creates a sub-folder in the plugin temp folder
        $uniqdir = uniqid('tmpcerts');
        $tmpdir = make_temp_directory('local_meccertbulkdownload/' . $uniqdir);
        if (!$tmpdir) {
            throw new \Exception("Error creating temporary folder for PDFs");
        }

        // obtains parameters from the form data and creates the where part of the query
        $where = meccertbulkdownload::get_certificates_params($customdata->fromfilterform);
        // obtains the query, adds the where part and executes it
        $recs = $DB->get_recordset_sql(
            meccertbulkdownload::get_certificates_query() . $where['string'],
            $where['params']
        );

        // IF SEARCH BY COURSE AND 1 COURSE SELECTED AND ALL GROUPS REQUESTED,
        // within the main zip (which in this case represents
        // the selected course) creates a folder for each group
        if (
            $customdata->fromfilterform->courseorcohort === 'cor'
            && $customdata->fromfilterform->corso !== 'no'
            && $customdata->fromfilterform->gruppocorso === 'no'
        ) {
            // obtains groups and their course members
            $courseid = (int) $customdata->fromfilterform->corso;
            $groups = $this->get_course_groups_and_members($courseid);
            // if the course has groups, create a subfolder for each group
            if ($groups) {
                foreach ($groups as $gname => $gmembers) {
                    mkdir($tmpdir . '/' . $gname, 0775);
                }
            }
            // if course has no groups, $groups is false, then continue
            // normally as for searches that do not require grouping
        } else {
            $groups = false;
        }

        $paramforpacknamecourse = '-';
        $paramforpacknamecoursecode = '-';
        $paramforpacknamecohort = '-';

        $i = 0;

        foreach ($recs as $cert) {

            if (meccertbulkdownload::LVNC && $i >= meccertbulkdownload::LVNC) {
                break;
            }
            $i++;

            // obtains the template associated with the certificate and generates the pdf
            $template = $DB->get_record('customcert_templates', array('id' => $cert->templateid), '*', MUST_EXIST);
            $template = new \mod_customcert\template($template);
            $pdf = $template->generate_pdf(false, $cert->userid, true);

            $certuser = core_user::get_user($cert->userid);
            $userfullname = fullname($certuser);

            // obtains the name to give to the pdf
            // https://www.php.net/manual/en/datetime.format.php
            $pdfname = meccertbulkdownload::get_pdf_name($customdata->templatepdf, [
                    $cert->username,
                    $userfullname,
                    $cert->lastname ? $cert->lastname : 'nousersurname',
                    $cert->courseshortname ? $cert->courseshortname : 'nocourseshortname',
                    $cert->courseidnumber ? $cert->courseidnumber : 'nocoursecode',
                    $cert->cohortname ? $cert->cohortname : 'nocohortname'
                ],
                $cert->coursecompletion ? $cert->coursecompletion : null
            );

            // IF GROUP DIVISION IS NOT REQUIRED, SAVE THE PDF IN THE
            // GENERAL TEMPORARY FOLDER. OTHERWISE IN THAT OF ITS
            // GROUP TO WHICH IT BELONGS. IF IT BELONGS TO MORE THAN ONE GROUP,
            // SAVE COPY TO ALL ITS GROUPS

            if (!$groups) {
                // save the pdf in the temporary folder
                file_put_contents($tmpdir . '/' . $pdfname . '.pdf', $pdf);
                // save the pdf in the list of files to be passed to the function that will compress them
                $filesforzipping[$pdfname . '.pdf'] = $tmpdir . '/' . $pdfname . '.pdf';
            } else {
                // search among group users to see if this user is present
                $ingroups = false;
                foreach ($groups as $gname => $gmembers) {
                    // if user of this certificate is among members of this
                    // group, save the pdf in the group folder
                    if (array_key_exists($cert->userid, $gmembers)) {
                        file_put_contents($tmpdir . '/' . $gname . '/' . $pdfname . '.pdf', $pdf);
                        $filesforzipping["$gname/$pdfname.pdf"] = $tmpdir . '/' . $gname . '/' . $pdfname . '.pdf';
                        $ingroups = true;
                    }
                }
                // if user is in no group, puts him in root of zip
                if (!$ingroups) {
                    file_put_contents($tmpdir . '/' . $pdfname . '.pdf', $pdf);
                    $filesforzipping["$pdfname.pdf"] = $tmpdir . '/' . $pdfname . '.pdf';
                }
            }

            // COURSE
            // if the course or cohort name is the same for all records
            // saves the value to use (if required by the template
            // selected by the user) as a parameter for the zip name
            if ($paramforpacknamecourse === '-') {  // primo giro
                $paramforpacknamecourse = $cert->courseshortname;
            } else { // other loops
                // if different from that of the first loop it means that they have courses
                // different so there cannot be the course parameter as the name of the zip
                if ($paramforpacknamecourse !== $cert->courseshortname) {
                    $paramforpacknamecourse = 'nocourseshortname';
                }
            }
            // COURSE CODE
            if ($paramforpacknamecoursecode === '-') {  // first loop
                $paramforpacknamecoursecode = $cert->courseidnumber;
            } else { // other loops
                // if different from that of the first round it means that they have courses
                // different therefore there cannot be the course code parameter as the name of the zip
                if ($paramforpacknamecoursecode !== $cert->courseidnumber) {
                    $paramforpacknamecoursecode = 'nocoursecode';
                }
            }
            // COHORT (GLOBAL GROUP)
            if ($paramforpacknamecohort === '-') {  // first loop
                $paramforpacknamecohort = $cert->cohortname;
            } else { // other loops
                // if different from that of the first loop it means that they have courses
                // different so there cannot be the course parameter as the name of the zip
                if ($paramforpacknamecohort !== $cert->cohortname) {
                    $paramforpacknamecohort = 'nocohortname';
                }
            }

            // mtrace('Coorte: ' . $paramforpacknamecohort);
        }

        $recs->close();

        // if all records had course name or course id or cohort = null,
        // the null remained in the respective variables (being, as mentioned,
        // the value same for all records), so now puts the no...
        $paramforpacknamecourse = $paramforpacknamecourse ? $paramforpacknamecourse : 'nocourseshortname';
        $paramforpacknamecoursecode = $paramforpacknamecoursecode ? $paramforpacknamecoursecode : 'nocoursecode';
        $paramforpacknamecohort = $paramforpacknamecohort ? $paramforpacknamecohort : 'nocohortname';

        // if no certificates with the passed parameters were found, it exits
        // (this should not happen because the user presses the
        // bulk download button only if the table displaying the certificates
        // that will be created and compressed is not empty)
        if (count($filesforzipping) == 0) {
            mtrace('Task per ' . get_string('pluginname', 'local_meccertbulkdownload') . ' terminato: nessun certificato trovato');
            return;
        }

        // if selected to obtain certificates only for a specific group
        // (inside a course), it gets the name to put in the name of the zip
        // if the zip name template requires it
        if (
            isset($customdata->fromfilterform->gruppocorso)
            && $customdata->fromfilterform->gruppocorso !== 'no'
        ) {
            $groupname = groups_get_group_name( (int) $customdata->fromfilterform->gruppocorso);
            $paramforpacknamegruppocorso = $groupname;
        } else {
            $paramforpacknamegruppocorso = 'nogroupname';
        }

        // JOINS PDFS CREATING THE COMPRESSED FILE

        // creates the path (path + filename) for the temporary compressed file
        $tempzippath = tempnam($CFG->tempdir . '/local_meccertbulkdownload/', 'pack');
        // compresses certificates into a temporary file
        $zipper = new \zip_packer();
        if (!$zipper->archive_to_pathname($filesforzipping, $tempzippath)) {
            throw new \Exception("Error creating the compressed file");
        }

        // SAVE THE TEMPORARY COMPRESSED FILE IN THE FILE AREA OF MOODLE
        // so that it remains saved until it is deleted
        // manually (tmp files are periodically deleted)

        // obtains the name to give to the compressed file
        $packname = meccertbulkdownload::get_pack_name($customdata->templatepack, [
            $paramforpacknamecourse,
            $paramforpacknamecoursecode,
            $paramforpacknamecohort,
            $paramforpacknamegruppocorso
        ]);

        // prepares the fileinfo object with the file info
        $fileinfo = array(
            'contextid' => \context_system::instance()->id, // ID of context
            'component' => 'local_meccertbulkdownload',     // usually = table name
            'filearea'  => 'meccertbulkdownload_issues',    // usually = table name
            'itemid'    => 0,                               // usually = ID of row in table
            'filepath'  => '/',   // any path beginning and ending in /
            'filename'  => $packname . '.zip'
        );

        // if the file already exists it deletes it
        if ($file = $fs->get_file(
            $fileinfo['contextid'],
            $fileinfo['component'],
            $fileinfo['filearea'],
            $fileinfo['itemid'],
            $fileinfo['filepath'],
            $fileinfo['filename']
        )) {
            mtrace('Id del file eliminato: >' . $file->get_id() . '<');
            $file->delete();
        }

        // save the compressed file to a Moodle file area
        $fs->create_file_from_pathname($fileinfo, $tempzippath);

        // deletes any files from this plugin and the files area compressed
        // files (the only one of the plugin) with name "." and of zero length
        $DB->delete_records('files', [
            'component' => 'local_meccertbulkdownload',
            'filearea' => 'meccertbulkdownload_issues',
            'filename' => '.',
            'filesize' => 0,
        ]);

        // delete the temporary compressed file (.tmp extension)
        unlink($tempzippath);

        // delete the temporary folder with the pdfs that were zipped
        $this->delete_directory($tmpdir);

        // notifies the user who created the task that the file is ready
        $this->send_end_notification($USER, $packname);

        mtrace('Task per ' . get_string('pluginname', 'local_meccertbulkdownload') . ' terminato');
    }

    /**
     * Delete a folder and its contents.
     *
     * @param string $dir Path of the folder to delete
     * @return bool  Success or failure
     */
    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * Notifies the passed user that the compressed certificate package
     * is ready (task completed).
     *
     * @param stdClass $user Notification recipient
     * @param string   $packname Name of the zip archive
     * @return int     Id of the notification
     */
    private function send_end_notification($user, $packname) {
        $packname = $packname . '.zip';

        $message = new \core\message\message();
        $message->component = 'local_meccertbulkdownload'; // Your plugin's name
        $message->name = 'confirmation'; // Your notification name from message.php
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $user;
        $message->subject = get_string('msgconfirmationsubject', 'local_meccertbulkdownload');
        $message->fullmessage = get_string('msgconfirmationfullmessage', 'local_meccertbulkdownload') . $packname;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = get_string('msgconfirmationfullmessagehtml', 'local_meccertbulkdownload') . '<b>' . $packname . '</b>';
        $message->smallmessage = get_string('msgconfirmationsmallmessage', 'local_meccertbulkdownload') . $packname;
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/local/meccertbulkdownload/list.php'))->out(false); // A relevant URL for the notification
        $message->contexturlname = get_string('msgconfirmationcontexturlname', 'local_meccertbulkdownload'); // Link title explaining where users get to for the contexturl

        return message_send($message);
    }

    /**
     * Makes a string usable as a directory name by replacing everything
     * anything that is not an unaccented letter or number, with a hyphen.
     *
     * @param string  $str String to be 'cleaned'
     * @return string Cleaned string
     */
    private function string_to_dirname($str) {
        return preg_replace( '/[^a-z0-9]+/', '-', strtolower( $str ) );
    }

    /**
     * Get the group members of the past course and return an array with
     * as key the normalized and truncated name of the group and as value
     * an array with member ids.
     *
     * @param int          $courseid Id of the course to obtain groups and members
     * @return false|array Groups or false if there are no groups
     */
    private function get_course_groups_and_members($courseid) {
        $groups = groups_get_all_groups($courseid, 0, 0, 'g.id, g.name');
        if (count($groups) > 0) {
            foreach ($groups as $group) {
                $groupzipname = $this->string_to_dirname($group->name);
                $groupzipname = substr($groupzipname, 0, 100);
                $groupzipname = $groupzipname . '_(' . $group->id . ')';
                $members[$groupzipname] = groups_get_members($group->id, 'u.id');
            }
            return $members;
        } else {
            return false;
        }
    }
}
