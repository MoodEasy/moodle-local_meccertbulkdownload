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
 * Form for choosing file name template.
 * 
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_meccertbulkdownload\form;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for the form for choosing file name template.
 * 
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class templates_form extends \moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('select', 'templatepdf', get_string('pdfnametemplatesitem', 'local_meccertbulkdownload'), $this->_customdata['pdftamplates']);

        $mform->addElement('select', 'templatepack', get_string('archivenametemplatesitemsingular', 'local_meccertbulkdownload'), $this->_customdata['packtemplates']);

        $mform->addElement('hidden', 'from_filter_form', $this->_customdata['from_filter_form']);
        $mform->setType('from_filter_form', PARAM_TEXT);

        $this->add_action_buttons(get_string('cancel'), get_string('formtemplatesubmit', 'local_meccertbulkdownload'));
    }
}