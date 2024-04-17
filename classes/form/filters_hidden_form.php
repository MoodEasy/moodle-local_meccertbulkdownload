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

namespace local_meccertbulkdownload\form;

defined('MOODLE_INTERNAL') || die();

/**
 * Form with filters on certificate table.
 */
class filters_hidden_form extends \moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'courseorcertificate', $this->_customdata['courseorcertificate']);
        $mform->setType('courseorcertificate', PARAM_TEXT);

        $mform->addElement('hidden', 'datefrom', $this->_customdata['datefrom']);
        $mform->setType('datefrom', PARAM_INT);

        $mform->addElement('hidden', 'dateto', $this->_customdata['dateto']);
        $mform->setType('dateto', PARAM_INT);

        $mform->addElement('hidden', 'estimatedarchivesize', $this->_customdata['estimatedarchivesize']);
        $mform->setType('estimatedarchivesize', PARAM_FLOAT);

        $mform->addElement(
            'submit',
            'addtask', 
            get_string('formtemplatesubmit', 'local_meccertbulkdownload')
                . ' (~' . $this->_customdata['estimatedarchivesize'] . ' MB)',
            [
                'style' => 'background-color: #f58a0b; border-color: #eb8208; color: white; margin-bottom: 6px;',
                'onMouseOver' => 'this.style.backgroundColor=\'#eb8208\'',
                'onMouseOut' => 'this.style.backgroundColor=\'#f58a0b\''
            ]
        );
    }
}
