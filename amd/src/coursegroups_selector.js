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
 * JavaScript module for loading the groups of the selected course.
 *
 * @module     local_meccertbulkdownload/coursegroups_selector
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

var course = document.getElementById("id_corso");

export const init = () => {
    course.addEventListener('change', () => {
        getGroups();
    });
};

const getGroups = () => {
    var courseid = course.value;
    $("#id_gruppocorso").hide();
    $("#cs-loader-1").css('display', 'inline-block');
    $("#id_gruppocorso").empty();
    $.ajax({
        url: "coursegroups.php",
        type: "GET",
        dataType: "json",
        data: "cid=" + courseid,
        success: function (response) {
            var groups = response;
            setAllText();
            $.each(groups, function(index, group) {
                $("#id_gruppocorso").append($('<option>').val(group.id).text(group.name));
            });
        },
        error: function () {
            $("#id_gruppocorso").append($('<option>').val('error').text('ERROR'));
        },
        complete: function () {
            $("#cs-loader-1").css('display', 'none');
            $("#id_gruppocorso").show();
        }
    });
};

const setAllText = () => {
    $("#id_gruppocorso").append($('<option>').val('no').text(window.alltxt));
};