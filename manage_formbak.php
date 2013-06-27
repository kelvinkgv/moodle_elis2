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

class elis2_manage_form extends moodleform {
    function definition() {
    	global $CFG,$DB,$PAGE,$COURSE,$FULLSCRIPT;
    	$PAGE->requires->js('/blocks/elis2/elis2.js');
    	
        $mform	= $this->_form;
		$data	= $this->_customdata['data'];
		$config	= $this->_customdata['config'];
		$config->maxrate = $config->maxrate == ''?DEFAULT_MAX_RATE:$config->maxrate;
        		
    	$return_url = $FULLSCRIPT;
        $mform->addElement('html', html_writer::tag(h3,get_string('my_booklist','block_elis2')));
        $mform->addElement('html', '<a href="'.$CFG->wwwroot.'/blocks/elis2/add.php?id='.$data->block_id.'&returnurl=' . $return_url .'"><img src="images/button-add.png" title="'.get_string('searcn_and_add_book','block_elis2').'"></a>');
        
        $book_obj = elis2_lib::get_my_book();
        $book_html = '<table>';
        if($book_obj){
	    
	    	for($a=0;$a<sizeof($book_obj);$a++){
				if($a%5 == 0) 
				$book_html.= '<tr class="elis2_book_tr">';
				$author_arr = explode("||",$book_obj[$a]->authors);
				for($i=0;$i<sizeof($author_arr);$i++)
					$author_arr[$i] = $author_arr[$i];
				
				$book_link = $CFG->wwwroot.'/blocks/elis2/book.php?id='.$data->block_id.'&isbn='.$book_obj[$a]->isbn.'&returnurl='.$return_url;
				
				$book_html.= '<td class="'.($a%2==0?'elis2_book_td_1':'elis2_book_td_2').'"><a href="'.$book_link.'">'.($book_obj[$a]->thumbnail==''?'-':'<img src="'.$book_obj[$a]->thumbnail.'">').'</a>';
				$book_html.='<br/><div class="elies_book_title"><a href="'.$book_link.'">'.$book_obj[$a]->title.'</a></div>';
				$book_html.='<div class="elies_book_author">'.(sizeof($author_arr)>0?implode(',',$author_arr):'-').'</div>';
				if($book_obj[$a]->publisheddate!='')
					$book_html.='<div class="elies_book_published_date">('.$book_obj[$a]->publisheddate.')</div>';
				$avg_rate = elis2_lib::get_book_avg_rating($book_obj[$a]->isbn);
				$book_html.='<div>'.elis2_lib::gen_book_rated_images($avg_rate->avg,$config->maxrate,1).'</div>';	
				$book_html.='<br/><a href="javascript:removeBookFromList('.$book_obj[$a]->isbn.',1)"><img src="images/button-cross.png" title="'.get_string('remove_book','block_elis2').'"></a>';	
				$book_html.='</td>';
				if($a%5 == 4)
				$book_html.= '</tr>';
			}
		}
		else{
			if(sizeof($q_arr)>0)
				$book_html.='<tr><td>'.get_string('search_no_result','block_elis2').'</td></tr>';
		}
		$book_html .= '</table>';
		$mform->addElement('html', $book_html);
        
		$this->set_data($data);
    }
    
    function validation($data, $files) {
        global $CFG;
        require_once('lib.php');
        $errors = array();
      
        return $errors;
    }
}
