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
 * Strings for component 'local_meccertbulkdownload', language 'en'
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['all'] = '(all)';
$string['archivenametemplatesitem_desc'] = '<p>The compressed filename templates work just like the pdf ones. However, there are fewer parameters that can be used:</p>
<table style="margin-bottom: 18px;">
<tr><td><strong>{{courseshortname}}</strong></td><td style="padding-left: 25px;">Course short name</td></tr>
<tr><td><strong>{{coursecode}}</strong></td><td style="padding-left: 25px;">Course code</td></tr>
<tr><td><strong>{{cohortname}}</strong></td><td style="padding-left: 25px;">Cohort name</td></tr>
<tr><td><strong>{{groupname}}</strong></td><td style="padding-left: 25px;">Group name (only if exporting a single group within the course)</td></tr>
<tr><td><strong>{{todaysdate(...)}}</strong></td><td style="padding-left: 25px;">Today\'s date</td></tr>
</table>
<p style="margin-bottom: 30px; color: red;"><strong>Attention:</strong> if two files have the same name, the new one overwrites the old one.</p>';
$string['archivenametemplatesitem'] = 'Templates for archive names';
$string['archivenametemplatesitemsingular'] = 'Template for archive name';
$string['bookconfirmmsg'] = 'The compressed file with the certificates that will be generated will have an estimated size of';
$string['bookconfirmmsgfreespace'] = 'Free disk space on the server is (detected value may be incorrect):';
$string['bookconfirmmsglightversion'] = "The light version of the plugin allows you to download a maximum of {HOW MANY_CERT} certificates.";
$string['bookconfirmmsgnb'] = 'N.B. the estimate for the compressed file is based on the average size of the certificates indicated in the plugin configurations.';
$string['bookconfirmmsgnotenoughspace'] = 'WARNING: There appears to be insufficient space on the server to generate the file.';
$string['bookconfirmmsgserver'] = 'However, the space required on the server to generate it is double the size of the file, therefore';
$string['bulkdownloadlink'] = 'Download Certificates';
$string['certcreation'] = 'Certificate issuing date';
$string['certificateissuing'] = 'Certificate issuing period';
$string['cohort'] = 'Cohort';
$string['courseandgroup'] = 'Course and group (of the course)';
$string['coursecompletion'] = 'Course completion period';
$string['coursecompletiondate'] = 'Course completion date';
$string['coursecompletionfrom'] = 'From';
$string['coursecompletionto'] = 'To';
$string['createmanagestring_desc'] = 'It allows to access the page where you can create, download and manage certificate packages.<br>&nbsp;';
$string['createmanagestring'] = 'Archive creation and management';
$string['credit'] = 'MoodEasy.com';
$string['deleteconfirmmsg'] = 'Are you sure you want to delete the following file?';
$string['deleteerror'] = 'Error: file not deleted';
$string['deletenoparam'] = 'Error: missing parameter';
$string['deletesuccess'] = 'File successfully deleted';
$string['errornotemplate'] = 'At least one template for the pdf files name and at least one for the compressed files name must be defined in the plugin settings.';
$string['errornotemplateparameter'] = 'Error: missing parameter';
$string['errornotemplatereplacepack'] = 'An error occurred while substituting parameters in the template for the name of the compressed files.';
$string['errornotemplatereplacepdf'] = 'An error occurred while substituting parameters in the template for the pdf filename.';
$string['estimatedarchivesize'] = 'Average certificate size (KB)';
$string['estimatedarchivesize_desc'] = '<p style="margin-bottom: 30px;">Average size of a certificate considering the average of all certificates generated in the various courses, expressed in KB. Used to estimate the size of the compressed file to be generated for downloading certificates.</p>';
$string['formtemplatesubmit'] = 'Book displayed certificates download';
$string['introseltemplate'] = 'Select a template for the names to be given to the pdf files and one for the name of the final compressed file with all the pdfs.';
$string['messageprovider:confirmation'] = 'Confirmation of completion of certificate package preparation';
$string['msgconfirmationsubject'] = 'Certificate package ready';
$string['msgconfirmationfullmessage'] = 'The following zip package of certificates is ready: ';
$string['msgconfirmationfullmessagehtml'] = 'The following zip package of certificates is ready: ';
$string['msgconfirmationsmallmessage'] = 'The following zip package of certificates is ready: ';
$string['msgconfirmationcontexturlname'] = 'package list';
$string['nocertificatesfound'] = 'No certificates found';
$string['packscreate'] = 'Create certificates archives';
$string['packsdownload'] = 'Manage and download certificates archives';
$string['pdfnametemplatesitem_desc'] = '<p>Each line represents a template. e.g. "Simple:certificate_file", the first part up to the colon ("Simple") is the name of the template, the second ("certificate_file") is the name to give to the file (<strong>the ".pdf" extension is added automatically</strong>). The <strong>template name (first part) must be a single word (no spaces) and the template in general must not contain particular characters (only letters, numbers, underscores and minus sign).</strong></p> <p><strong>In the template (after the colon) it is possible to enter some parameters (enclosed in braces) which will then be replaced by the corresponding values</strong> (e.g. {{username}} will be replaced by the username of the user). The <b>{{todaysdate(…)}}</b> parameter has a particular behavior, it will be replaced by today\'s date in the format indicated by the letters indicated in between. For example, if today is 12/25/2023, {{todaysdate(d-m-Y)}} will become 12-25-2023. Same behavior for the parameter <b>{{courseenddate(...)}}</b> which however will return the completion date of the course in the indicated format. <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">Here you can find the characters you can use to format dates.</a> <span style="color: red;">Be careful not to put slash and backslash (or other special characters) as separators in dates.</span></p><p>The usable parameters are:</p>
<table style="margin-bottom: 18px;">
<tr><td><strong>{{username}}</strong></td><td style="padding-left: 25px;">Username</td></tr>
<tr><td><strong>{{userfullname}}</strong></td><td style="padding-left: 25px;">Full name</td></tr>
<tr><td><strong>{{usersurname}}</strong></td><td style="padding-left: 25px;">Surname</td></tr>
<tr><td><strong>{{courseshortname}}</strong></td><td style="padding-left: 25px;">Course short name</td></tr>
<tr><td><strong>{{coursecode}}</strong></td><td style="padding-left: 25px;">Course code</td></tr>
<tr><td><strong>{{cohortname}}</strong></td><td style="padding-left: 25px;">Cohort name</td></tr>
<tr><td><strong>{{todaysdate(...)}}</strong></td><td style="padding-left: 25px;">Today\'s date</td></tr>
<tr><td><strong>{{courseenddate(...)}}</strong></td><td style="padding-left: 25px;">Course end date</td></tr>
</table>
<p style="margin-bottom: 30px; color: red;"><strong>Attention:</strong> if two files have the same name, the new one overwrites the old one.</p>';
$string['pdfnametemplatesitem'] = 'Templates for pdf names';
$string['pluginname_help'] = 'Allows the selection of a group of Custom Cert certificates by course, group or date, and download them in bulk and in the background.';
$string['pluginname'] = 'ME CustomCert Bulk Download';
$string['preview'] = 'Preview';
$string['queuetasksuccess'] = 'The task has been queued and it will be done as soon as possible. The resulting file will appear below.';
$string['searchfor'] = 'Search for';
$string['tablerecordscount'] = 'Record from {{from}} to {{to}} of {{count}} - Per page: ';
