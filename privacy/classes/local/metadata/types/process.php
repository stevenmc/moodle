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
 * This file defines an item of metadata which encapsulates a user's preferences.
 *
 * @package    core_privacy
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_privacy\local\metadata\types;

defined('MOODLE_INTERNAL') || die();

/**
 * Data Process Type
 * @copyright 2018 Michael Hughes <michaelhughes@strath.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_privacy\local\metadata\types
 */
class process implements type {
    protected $name;

    protected $summary;

    protected $privacyfields;

    public function __construct($name, $summary = '', array $privacyfields = []) {
        $this->name = $name;
        $this->summary = $summary;
        $this->privacyfields = $privacyfields;
    }
    public function get_name()
    {
        return $this->name;
    }

    public function get_privacy_fields()
    {
        return $this->privacyfields;
    }

    public function get_summary()
    {
        return $this->summary;
    }
}