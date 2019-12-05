<?php


namespace mod_assign;
use assign;
use csv_import_reader;
use stdClass;
/**
 * Class overrideimporter
 * @package mod_assign
 *
 * This is synonymous with the assignfeedback_offline_grade_importer class.
 */
class overrideimporter
{
    const IMPORTTYPE = 'overridesfile';
    public $importid;

    /**
     * @var csv_import_reader $csvreader - CSV importer class
     */
    private $csvreader;

    private $assignment;

    private $encoding;

    private $separator;

    private $groupnameindex;
    private $groupingnameindex;
    private $allowsubmissionsfromdateindex;
    private $duedateindex;
    private $cutoffdateindex;

    public function __construct($importid, assign $assignment, $encoding = 'utf-8', $separator = 'comma') {
        $this->importid = $importid;
        $this->assignment = $assignment;
        $this->encoding = $encoding;
        $this->separator = $separator;
    }

    /**
     * Scan the file and generate a preview / pre-flight report
     */
    function preview() {
        $o = '';

//        $o .= print_r($this,1);

        return $o;
    }

    /**
     * Parse a csv file and save the content to a temp file
     * Should be called before init()
     *
     * @param string $csvdata The csv data
     * @return bool false is a failed import
     */
    public function parsecsv($csvdata) {
        $this->csvreader = new csv_import_reader($this->importid, self::IMPORTTYPE);
        $this->csvreader->load_csv_content($csvdata, $this->encoding, $this->separator);
    }

    public function init() {
        if ($this->csvreader == null) {
            $this->csvreader = new csv_import_reader($this->importid, self::IMPORTTYPE);
        }
        $this->csvreader->init();

        $columns = $this->csvreader->get_columns();

        $strgroupname = get_string('groupname', 'group');
        $strgroupingname = get_string('groupingname', 'group');
        $straollowsubmissionsfrom = get_string('allowsubmissionsfromdate', 'assign');
        $strduedate = get_string('duedate', 'assign');
        $strcutoffdate = get_string('cutoffdate', 'assign');

        echo $strgroupingname;

        if ($columns) {
            foreach($columns as $index => $column) {
                if ($column == $strgroupname) {
                    $this->groupnameindex = $index;
                }
                if ($column == $strgroupingname) {
                    $this->groupingnameindex = $index;
                }
                if ($column == $straollowsubmissionsfrom) {
                    $this->allowsubmissionsfromdateindex = $index;
                }
                if ($column == $strduedate) {
                    $this->duedateindex = $index;
                }
                if ($column == $strcutoffdate) {
                    $this->cutoffdateindex = $index;
                }
            }
        }

        return true;
    }

    /**
     * Return the encoding for this csv import.
     *
     * @return string The encoding for this csv import.
     */
    public function get_encoding() {
        return $this->encoding;
    }

    /**
     * Return the separator for this csv import.
     *
     * @return string The separator for this csv import.
     */
    public function get_separator() {
        return $this->separator;
    }

    /**
     * Get the next row of data from the csv file (only the columns we care about)
     *
     * @return stdClass or false The stdClass is an object containing user, grade and lastmodified
     */
    public function next() {
        global $DB;
        $result = new stdClass();
        while ($record = $this->csvreader->next()) {
            $result->groupname = $record[$this->groupnameindex];
            $result->groupingname = $record[$this->groupingnameindex];
            $result->allowsubmissionsfromdate = $this->allowsubmissionsfromdateindex ? $record[$this->allowsubmissionsfromdateindex] : null;
            $result->duedate = $this->duedateindex ? $record[$this->duedateindex]: null;
            $result->cutoffdate = $this->cutoffdateindex? $record[$this->cutoffdateindex] : null;
            return $result;
            /*
            $idstr = $record[$this->idindex];
            // Strip the integer from the end of the participant string.
            $id = substr($idstr, strlen(get_string('hiddenuser', 'assign')));
            if ($userid = $this->assignment->get_user_id_for_uniqueid($id)) {
                if (array_key_exists($userid, $this->validusers)) {
                    $result->grade = $record[$this->gradeindex];
                    $result->modified = strtotime($record[$this->modifiedindex]);
                    $result->user = $this->validusers[$userid];
                    $result->feedback = array();
                    foreach ($this->feedbackcolumnindexes as $description => $details) {
                        if (!empty($details['index'])) {
                            $details['value'] = $record[$details['index']];
                            $result->feedback[] = $details;
                        }
                    }

                    return $result;
                }
            }*/
        }

        // If we got here the csvreader had no more rows.
        return false;
    }

    /**
     * Close the grade importer file and optionally delete any temp files
     *
     * @param bool $delete
     */
    public function close($delete) {
        $this->csvreader->close();
        if ($delete) {
            $this->csvreader->cleanup();
        }
    }
}