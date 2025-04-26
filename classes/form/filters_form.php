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

        $mform->addElement('html', '<div class="filters-form-space-1"></div>');

        $mform->addElement('select', 'courseorcertificate', get_string('searchfor', 'local_meccertbulkdownload'), [
            'cor' => ucfirst(get_string('coursecompletion', 'local_meccertbulkdownload')),
            'cer' => ucfirst(get_string('certificateissuing', 'local_meccertbulkdownload')),
        ]);
        $mform->setType('courseorcertificate', PARAM_ALPHA);

        $mform->addElement('date_time_selector', 'datefrom', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('coursecompletionfrom',
            'local_meccertbulkdownload'), ['optional' => false]);
        $mform->setType('datefrom', PARAM_INT);

        $mform->addElement('date_time_selector', 'dateto', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('coursecompletionto',
            'local_meccertbulkdownload'), ['optional' => false]);
        $mform->setType('dateto', PARAM_INT);

        $mform->addElement('html', '<div class="filters-form-space-2"></div>');

        $mform->addElement('select', 'courseorcohort', get_string('searchfor', 'local_meccertbulkdownload'), [
            'coo' => ucfirst(get_string('cohort', 'local_meccertbulkdownload')),
            'cor' => ucfirst(get_string('courseandgroup', 'local_meccertbulkdownload')),
        ]);
        $mform->setType('courseorcohort', PARAM_ALPHA);

        $options = ['multiple' => false, 'includefrontpage' => false];
        $mform->addElement('autocomplete', 'coorte', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('cohort', 'local_meccertbulkdownload'),
            [], $options);
        $mform->setType('coorte', PARAM_ALPHANUM);

        $options = ['multiple' => false, 'includefrontpage' => false];
        $mform->addElement('autocomplete', 'corso', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('course'), [], $options);
        $mform->setType('corso', PARAM_ALPHANUM);

        $courseandgroup = [];
        $courseandgroup[] =& $mform->createElement('select', 'gruppocorso', get_string('group'),
            $this->_customdata['coursegroups']);
        $courseandgroup[] =& $mform->createElement('html', '<span id="cs-loader-1" class="cs-loader"></span>');
        $mform->addGroup($courseandgroup, 'courseandgroup', "&nbsp;&nbsp;&nbsp;&nbsp;" . get_string('coursegroup',
            'local_meccertbulkdownload'), [' '], false);
        $mform->setType('gruppocorso', PARAM_ALPHANUM);

        $mform->hideIf('coorte', 'courseorcohort', 'neq', 'coo');
        $mform->hideIf('corso', 'courseorcohort', 'neq', 'cor');
        $mform->hideIf('courseandgroup', 'courseorcohort', 'neq', 'cor');

        $mform->disabledIf('courseandgroup', 'courseorcohort', 'neq', 'pro');

        $mform->addElement('html', '<div class="filters-form-space-3"></div>');

        $buttonarray = [];
        $buttonarray[] =& $mform->createElement('submit', 'submitbuttonn', get_string('preview', 'local_meccertbulkdownload'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }
}
