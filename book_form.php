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

class elis2_book_form extends moodleform {
    function definition() {
    	global $CFG,$DB,$PAGE,$COURSE,$FULLSCRIPT,$USER;
    	$PAGE->requires->js('/blocks/elis2/elis2.js');
    	
        $mform	= $this->_form;
		$data	= $this->_customdata['data'];
		$config	= $this->_customdata['config'];
		$config->maxrate = $config->maxrate == ''?DEFAULT_MAX_RATE:$config->maxrate;
		
        $return_url = $FULLSCRIPT;
        
    	$book_obj = elis2_lib::get_book_info_with_my_rating($data->isbn);
    	
        $mform->addElement('html', html_writer::tag('h3',get_string('book_review','block_elis2').' : '.$book_obj->title.' - ('.implode(",",$book_obj->authors).')'));
        
        $my_books = elis2_lib::get_my_book();
        $my_book_size = sizeof($my_books);
        $in_booklist = false; 
        for($a=0;$a<$my_book_size;$a++){
        	if($my_books[$a]->isbn==$data->isbn)
        		$in_booklist = true;
        }
        if($in_booklist){
        	if(elis2_lib::check_submitted_activity($data->isbn,$USER->id)==false)
        		$mform->addElement('html', '<div id="action_div_'.$data->isbn.'"><a href="javascript:block_elis2.removeBookFromList('.$data->isbn.',\''.$data->gbook_id.'\','.$config->maxrate.')"><img src="images/button-cross.png" title="'.get_string('remove_book','block_elis2').'"></a></div>');
        }
        else
        	$mform->addElement('html', '<div id="action_div_'.$data->isbn.'"><a href="javascript:block_elis2.addBook2List('.$data->isbn.',\''.$data->gbook_id.'\','.$config->maxrate.')"><img src="images/button-add.png" title="'.get_string('search_and_add_book','block_elis2').'"></a></div>');
        
        
        $book_html = '<table width="100%">';
        if($book_obj){
	    		$book_html.= '<tr class="elis2_book_tr"><td colspan="2" style="text-align:center">'.($book_obj->thumbnail==''?elis2_lib::gen_no_cover():'<img src="'.$book_obj->thumbnail.'">');
				$book_html.= '<br/>';
				$book_html.= '<div id="book_rating_div_'.$data->isbn.'">'.elis2_lib::gen_book_rating_images($book_obj,$config->maxrate).'</div>';
				$book_html.= '<br/><br/>';
				$book_html.='</td></tr>';
				$book_html.= '<tr class="elis2_book_tr"><td>'.get_string('book_title','block_elis2').'</td><td>'.$book_obj->title.'</td></tr>';
				
				//'<a href="add.php?id='.$data->block_id.'&search_book_author='.$book_obj->authors[$i].'&returnurl='.$return_url.'">'.
				for($i=0;$i<sizeof($book_obj->authors);$i++)
					$author_arr[$i] = $book_obj->authors[$i];
				$book_html.='<tr class="elis2_book_tr"><td>'.get_string('book_author','block_elis2').'</td><td>'.(sizeof($author_arr)>0?implode(',',$author_arr):'-').'</td></tr>';
				$book_html.='<tr class="elis2_book_tr"><td>'.get_string('book_isbn','block_elis2').'</td><td>'.$book_obj->isbn.'</td></tr>';
				if($book_obj->publisheddate!='')
					$book_html.='<tr class="elis2_book_tr"><td>'.get_string('book_publisheddate','block_elis2').'</td><td>'.($book_obj->publisheddate==''?'-':$book_obj->publisheddate).'</td></tr>';
				if($book_obj->publisher!='')
					$book_html.='<tr class="elis2_book_tr"><td>'.get_string('book_publisher','block_elis2').'</td><td>'.($book_obj->publisher==''?'-':$book_obj->publisher).'</td></tr>';	
					
				$book_html.= '<tr class="elis2_book_tr"><td>'.get_string('book_description','block_elis2').'</td><td>'.($book_obj->description==''?'-':$book_obj->description).'</td></tr>';
				
			
		}
		else{
			
				$book_html.='<tr><td>'.get_string('search_no_result','block_elis2').'</td></tr>';
		}
		$book_html .= '</table>';
		$book_html .= '<p align="center"><input type="button" value="Back" onClick="history.go(-1)"/></p>';
		$book_html.= elis2_lib::render_book_student_submission($book_obj->isbn,$data->block_id);
		
		$mform->addElement('html', $book_html);
        
		
		//$buttonarray[] = &$mform->createElement('button', 'backbutton', get_string("back","block_elis2"),'onClick="history.go(-1)"');
		//$mform->addGroup($buttonarray, 'actionbtn', '', array(' '), false);
		
		$this->set_data($data);
    }
    
    function validation($data, $files) {
        global $CFG;
        require_once('lib.php');
        $errors = array();
      
        return $errors;
    }
}