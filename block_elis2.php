<?php
//ini_set('display_errors',1); 
//error_reporting(E_ALL ^ E_NOTICE);
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
 * Form for editing elis2 block instances.
 *
 * @package   block_elis2
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license   http://www.gnu.org/copyleft/gpl.banners GNU GPL v3 or later
 */

class block_elis2 extends block_base {

    function init() {
        global $PAGE;
        
        $this->title = get_string('pluginname', 'block_elis2');
    }

    function applicable_formats() {
        return array('all' => true);
    }


    function instance_allow_multiple() {
        return true;
    }
	
    function get_content() {
        global $PAGE,$CFG;
        require_once($CFG->libdir . '/filelib.php');
            
        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = $this->genElis2Frontend();
        
        return $this->content;
    }
    
    function genElis2Frontend(){
    	global $CFG,$DB,$PAGE,$COURSE,$FULLSCRIPT;
    	require_once(dirname(__FILE__).'/lib.php');
    	$return_url = $FULLSCRIPT;
    	$PAGE->requires->css('/blocks/elis2/styles.css');
 	    if(elis2_lib::is_index_page()){       
	        $book_obj = elis2_lib::get_my_book(6);
	        $book_html = '<div align="center">';
	        if ($configdata = $DB->get_field('block_instances', 'configdata', array('id' => $this->instance->id))) {
	        	   $config = unserialize(base64_decode($configdata));
			}
	        
	        if($book_obj){
	        	$book_html.= '<div>';
		    	for($a=0;$a<sizeof($book_obj);$a++){
					$author_arr = explode("||",$book_obj[$a]->authors);
					for($i=0;$i<sizeof($author_arr);$i++)
						$author_arr[$i] = $author_arr[$i];
					$book_link = $CFG->wwwroot.'/blocks/elis2/book.php?id='.$this->instance->id.'&isbn='.$book_obj[$a]->isbn.'&gbook_id='.$book_obj[$a]->gbook_id.'&returnurl='.$return_url;
					$book_html.= '<span class="elis2_block_book_display">'.($book_obj[$a]->s_thumbnail==''?'-':'<a href="'.$book_link.'"><img src="'.$book_obj[$a]->s_thumbnail.'"></a>');
					$book_html.='</span>';
				}
				$book_html.= '</div>';
			}
			else{
				if(sizeof($q_arr)>0)
					$book_html.='<div>'.get_string('search_no_result','block_elis2').'</div>';
			}
			$book_html .= '</div>';
    	}
		
		if(isloggedin()){
			if(elis2_lib::is_index_page()){
				$btn = '<span class="elies_manage_book_link"><a href="'.$CFG->wwwroot.'/blocks/elis2/student.php?id=' . $this->instance->id . '&returnurl=' . $return_url .'">
                    '.get_string('my_reading_journal', 'block_elis2').'</a></span>';
			}else {
				

        		if(elis2_lib::has_report_right($this->instance->id,$COURSE->id)){
					$btn = '<span class="elies_manage_book_link"><a href="'.$CFG->wwwroot.'/blocks/elis2/class_report.php?id=' . $COURSE->id . '&returnurl=' . $return_url .'">
                    	'.get_string('class_reports', 'block_elis2').'</a></span>';
        		}
			}
		}
         
    	//<div class="header">'.get_string('pluginname', 'block_elis2').'</div>
    	$html = $book_html.$btn.'<br/>';
        
    	$context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);
        $admin_context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    	
    	return $html;
    }

	
    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_elis2');
        return true;
    }

    function instance_can_be_docked() {
    	return false;
    }
}
