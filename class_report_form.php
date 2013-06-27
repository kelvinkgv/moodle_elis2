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
 * minimalistic edit form
 *
 * @package   block_private_files
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class elis2_class_report_form extends moodleform {
    function definition() {
    	global $CFG,$DB,$PAGE,$COURSE,$FULLSCRIPT,$USER;
    	
        $mform = $this->_form;
		$data  = $this->_customdata['data'];
				
		/*require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_element', 'yui_animation', 'yui_dom-event','yui_ajax'));
		$PAGE->requires->js('/blocks/elis2/elis2.js');
		$PAGE->requires->js('/blocks/elis2/activity/js/dialog.js');*/
		
		
    	# period
      	$period_html = elis2_lib::get_report_period_selection();
		
		# report type
		//$report_arr 	= array('Stats','Cloud','Reading','Merits');
		$report_arr 	= array('Stats','Reading');
		$report_size 	= sizeof($report_arr);
		$reporttypetab_html = '<ul>';
		$reporttypecontent_html = ''; 
		for($i=0;$i<$report_size;$i++){
			$reporttypetab_html .= '<li><a href="#'.$report_arr[$i].'" onClick="document.getElementById(\'curr_type\').value=\''.$report_arr[$i].'\';block_elis2.elis2LoadReportData(\'\',\''.$report_arr[$i].'\')" style="font-size:100%">'.$report_arr[$i].'</a></li>';
			$reporttypecontent_html .= '<div id="'.$report_arr[$i].'_content"></div>';
		}
		$reporttypetab_html .= '</ul>';
		$reporttype_html = '<div id="report_div">'.$reporttypetab_html.'<div>'.$reporttypecontent_html.'</div></div>';
		
		$book_html = '<table width="100%">';
		$book_html.= '<tr><td>'.$period_html.'</td></tr>';
		$book_html.= '<tr><td>'.$reporttype_html.'</td></tr>';
		$book_html .= '</table>';
		
		
		$hidden_html = '<input type="hidden" id="curr_type" name="curr_type" value="'.$report_arr[0].'"/>';
		$hidden_html .= '<input type="hidden" id="course_id" name="course_id" value="'.$data->course_id.'"/>';
		
		$mform->addElement('html', $book_html);
		$mform->addElement('html', $hidden_html);
		
       $PAGE->requires->js_init_call('block_elis2.elis2LoadReportData',array($report_arr[0]));
        $this->set_data($data);
    }
}
