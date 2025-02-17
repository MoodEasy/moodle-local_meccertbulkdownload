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
 * Renderable for the table_pagination template used in index and list pages.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_meccertbulkdownload\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;
use moodle_url;

/**
 * Renderable for the results table section of the index page.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_pagination implements renderable, templatable {

    /**
     * @var string $recordsstatus
     */
    private $recordsstatus = null;

    /**
     * @var string $paginationurl
     */
    private $paginationurl = null;

    /**
     * @var array $perpageoptions
     */
    private $perpageoptions = null;

    /**
     * @var string $pagingbar
     */
    private $pagingbar = null;

    /**
     * Constructor for this object.
     *
     * @param string $recordsstatus
     * @param string $paginationurl
     * @param stdClass[] $perpageoptions
     * @param string $pagingbar
     */
    public function __construct(
        string $recordsstatus,
        string $paginationurl,
        array $perpageoptions,
        string $pagingbar,
    ) {
        $this->recordsstatus = $recordsstatus;
        $this->paginationurl = $paginationurl;
        $this->perpageoptions = $perpageoptions;
        $this->pagingbar = $pagingbar;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->recordsstatus = $this->recordsstatus;
        $data->paginationurl = $this->paginationurl;
        $data->perpageoptions = $this->perpageoptions;
        $data->pagingbar = $this->pagingbar;
        return $data;
    }
}
