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

defined('MOODLE_INTERNAL') || die;

// https://moodledev.io/docs/apis/subsystems/admin
if ($hassiteconfig) {

    $ADMIN->add('localplugins', new admin_category('local_meccertbulkdownload_settings', new lang_string('pluginname', 'local_meccertbulkdownload')));
    // name and text of the configuration page added for the plugin and text of the link that appears in the plugin section to access the page
    $settingspage = new admin_settingpage('managelocalmeccertbulkdownload', new lang_string('pluginname', 'local_meccertbulkdownload'));

    if ($ADMIN->fulltree) {

        $settingspage->add(new \mod_customcert\admin_setting_link('local_meccertbulkdownload/createmanagelink',
            new lang_string('createmanagestring', 'local_meccertbulkdownload'), new lang_string('createmanagestring_desc', 'local_meccertbulkdownload'),
            new lang_string('createmanagestring', 'local_meccertbulkdownload'), new moodle_url('/local/meccertbulkdownload/index.php'),
            ''
        ));

        $settingspage->add(new admin_setting_configtext('local_meccertbulkdownload/estimatedarchivesize',
            get_string('estimatedarchivesize', 'local_meccertbulkdownload'),
            get_string('estimatedarchivesize_desc', 'local_meccertbulkdownload'),
            500,
            PARAM_INT
        ));

        $settingspage->add(new admin_setting_configtextarea('local_meccertbulkdownload/pdfnametemplates',
            new lang_string('pdfnametemplatesitem', 'local_meccertbulkdownload'),
            new lang_string('pdfnametemplatesitem_desc', 'local_meccertbulkdownload'),
            'Base:{{userfullname}}_{{courseshortname}}_{{courseenddate(Y-m-d)}}'
        ));

        $settingspage->add(new admin_setting_configtextarea('local_meccertbulkdownload/packnametemplates',
            new lang_string('archivenametemplatesitem', 'local_meccertbulkdownload'),
            new lang_string('archivenametemplatesitem_desc', 'local_meccertbulkdownload'),
            'Base:{{courseshortname}}_{{todaysdate(Y-m-d)}}'
        ));

    }

    $ADMIN->add('localplugins', $settingspage);

    // create a link to the plugin index on the site's Administration > Reports page.
    $ADMIN->add(
        'reports',
        new admin_externalpage(
            'index_meccertbulkdownload',
            new lang_string('pluginname', 'local_meccertbulkdownload'),
            $CFG->wwwroot . "/local/meccertbulkdownload/index.php",
            'mod/customcert:viewallcertificates'
        )
    );
}
