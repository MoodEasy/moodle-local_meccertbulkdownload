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
 * Form for certificate search filters.
 * 
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_meccertbulkdownload\form;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for the form for certificate search filters.
 * 
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        
        $mform = $this->_form;

        $mform->registerNoSubmitButton('addtask');

        $mform->addElement('static', 'spazio1', '');
        // $mform->addElement('header', 'filterheaderfordates', get_string('coursecompletionfrom', 'local_meccertbulkdownload'));

        $mform->addElement('select', 'courseorcertificate', get_string('searchfor', 'local_meccertbulkdownload'), [
            'cor' => ucfirst(get_string('coursecompletion', 'local_meccertbulkdownload')),
            'cer' => ucfirst(get_string('certificateissuing', 'local_meccertbulkdownload'))
        ]);

        $mform->addElement('date_selector', 'datefrom', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('coursecompletionfrom', 'local_meccertbulkdownload'), ['optional' => false]);

        $mform->addElement('date_selector', 'dateto', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('coursecompletionto', 'local_meccertbulkdownload'), ['optional' => false]);

        // $mform->closeHeaderBefore('courseorcohort');
        $mform->addElement('static', 'spazio2', '');

        $mform->addElement('select', 'courseorcohort', get_string('searchfor', 'local_meccertbulkdownload'), [
            'coo' => ucfirst(get_string('cohort', 'local_meccertbulkdownload')),
            'cor' => ucfirst(get_string('courseandgroup', 'local_meccertbulkdownload'))
        ]);

        $mform->addElement('select', 'coorte', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('cohort', 'local_meccertbulkdownload'), $this->_customdata['cohorts']);
        
        $courseandgroup = array();
        $courseandgroup[] =& $mform->createElement('select', 'corso', get_string('course'), $this->_customdata['courses']);
        $courseandgroup[] =& $mform->createElement('select', 'gruppocorso', get_string('group'), $this->_customdata['coursegroups']);
        $courseandgroup[] =& $mform->createElement('html', '<span id="cs-loader-1" class="cs-loader"></span>');
        $mform->addGroup($courseandgroup, 'courseandgroup', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('courseandgroup', 'local_meccertbulkdownload'), array(' '), false);

        $mform->disabledIf('courseorcohort', 'coorte', 'neq', 'corno');
        $mform->disabledIf('coorte', 'courseorcohort', 'neq', 'corno');
        $mform->disabledIf('courseandgroup', 'courseorcohort', 'neq', 'coono');

        $mform->addElement('static', 'spazio3', '');

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbuttonn', get_string('preview', 'local_meccertbulkdownload'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}