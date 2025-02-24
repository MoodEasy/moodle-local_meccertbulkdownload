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
 * Renderable for the results table section of the index page.
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

/**
 * Renderable for the results table section of the index page.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class results_table_section implements renderable, templatable {

    /**
     * @var bool $archivescreation
     */
    private $archivescreation = null;

    /**
     * @var bool $archivescreationform
     */
    private $archivescreationform = null;

    /**
     * @var int $resultscount
     */
    private $resultscount = null;

    /**
     * @var string $resultstable
     */
    private $resultstable = null;

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
     * @var string $downloaddataformatselector
     */
    private $downloaddataformatselector = null;

    /**
     * Constructor for this object.
     *
     * @param bool $archivescreation
     * @param string $archivescreationform
     * @param int $resultscount
     * @param string $resultstable
     * @param string $recordsstatus
     * @param string $paginationurl
     * @param stdClass[] $perpageoptions
     * @param string $pagingbar
     * @param string $downloaddataformatselector
     */
    public function __construct(
        bool $archivescreation,
        string $archivescreationform,
        int $resultscount,
        string $resultstable,
        string $recordsstatus,
        string $paginationurl,
        array $perpageoptions,
        string $pagingbar,
        string $downloaddataformatselector
    ) {
        $this->archivescreation = $archivescreation;
        $this->archivescreationform = $archivescreationform;
        $this->resultscount = $resultscount;
        $this->resultstable = $resultstable;
        $this->recordsstatus = $recordsstatus;
        $this->paginationurl = $paginationurl;
        $this->perpageoptions = $perpageoptions;
        $this->pagingbar = $pagingbar;
        $this->downloaddataformatselector = $downloaddataformatselector;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->archivescreation = $this->archivescreation;
        $data->archivescreationform = $this->archivescreationform;
        $data->resultscount = $this->resultscount;
        $data->resultstable = $this->resultstable;
        $data->recordsstatus = $this->recordsstatus;
        $data->paginationurl = $this->paginationurl;
        $data->perpageoptions = $this->perpageoptions;
        $data->pagingbar = $this->pagingbar;
        $data->downloaddataformatselector = $this->downloaddataformatselector;
        return $data;
    }
}
