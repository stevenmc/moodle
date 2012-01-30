<?php
//require_once('HTML/QuickForm/textarea.php');
//require_once $CFG->libdir.'/form/textarea.php';
require_once('../../../lib/form/textarea.php');
/**
 * HTML class for a textarea type element
 *
 * @author       Jamie Pratt
 * @access       public
 */
class MoodleQuickForm_gradecalc extends MoodleQuickForm_textarea{
    /**
     * Need to store id of form as we may need it for helpbutton
     *
     * @var string
     */
    var $_formid = '';
    /**
     * html for help button, if empty then no help
     *
     * @var string
     */
    var $_helpbutton='';

    var $_hiddenLabel=false;

    function MoodleQuickForm_gradecalc($elementName=null, $elementLabel=null, $attributes=null) {
        parent::MoodleQuickForm_textarea($elementName, $elementLabel, $attributes);
        	
    }
    /**
     * set html for help button
     *
     * @access   public
     * @param array $help array of arguments to make a help button
     * @param string $function function name to call to get html
     */
    function setHelpButton($helpbuttonargs, $function='helpbutton'){
        debugging('component setHelpButton() is not used any more, please use $mform->setHelpButton() instead');
    }
    /**
     * get html for help button
     *
     * @access   public
     * @return  string html for help button
     */
    function getHelpButton(){
        return $this->_helpbutton;
    }

    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }

    function toHtml(){
    	global $CFG;
        //if ($this->_hiddenLabel){
            $this->_generateId();
            $result =  '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>';
  			$result .= '<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>';
  			$result .= '<script src="'.$CFG->wwwroot.'/grade/edit/tree/jquery.jstree.js"></script>';
  			//$result .= '<script src="'.$CFG->wwwroot.'/grade/edit/tree/jquery.strath.gradeitemeditor.js"></script>';
  			$result .= '<script src="'.$CFG->wwwroot.'/grade/edit/tree/gradeeditor.js"></script>';
  			$result .= '<script src="'.$CFG->wwwroot.'/grade/edit/tree/json.js"></script>';
  			$result .= '<script src="'.$CFG->wwwroot.'/grade/edit/tree/js.php"></script>';
  			$result .= parent::toHtml();
            return 	$result;
            //return '<label class="accesshide" for="' . $this->getAttribute('id') . '" >' .
            //        $this->getLabel() . '</label>' . parent::toHtml();
        /*} else {
            return parent::toHtml();
        }*/
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    $caller calling object
     * @since     1.0
     * @access    public
     * @return    void
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                $this->_formid = $caller->getAttribute('id');
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    } // end func onQuickFormEvent
    /**
     * Slightly different container template when frozen.
     *
     * @return string
     */
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }
}
