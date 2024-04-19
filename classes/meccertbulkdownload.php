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

namespace local_meccertbulkdownload;

defined('MOODLE_INTERNAL') || die();

class meccertbulkdownload {


    // --- INTERNAL PLUGIN CONFIGURATION ---

    // Whether to show the confirmation page, with estimated size of the
    // compressed file and disk space, before proceeding to the reservation
    // of the creation of the compressed file.
    const ASK_DOWNLOAD_CONFIRMATION = true;

    const LVNC = 10 + 10;

    // -------------------------------------


    /**
     * Used by the table in the index with the certificate list and by the 
     * task for the generation of certificates.
     * 
     * @return string   The string with the query for the list of certificates.
     */
    public static function get_certificates_query($count = false)
    {
        if ($count) {
            $q = 'SELECT COUNT(mci.id) AS quanti';
        } else {
            $q = 'SELECT
                         mci.id,
                         mci.userid,
                         mci.timecreated AS certcreation,
                         mu.username,
                         mu.firstname,
                         mu.lastname,
                         mc.templateid,
                         mc.course AS courseid,
                         mco.fullname AS coursename,
                         mco.shortname AS courseshortname,
                         mco.idnumber AS courseidnumber,
                         mcm.cohortid,
                         mcoh.name AS cohortname,
                         mcc.timecompleted AS coursecompletion';
        }
        return $q .= '
                    FROM {customcert_issues} mci
                    JOIN {customcert} mc ON mci.customcertid = mc.id
                    JOIN {user} mu ON mci.userid = mu.id 
               LEFT JOIN {cohort_members} mcm ON mci.userid = mcm.userid
               LEFT JOIN {cohort} mcoh ON mcm.cohortid = mcoh.id
                    JOIN {course} mco ON mc.course = mco.id
               LEFT JOIN {course_completions} mcc ON mci.userid = mcc.userid
                         AND mc.course = mcc.course';
    }

    /**
     * Used by download function (.csv, xlsx, etc.) of the table with the
     * certificates. It differs from the one used by the table itself and the
     * task for certificate generation, only in the SELECT fields.
     * 
     * @return string   The string with the query for the list of certificates.
     */
    public static function get_certificates_download_query()
    {
        return 
            'SELECT
                    mu.username,
                    CONCAT(mu.firstname, " ", mu.lastname),
                    mcoh.name AS cohortname,
                    mco.fullname AS coursename,
                    mci.timecreated AS certcreation,
                    mcc.timecompleted AS coursecompletion
               FROM {customcert_issues} mci
               JOIN {customcert} mc ON mci.customcertid = mc.id
               JOIN {user} mu ON mci.userid = mu.id 
          LEFT JOIN {cohort_members} mcm ON mci.userid = mcm.userid
          LEFT JOIN {cohort} mcoh ON mcm.cohortid = mcoh.id
               JOIN {course} mco ON mc.course = mco.id
          LEFT JOIN {course_completions} mcc ON mci.userid = mcc.userid
                    AND mc.course = mcc.course';
    }

    /**
     * Checks the passed data (coming from forms with filters) and obtains
     * filters to build the WHERE part of the certificate query.
     *
     * @param stdClass $fromform    Data form "filters_form".
     * @return array                String and parameters of the WHERE.
     */
    public static function get_certificates_params($fromform)
    {
        // what period? Course completion date or certificate issue date
        $period = ['cor' => 'mcc.timecompleted', 'cer' => 'mci.timecreated'];
        $period = $period[$fromform->courseorcertificate];

        $whereArray = [];
        $whereStr = '';
        $whereParams = null;

        if (isset($fromform->datefrom) && $fromform->datefrom) {
            $whereArray[] = "$period >= :datafinecorsofrom";
            $whereParams['datafinecorsofrom'] = $fromform->datefrom;
        }
        if (isset($fromform->dateto) && $fromform->dateto) {
            $whereArray[] = "$period <= :datafinecorsoto";
            $whereParams['datafinecorsoto'] = $fromform->dateto + 86399;
        }

        if (count($whereArray)) {
            $whereStr = " WHERE " . implode(" AND ", $whereArray);
        }

        return ['string' => $whereStr, 'params' => $whereParams];
    }

    public static function get_certificates_fields()
    {
        return [
            'Username',
            get_string('user'),
            get_string('cohort', 'local_meccertbulkdownload'),
            get_string('course'),
            get_string('certcreation', 'local_meccertbulkdownload'),
            get_string('coursecompletiondate', 'local_meccertbulkdownload')
        ];
    }

    public static function get_pdf_templates($only_names = false)
    {
        $pdftamplates = get_config('local_meccertbulkdownload', 'pdfnametemplates');
        return self::get_array_from_lines($pdftamplates, $only_names);
    }

    public static function get_pack_templates($only_names = false)
    {
        $packtamplates = get_config('local_meccertbulkdownload', 'packnametemplates');
        return self::get_array_from_lines($packtamplates, $only_names);
    }

    /**
     * @param array $params username, userfullname, usersurname, courseshortname,
     *                      coursecode, cohortname.
     */
    public static function get_pdf_name($template_name, $params, $coursecompletiondate)
    {
        $search = [
            '{{username}}', 
            '{{userfullname}}', 
            '{{usersurname}}', 
            '{{courseshortname}}', 
            '{{coursecode}}', 
            '{{cohortname}}'
        ];
        $pdftamplates = self::get_pdf_templates();
        if (array_key_exists($template_name, $pdftamplates)) {
            $pdfname = $pdftamplates[$template_name];
            $pdfname = str_replace($search, $params, $pdfname);
            return self::date_replace($pdfname, $coursecompletiondate);
        } else {
            return false;
        }
    }

    public static function get_pack_name($template_name, $params)
    {
        $search = [
            '{{courseshortname}}',
            '{{coursecode}}',
            '{{cohortname}}',
            '{{groupname}}'
        ];
        $packtamplates = self::get_pack_templates();
        if (array_key_exists($template_name, $packtamplates)) {
            $packname = $packtamplates[$template_name];
            $packname = str_replace($search, $params, $packname);
            return self::date_replace($packname);
        } else {
            return false;
        }
    }

    /**
     * Transform a byte size into the most suitable format.
     *
     * @see https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
     */
    public static function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
    
        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 
    
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 

    /**
     * Process text from text boxes in plugin configuration for the choice of
     * file name templates.
     * 
     * @param string $text      Text on multiple lines and with each line containing a
     *                          semicolon separating the first and second
     *                          part of the text.
     * @param bool $only_names  FALSE (defualt): sets template name as key
     *                          of the array and the template as the value.
     *                          TRUE: sets template name as key and as
     *                          value puts name + value in parentheses.
     * @return array            Array with one item for each line of text
     *                          received and keyed to the first part of the text
     *                          and value the second part.
     */
    private static function get_array_from_lines($text, $only_names = false)
    {
        $text = trim($text);
        $text = explode("\n", $text);
        $text = array_filter($text, 'trim'); // remove any extra \r characters left behind

        $lines = [];
        foreach ($text as $line) {
            $line = trim($line);
            if ($line) {
                $lineArr = explode(":", $line);
                if (!empty($lineArr)) {
                    if ($only_names) {
                        $lines[trim($lineArr[0])] = trim($lineArr[0]) . ' - ' . trim($lineArr[1]);
                    } else {
                        $lines[trim($lineArr[0])] = trim($lineArr[1]);
                    }
                }
            }
        }

        return $lines;
    }

    /**
     * In the passed string, replace any appropriate parameters with the
     * today's date or the end date passed.
     * 
     * The parameter for today's date is es. "{{todaysdate(mdY)}}".
     * The parameter for the course end date is es. "{{courseenddate(mdY)}}".
     * 
     * Example: "Today is {{todaysdate(d-m-Y)}}." => "today is the 23-02-2023."
     */
    private static function date_replace($string, $coursecompletiondate = null)
    {
        $string = preg_replace_callback(
            '/\{\{todaysdate\((.*)\)\}\}/U',
            function ($matches) {
                return date($matches[1]);
            },
            $string
        );

        $string = preg_replace_callback(
            '/\{\{courseenddate\((.*)\)\}\}/U',
            function ($matches) use ($coursecompletiondate) {
                if ($coursecompletiondate) {
                    return date($matches[1], $coursecompletiondate);
                } else {
                    // if the course end date is not there, if it finds the
                    // parameter in the string puts 'nocourseenddate'
                    return 'nocourseenddate';
                }
            },
            $string
        );

        return $string;
    }

    /**
     * Calculates the estimated size of the zip package obtained by compressing the
     * number of certificates passed. It calculates this based on the estimated
     * average size, entered by the user in the plugin configurations, of a
     * single certificate.
     * 
     * @param integer $certificatesnumber Number of certificates they will make
     *                                    up the compressed package.
     * @return integer Estimated size of the compressed package in MB.
     */
    public static function get_estimatedarchivesize($certificatesnumber)
    {
        // in the configuration the estimated size of a certificate is entered in KB
        $estimatedarchivesize = get_config('local_meccertbulkdownload', 'estimatedarchivesize');
        if (!$estimatedarchivesize) $estimatedarchivesize = 500;
        $estimatedarchivesize = $estimatedarchivesize * $certificatesnumber;
        return $estimatedarchivesize / 1000;  // MB
    }

    /**
     * Get free disk space in MB and without decimal places.
     *
     * @return integer Free space in MB rounded.
     */
    public static function get_free_disk_space()
    {
        $free_space = 0;
        
        try {
            $win = disk_free_space("C:");
        } catch (\Exception $e) {}
        try {
            $lin = disk_free_space("/");
        } catch (\Exception $e) {}

        if (isset($win) && $win) $free_space = $win;
        if (isset($lin) && $lin) $free_space = $lin;
        
        if ($free_space > 0) $free_space = $free_space / 1000000;
        return round($free_space);
    }
}
