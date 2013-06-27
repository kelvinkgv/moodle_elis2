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

class elis2_student_form extends moodleform {
    function definition() {
    	global $CFG,$DB,$PAGE,$COURSE,$FULLSCRIPT,$USER;
    	
        $mform = $this->_form;
		$data  = $this->_customdata['data'];
				
		//require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_element', 'yui_animation', 'yui_dom-event','yui_ajax'));
		//$PAGE->requires->yui2_lib('yahoo');
		
		$PAGE->requires->yui_module('yahoo','//void');
		$PAGE->requires->yui_module('dom','//void');
		$PAGE->requires->yui_module('event','//void');
		$PAGE->requires->yui_module('element','//void');
		$PAGE->requires->yui_module('animation','//void');
		$PAGE->requires->yui_module('dom-event','//void');
		$PAGE->requires->yui_module('ajax','//void');
		
		$PAGE->requires->js('/blocks/elis2/elis2.js');
		$PAGE->requires->css('/blocks/elis2/styles.css');
		
		$q_arr = array();
			
		$my_book_obj = elis2_lib::get_my_book();
		
		if($data->book_search_term==''){
			# book recommendataion table
			
			$book_html = '<table>';
			$book_html.=elis2_lib::recrow(elis2_lib::borrowedbyleastperson(1),elis2_lib::borrowedbyleastperson(2),elis2_lib::popularbyborrows(),elis2_lib::popularbycohortgenderrating(),elis2_lib::popularbycohortrating());
			$book_html.=elis2_lib::recrow(elis2_lib::friendByRating(1),elis2_lib::friendByRating(2),elis2_lib::friendByRating(3),elis2_lib::wildcard(),elis2_lib::totallywildcard());
            $book_html .= '</table>';
		    $mform->addElement('html', $book_html);
		}
		else{
			$q_arr['book_by_latest'] = 1;	
			$q_arr['book_isbn'] = urlencode(trim($data->book_search_term));
			
			if(sizeof($q_arr)>0)	
				list($total_book,$book_obj) = elis2_lib::get_books_from_service($q_arr,$data->book_per_page,$data->curr_page);
			
			if(sizeof($book_obj)==0){
				unset($q_arr['book_isbn']);
				$q_arr['book_title'] = urlencode(trim($data->book_search_term));
				list($total_book,$book_obj) = elis2_lib::get_books_from_service($q_arr,$data->book_per_page,$data->curr_page);
			}
	
	    	if(sizeof($book_obj)==0){
				unset($q_arr['book_title']);
				$q_arr['book_author'] = urlencode(trim($data->book_search_term));
				list($total_book,$book_obj) = elis2_lib::get_books_from_service($q_arr,$data->book_per_page,$data->curr_page);
			}
		
		
			if(sizeof($q_arr)>0)	
				list($total_book,$book_obj) = elis2_lib::get_books_from_service($q_arr,$data->book_per_page,$data->curr_page);
			
			
		    
			# search result
			$book_html = '<table>';
			if($book_obj){
		    		    	
		    	$my_book_arr = array();
		    	for($a=0;$a<sizeof($my_book_obj);$a++){
		    		$my_book_isbn[] = $my_book_obj[$a]->isbn;
		    	}
		    	
		    	$ct = 0;
		    	
				foreach($book_obj as $book){
						
					if($ct%5 == 0) 
					$book_html.= '<tr class="elis2_book_tr">';
					/*if($data->book_has_thumbnail && $book['thumbnail']==''){
						continue; # skip those without thumbnail if 'must have thumbnail' is one of the criteria
					}*/
					
					$book_html.= '<td class="'.($ct%2==0?'elis2_book_td_1':'elis2_book_td_2').'" valign="top">';
					$book_html.='<div class="elies_book_title">'.$book['title'].'</div>';
					
					if(($book['authors']))
						$book_html.='<div class="elies_book_author">'.(sizeof($book['authors'])==0?'-':implode(",",$book['authors'])).'</div>';
					if(isset($book['publishedDate']) && $book['publishedDate']!='')
						$book_html.='<div class="elies_book_published_date">('.$book['publishedDate'].')</div>';
						
					$addbook_link = 'javascript:block_elis2.addBook2List(\''.$book['isbn'].'\',\''.$book['id'].'\',\'\','.$data->block_id.')';	
					$book_html.='<div><a href="'.$CFG->wwwroot.'/blocks/elis2/book.php?id='.$data->block_id.'&gbook_id='.$book['id'].'&isbn='.$book['isbn'].'&returnurl='.$FULLSCRIPT.'" title="'.get_string('click_2_view_book_details','block_elis2').'">';
					$book_html.=($book['thumbnail']==''?elis2_lib::gen_no_cover():'<img src="'.$book['thumbnail'].'">');
					$book_html.='</a></div>';
					$book_html.='<div id="action_div_'.$book['isbn'].'">';
					if($book['isbn']!=''){
						if(in_array($book['isbn'],$my_book_isbn))
							$book_html.='<br/><a href="'.$addbook_link.'"><img src="images/button-cross.png" title="'.get_string('remove_book','block_elis2').'"></a>';
						else {
							//$book_html.='<br/><input name="addbook_'.$book['isbn'].'" id="addbook_'.$book['isbn'].'" type="button" value="'.get_string("add_2_booklist","block_elis2").'" onClick="addBook2List(\''.$book['isbn'].'\')">';
							$book_html.='<br/><a href="javascript:block_elis2.addBook2List(\''.$book['isbn'].'\',\''.$book['id'].'\',\'\','.$data->block_id.')"><img src="images/button-add.png" title="'.get_string("add_2_booklist","block_elis2").'"></a>';
						}
					}
					$book_html.='</div>';
					$book_html.='</td>';
					if($ct%5 == 4)
					$book_html.= '</tr>';
					$ct++;
				}
			}
			else{
				if(sizeof($q_arr)>0)
					$book_html.='<tr><td>'.get_string('search_no_result','block_elis2').'</td></tr>';
			}
			$book_html .= '</table>';
			$mform->addElement('html', $book_html);
	    	if(sizeof($book_obj)>0 && $data->curr_page>0){
				$this->gen_pagination($data->book_per_page,$total_book);
				//$mform->addElement('html', $pagination);
			}
		}

		# activity summary
		$mform->addElement('html','<h4>'.get_string('profile_of_completed_activities','block_elis2').' <a href="javascript:void(0)" onClick="block_elis2.elis2OpenExplainDialog(0,\'\',\'explain\',800,600,1,\'Explain\',0,'.$data->block_id.')">['.get_string('explained','block_elis2').']</a></h4>');
		$mform->addElement('html','<h4>'.get_string('profile_of_completed_activities_desc','block_elis2').'</h4>');
		
		$mform->addElement('html','<div id="explain_div"><div id="explain_dialog" style="float:center;"><div style="overflow:auto;" id="explain_dialog_content"></div></div></div>');
		$mform->addElement('html',elis2_lib::render_act_summary($data->block_id,$COURSE->id));
		
		# search
		$mform->addElement('html', get_string('search_book_instruction','block_elis2'));
		$mform->addElement('text', 'book_search_term', '',$data->book_search_term);
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('search', 'block_elis2'),'onClick="if(document.getElementById(\'id_curr_page\'))document.getElementById(\'id_curr_page\').options[0].selected=true;"');
		$mform->addGroup($buttonarray, 'actionbtn', '', array(' '), false);
		
		$mform->addElement('html', '<hr/>');
		
		$mform->addElement('html', '<p>'.get_string('reading_record_for','block_elis2').' '.$USER->firstname.' '.$USER->lastname.' ['.get_string('last_update','block_elis2').' '.elis2_lib::get_user_last_booklist_update().']</p><hr/>');
		# my books
    	if(sizeof($my_book_obj)>0){
			$my_book_html = '<table id="my_booklist_table" width="100%">';
			$my_book_size = sizeof($my_book_obj);
			
			for($a=0;$a<$my_book_size;$a++){
				
				$my_book_html .= elis2_lib::gen_mybooklist_record($my_book_obj[$a],$a,$data->block_id,$my_book_obj[$a]->gbook_id);
			}
			$my_book_html .= '</table>';
			$mform->addElement('html', $my_book_html);
		}
		
		
		
		$mform->addElement('hidden', 'id',$data->block_id);
		$mform->addElement('hidden', 'book_per_page', $data->book_per_page==''?SEARCH_BOOK_PER_PAGE:$data->book_per_page);
       
        $this->set_data($data);
    }
    
    function validation($data, $files) {
        global $CFG;
        require_once('lib.php');
        $errors = array();
        
        return $errors;
    }
    
	function gen_pagination($book_per_page,$total){
		$mform	= $this->_form;
		
		/*$total_page = ceil($total/$book_per_page);
		$page_btn_arr = array();
		for($a=1;$a<=$total_page;$a++){
			$page_btn_arr[$a] = $a;
		}*/
		for($i=1;$i<=10;$i++)
			$page_btn_arr[$i] = $i;
		
		$mform->addElement('select', 'curr_page', get_string('go_to_page', 'block_elis2'),$page_btn_arr,'onChange="document.forms[\'mform1\'].submit()"');
	}
}
