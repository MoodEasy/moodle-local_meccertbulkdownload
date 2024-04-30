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
 * Version information for local_meccertbulkdownload.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_meccertbulkdownload';  // To check on upgrade, that module sits in correct place.
$plugin->version   = 2024030100;        // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2017111300;        // Requires this Moodle version (3.4.0).
$plugin->release   = 'v1.0.0';
$plugin->maturity  = MATURITY_STABLE;
$plugin->dependencies = [
    'mod_customcert' => 2017111308,  // Requires the "Custom certificate" plugin.
];
