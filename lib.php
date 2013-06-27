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
 * Form for editing banners block instances.
 *
 * @package   block_elis2
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.banners GNU GPL v3 or later
 */

define('SEARCH_BOOK_PER_PAGE',10);
define('DEFAULT_MAX_RATE',5);
define('USER_YR',11);
define('USER_GENDER','M');
class elis2_lib{
	function block_elis2_pluginfile() {
	
	
	
	}
	
	/**
	 * Perform global search replace such as when migrating site to new URL.
	 * @param  $search
	 * @param  $replace
	 * @return void
	 */
	function block_elis2_global_db_replace($search, $replace,$block_id) {
	    global $DB;
	
	    //$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
	    $instances = $DB->get_recordset('block_instances', array('id' => $block_id));
	    foreach ($instances as $instance) {
	        // TODO: intentionally hardcoded until MDL-26800 is fixed
	        $config = unserialize(base64_decode($instance->configdata));
	        if (isset($config->text) and is_string($config->text)) {
	            $config->text = str_replace($search, $replace, $config->text);
	            $DB->set_field('block_instances', 'configdata', base64_encode(serialize($config)), array('id' => $instance->id));
	        }
	    }
	    $instances->close();
	}
	
	function get_books_from_service($q_arr,$book_per_page,$curr_page){
		/*
		 *  
			intitle: Returns results where the text following this keyword is found in the title.
		    inauthor: Returns results where the text following this keyword is found in the author.
		    inpublisher: Returns results where the text following this keyword is found in the publisher.
		    subject: Returns results where the text following this keyword is listed in the category list of the volume.
		    isbn: Returns results where the text following this keyword is the ISBN number.
		    lccn: Returns results where the text following this keyword is the Library of Congress Control Number.
		    oclc: Returns results where the text following this keyword is the Online Computer Library Center number.
		    zh-CN
		* 
		*/
		$result = array();
		$q = '';
		$startIndex = '';			
		if(isset($q_arr['book_title']) && $q_arr['book_title']!='')
			$q.= 'intitle:'.$q_arr['book_title'];
		if(isset($q_arr['book_author']) && $q_arr['book_author']!=''){
			if($q!='')
				$q.= '+';
			$q.= 'inauthor:'.$q_arr['book_author'];
		}
		if(isset($q_arr['book_isbn']) && $q_arr['book_isbn']!=''){
			if($q!='')
				$q.= '+';
			$q.= 'isbn:'.$q_arr['book_isbn'];
		}
		if(isset($q_arr['id']) && $q_arr['id']!=''){
			if($q!='')
				$q.= '+';
			$q.= 'id:'.$q_arr['id'];
		}
		$q.= '&maxResults='.$book_per_page;
		
		if(isset($q_arr['book_by_latest']) && $q_arr['book_by_latest'])
			$q.='&orderBy=newest';
		if($curr_page>1){
			//$startIndex = ($curr_page-1)*$book_per_page;
			$startIndex = ($curr_page-1)*$book_per_page;
		}
		if($startIndex!='')
			$q.= '&startIndex='.$startIndex;
			
		$q.='&printType=books'; //only show books, no magazine, other..etc
		//$q.='&langRestrict=en'; // restrict to show english only, not working apparently	
		$json_string = file_get_contents('https://www.googleapis.com/books/v1/volumes?q='.$q);
	
		$json_arr = json_decode($json_string);
			
		$bk_ct = 1;
		//$show_arr = array();
		if(isset($json_arr->items)){
			foreach($json_arr->items as $item){
				foreach($item as $bookdata){
		
					if(is_object($bookdata) && isset($bookdata->title)){
						$isbn = '';
						if(isset($bookdata->industryIdentifiers)){
							foreach($bookdata->industryIdentifiers as $identify){
								if($identify->type=='ISBN_13'){
									$isbn = $identify->identifier;
								}
							}
						}
						/*if(!in_array($isbn,$show_arr))
							$show_arr[] = $isbn;
						else
							continue;*/
							
						$result[] = array(  
											'book_ct'=>$startIndex==''?$bk_ct:$startIndex+$bk_ct,
											'title'=>isset($bookdata->title)?$bookdata->title:'',
											'id'=>isset($item->id)?$item->id:'',
											'authors'=>isset($bookdata->authors)?$bookdata->authors:'',
											'isbn'=>isset($isbn)?$isbn:'',	
											'publisher'=>isset($bookdata->publisher)?$bookdata->publisher:'',
											'publishedDate'=>isset($bookdata->publishedDate)?$bookdata->publishedDate:'',
											'description'=>isset($bookdata->description)?$bookdata->description:'',
											'categories'=>isset($bookdata->categories)?$bookdata->categories:'',
											'thumbnail'=>isset($bookdata->imageLinks->thumbnail)?$bookdata->imageLinks->thumbnail:'',
											'smallThumbnail'=>isset($bookdata->imageLinks->smallThumbnail)?$bookdata->imageLinks->smallThumbnail:''
						);
						$bk_ct++;
					}
				}
			}
		}
		return array($json_arr->totalItems,$result);
	}
	
	function bookAlreadyAdded2List($isbn){
		global $USER,$DB,$CFG;
		$sql = "SELECT count(*) as count FROM ".$CFG->prefix."block_elis2_booklist WHERE uid = '".$USER->id."' AND isbn = '".$isbn."'";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $book){
	    		if($book->count>0)
	    			return true;
	    			
			}
		}
	}
	
	function bookAlreadyAdded2Master($isbn){
		global $USER,$DB,$CFG;
		$sql = "SELECT count(*) as count FROM ".$CFG->prefix."block_elis2_book WHERE isbn = '".$isbn."'";
		
		if ($records = $DB->get_recordset_sql($sql)) {
			foreach($records as $book){
	    		if($book->count>0)
	    			return true;
	    	}
		}
	}
	
	function get_my_book($limit){
		global $USER,$DB,$CFG;
		$sql = "SELECT 
				b.gbook_id,b.id as book_id,b.isbn,b.thumbnail,b.s_thumbnail,b.title,b.authors,b.publisher,
				if(date_format(b.publisheddate,'%m')='00',date_format(b.publisheddate,'%Y'),if(date_format(b.publisheddate,'%d')='00',date_format(b.publisheddate,'%m/%Y'),date_format(b.publisheddate,'%d/%m/%Y'))) as publisheddate,b.description,b.timeinput,
				l.is_read 
				FROM ".$CFG->prefix."block_elis2_book as b inner join ".$CFG->prefix."block_elis2_booklist as l on b.isbn=l.isbn WHERE l.uid = '".$USER->id."' 
				ORDER BY l.timeinput desc";
		if($limit!='')
			$sql.= " limit ".$limit;
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $book){
	    		$return_arr[] = $book;		
			}
			return $return_arr;
		}	
	}
	
	function get_book_avg_rating($isbn){
		global $DB,$CFG;
		$sql = "SELECT 
					round(sum(rating)/count(rating)) as avg, count(rating) as rate_count 
				FROM 
					".$CFG->prefix."block_elis2_booklist
				WHERE 
					isbn = '".$isbn."'
				GROUP BY
					isbn";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $rate){
	    		return $rate;
	    	}
		}	
	}
	
	function get_book_info_with_my_rating($isbn){
		global $DB,$USER,$CFG;
		
		$sql = "SELECT 
					b.id as book_id,b.isbn,b.thumbnail,b.s_thumbnail,b.title,b.authors,b.publisher,
					if(date_format(b.publisheddate,'%m')='00',date_format(b.publisheddate,'%Y'),if(date_format(b.publisheddate,'%d')='00',date_format(b.publisheddate,'%m/%Y'),date_format(b.publisheddate,'%d/%m/%Y'))) as publisheddate,
					b.description,b.timeinput,l.rating 
				FROM 
					".$CFG->prefix."block_elis2_book as b
				LEFT JOIN	 
					".$CFG->prefix."block_elis2_booklist as l
				ON
					b.isbn=l.isbn AND l.uid='".$USER->id."' 
				WHERE 
					b.isbn = '".$isbn."'";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $book){
	    		
	    	}
	    	$book->authors		= explode("||",$book->authors);
	    	$rate_obj = self::get_book_avg_rating($isbn);
	    	$book->avg_rating	= isset($rate_obj->avg)?$rate_obj->avg:'';
	    	$book->rate_count	= isset($rate_obj->rate_count)?$rate_obj->rate_count:''; 
	    	
			return $book;
		}	
	}
	
	function gen_rating_image($small_img=false){
		global $CFG;
		return '<img src="'.$CFG->wwwroot.'/blocks/elis2/images/starOn.gif" '.($small_img?'width="25px"':'').'>';	
	}
	
	function gen_no_cover($small_img=false){
		global $CFG;
		return '<img src="'.$CFG->wwwroot.'/blocks/elis2/images/nocover.png" '.($small_img?'width="55px"':'').'>';	
	}
	
	
	
	function gen_rating_off_image($score_value='',$isbn='',$max_rate='',$small_img=false){
		global $CFG;
		$html = '<img src="'.$CFG->wwwroot.'/blocks/elis2/images/starOff.gif" '.($small_img?'width="25px"':'').' ';
		if($max_rate!='')
			$html.=' id="rate_'.$isbn.'_'.$score_value.'"';
		if($score_value!=''){
			$html .= 'onMouseOut="block_elis2.rateMouseOutEffect('.$score_value.',\''.$isbn.'\')"';
			$html .= 'onMouseOver="block_elis2.rateMouseOverEffect('.$score_value.',\''.$isbn.'\')"';
			$html .= 'onClick="block_elis2.rateBook('.$score_value.',\''.$isbn.'\',\''.$max_rate.'\',\''.$small_img.'\')"';
		}
		$html .= '>';

		return $html;
	}
	
	function gen_book_rating_images($book_obj,$max_rate,$show_rate_status=true){
		$html = '';
		
		$html.= self::gen_book_rated_images($book_obj->avg_rating,$max_rate,'',$book_obj->isbn).'<br/>';
		
		/*if($show_rate_status){
			if($book_obj->rate_count<=0)
				$html.= get_string('be_first_2_rate','block_elis2');
			else{
				if($book_obj->rate_count>1 && $book_obj->rating!='')
					$html.= str_replace('<!--no-->',$book_obj->rate_count-1,get_string('you_and_x_have_rated','block_elis2'));
				elseif($book_obj->rate_count>=1 && $book_obj->rating=='')	
					$html.= $book_obj->rate_count.' '.($book_obj->rate_count>1?get_string('have_rated','block_elis2'):get_string('has_rated','block_elis2'));
				elseif($book_obj->rate_count==1 && $book_obj->rating!='')
					$html.= get_string('you_have_rated','block_elis2');	
			}	
		}*/
		
		return $html;
	}
	
	function gen_my_rated_images($isbn,$score,$max_rate,$small_img=false){
		$html = '';
		for($a=1;$a<=$max_rate;$a++){
	    		if($score<$a)
	    			$html.= elis2_lib::gen_rating_off_image('',$isbn,'',$small_img);
	    		else
					$html.= elis2_lib::gen_rating_image($small_img);
	    	}
		
		$html.='<a href="javascript:block_elis2.rateAgain(\''.$isbn.'\',\''.$max_rate.'\',\''.$small_img.'\')">'.get_string('rate_again','block_elis2').'</a>';
		return $html;
	}
	
	function gen_book_rated_images($score,$max_rate,$small_img,$isbn){
		$html = '';
		for($a=1;$a<=$max_rate;$a++){
	    		if($score<$a)
	    			$html.= elis2_lib::gen_rating_off_image('',$isbn,'',$small_img);
	    		else
					$html.= elis2_lib::gen_rating_image($small_img);
	    	}
		return $html;
	}
	
	function get_esf_years(){
		global $DB;
		$return_arr = array();
		$sql = "SELECT yr,year FROM {local_esf_years} order by yr asc";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $year){
	    		$return_arr[] = array('yr'=>$year->yr,'year'=>$year->year);
			}
		}
		return $return_arr;
	}
	
	function get_user_last_booklist_update(){
		global $DB,$USER,$CFG;
		$return_arr = array();
		$sql = "SELECT date_format(timeinput,'%d/%m/%Y') as timeinput FROM ".$CFG->prefix."block_elis2_booklist WHERE uid = '".$USER->id."' ORDER BY timeinput desc limit 1";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $record){
	    		return $record->timeinput;
			}
		}
	}
	
	function gen_mybooklist_record($obj,$ct='',$block_id,$gbook_id=''){
		global $CFG,$FULLSCRIPT,$data,$USER;
		
		$book_link = $CFG->wwwroot.'/blocks/elis2/book.php?id='.$block_id.'&gbook_id='.$obj->gbook_id.'&isbn='.$obj->isbn.'&returnurl='.$FULLSCRIPT;
		$my_book_html  = '<tr id="my_booklist_'.$obj->isbn.'" class="booklist_class"><td width="10%">'.($obj->s_thumbnail==''?self::gen_no_cover(1):'<a href="'.$book_link.'"><img src="'.$obj->s_thumbnail.'"></a>').'</td>';
		$my_book_html .= '<td width="20%"><b>'.$obj->title.'</b>';
		$my_book_html .= '<br/>'.str_replace('||',', ',$obj->authors);
		$my_book_html .= '<br/>'.$obj->publisheddate.'</td>';
		$my_book_html .= '<td>'.get_string('read','block_elis2').': <input type="checkbox" value="1" name="'.$obj->isbn.'_read" onClick="block_elis2.bookRead(this.checked,\''.$obj->isbn.'\','.$ct.','.$block_id.')" '.(isset($obj->is_read) && $obj->is_read==true?'checked':'').'/>';
		$my_book_html.= '<br/><div id="activity_div_'.$obj->isbn.'">';
		if(isset($obj->is_read) && $obj->is_read==true){
			$my_book_html.= self::gen_rating($obj->isbn,$block_id);
			$my_book_html.= '<p id="activity_'.$ct.'_p">';
			$my_book_html.= self::gen_activity_icon($obj->isbn,$ct,$block_id,$gbook_id);
			$my_book_html.= '</p>';
		}
		$my_book_html.= '</div></td>';
		$my_book_html .= '<td width="10%" id="delete_btn_'.$obj->isbn.'">';
		
		if(self::check_submitted_activity($obj->isbn,$USER->id)==false)
			$my_book_html .= '<a href="javascript:block_elis2.removeBookFromList('.$obj->isbn.',\''.$obj->gbook_id.'\')"><img src="images/button-cross.png" title="'.get_string('remove_book','block_elis2').'"></a>';
		$my_book_html .= '</td>';
		$my_book_html .= '</tr>';
		$my_book_html .= '<tr id="my_booklist_'.$obj->isbn.'_hr"><td colspan="4"><hr/></td></tr>';
		
		return $my_book_html;
	}
	function gen_rating($isbn,$block_id){
		global $DB,$CFG,$USER;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;   
		
		//$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
		$instances = $DB->get_recordset('block_instances', array('id' => $block_id));
	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	    }		
		$book_obj = elis2_lib::get_book_info_with_my_rating($isbn);
		$rating_html = '<div id="rate_div_'.$isbn.'">';
		if($book_obj->rating=='')
			for($a=1;$a<=$config->maxrate;$a++)
				$rating_html .= self::gen_rating_off_image($a,$isbn,$config->maxrate,1);
		else{
				$rating_html .= self::gen_my_rated_images($isbn,$book_obj->rating,$config->maxrate,1);
		}
		$rating_html.= '</div>';
		return $rating_html;
	}
	
	function gen_activity_icon($isbn='',$ct='',$block_id,$gbook_id=''){
		global $DB,$CFG,$USER,$COURSE;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		
		
		$act_arr = get_directory_list($CFG->dirroot.'/blocks/elis2/activity','','',true);
		$act_size = sizeof($act_arr);
		//$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
		$instances = $DB->get_recordset('block_instances', array('id' => $block_id));
	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	    }
	   
	    $submitted_act = self::check_submitted_activity($isbn);
	    
	    if($submitted_act!=''){
	    	for($i=0;$i<$act_size;$i++){
	    		if($act_arr[$i]==$submitted_act){
	    			$index = $i;
	    			$loop = $index+1;	
	    		}
	    	}
	    	$html = get_string('your_submitted_act','block_elis2').':<br/>';
	    }else{
	    	
	    	 # get act order from config for display
			$order_str = '_order';
			foreach($config as $key=>$value){
				if(substr($key,-strlen($order_str))==$order_str){
					list($dum,$act_name,$dum2) = explode('_',$key);
					$act_order[$value-1] = $act_name;
				}
			}
			if(is_array($act_order) && sizeof($act_order)>0)
				$act_arr = $act_order;
	    	
	    	$index = 0;
	    	$loop = $act_size;
	    	$html = get_string('choose_activity','block_elis2').':<br/>';
	    	
	    }
	    $dialog_html = '';
	    
	$instances = self::get_main_elis_block_instance();
		foreach ($instances as $instance) {
		        $instance_id = $instance->id;
		    }
		for($i=$index;$i<$loop;$i++){
			$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$act_arr[$i].'/class_elis_plugin_'.$act_arr[$i].'.php';
			if(is_file($class_file)){
				$class_name = 'elis_'.$act_arr[$i];
				# if user has right to access this activity
				
				$has_act_on = false;
				if($config->{'elis_'.$act_arr[$i].'_'.$user_yr})
					$has_act_on = true;
				elseif(self::is_staff($instance_id,$COURSE->id)){
					for($x=7;$x<=13;$x++) 
						if($config->{'elis_'.$act_arr[$i].'_'.$x}){
							$has_act_on = true;
							break;
						}
						
				}	
				# if user has right to access this activity				
				
				
				if($has_act_on){
					include_once($class_file);
					$class_name = 'elis_plugin_'.$act_arr[$i];
					# check if class exists
					if(class_exists($class_name)==false)
						continue;
					# instantiate a class
					${$class_name} = new $class_name();
					$html.= '<a href="javascript:block_elis2.elis2OpenActDialog(\''.$ct.'\',\''.$isbn.'\',\''.$act_arr[$i].'\',800,500,true,\''.${$class_name}->title.'\','.(($submitted_act==$act_arr[$i])?1:0).','.$block_id.',\''.$gbook_id.'\');" class="'.$act_arr[$i].'_dialog_btn">'.${$class_name}->render_icon().'</a>';
					
					#dialog
					$dialog_html.='<div id="'.$act_arr[$i].'_dialog_'.$ct.'" style="float:center;">
					<div style="overflow:auto;" id="'.$act_arr[$i].'_dialog_content_'.$ct.'"></div></div>';
				}
			}
		}
		
		return $html.$dialog_html;
	}
	
	/*function gen_activity_icon_with_rating($isbn,$ct){
		global $DB,$CFG,$USER;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;   

		$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
	    foreach ($instances as $instance) {
	        // TODO: intentionally hardcoded until MDL-26800 is fixed
	        $config = unserialize(base64_decode($instance->configdata));
	        
	    }
	    
	    $act_arr = get_directory_list($CFG->dirroot.'/blocks/elis2/activity','','',true);
		$act_size = sizeof($act_arr);
		
		$book_obj = elis2_lib::get_book_info_with_my_rating($isbn);
		$rating_html.= '<div id="rate_div_'.$isbn.'">';
		if($book_obj->rating=='')
			for($a=1;$a<=$config->maxrate;$a++)
				$rating_html .= self::gen_rating_off_image($a,$isbn,$config->maxrate,1);
		else{
				$rating_html .= self::gen_my_rated_images($isbn,$book_obj->rating,$config->maxrate,1);
		}
		$rating_html.= '</div>';
		$dialog_html = '';
		for($i=0;$i<$act_size;$i++){
			$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$act_arr[$i].'/class_elis_plugin_'.$act_arr[$i].'.php';
			if(is_file($class_file)){
				$class_name = 'elis_'.$act_arr[$i];
				# if user has right to access this activity
				
				if($config->{'elis_'.$act_arr[$i].'_'.$user_yr}){
					include_once($class_file);
					$class_name = 'elis_plugin_'.$act_arr[$i];
					# check if class exists
					if(class_exists($class_name)==false)
						continue;
					# instantiate a class
					${$class_name} = new $class_name();
					$html.= '<a href="javascript:openActDialog(\''.$ct.'\',\''.$isbn.'\',\''.$act_arr[$i].'\',800,500,true,\''.${$class_name}->title.'\','.(self::has_submit_activity($isbn,$act_arr[$i])?1:0).');" class="'.$act_arr[$i].'_dialog_btn">'.${$class_name}->render_icon().'</a>';
					
					#dialog
					$dialog_html.='<div id="'.$act_arr[$i].'_dialog_'.$ct.'" style="float:center;">
					<div style="overflow:auto;" id="'.$act_arr[$i].'_dialog_content_'.$ct.'"></div></div>';
				}
			}
		}
		
		if($html!='')
			$html = get_string('choose_activity','block_elis2').'<p id="activity_'.$ct.'_p">'.$html.'</p>';
		$html = $rating_html.$html.$dialog_html;	 
		return $html;
	}*/
	function check_submitted_activity($isbn,$uid=''){
		global $USER,$DB,$CFG;
		
		if($uid=='') $uid = $USER->id;
		$sql = "SELECT 
					activity
				FROM 
					".$CFG->prefix."block_elis2_act_submission WHERE uid = '".$uid."' AND isbn='".$isbn."'";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $submission){
	    		
	    		return $submission->activity;		
			}
		}	
	}
	
	function count_submitted_activity($act){
		global $USER,$DB,$CFG;
		
		$sql = "SELECT 
					count(distinct isbn) as total
				FROM 
					".$CFG->prefix."block_elis2_act_submission WHERE uid = '".$USER->id."' AND activity='".$act."'";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $submission){
	    		
	    		return $submission->total;		
			}
		}	
	}
	/*function has_submit_activity($isbn,$activity){
		global $USER,$DB;
		
		$sql = "SELECT 
					count(*) as submitted
				FROM 
					{block_elis2_act_submission} WHERE uid = '".$USER->id."' AND activity = '".$activity."' AND isbn='".$isbn."'";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $submission){
	    		
	    		return $submission->submitted;		
			}
		}	
	}*/
	function popularbyborrows(){
		global $USER,$DB,$CFG;
		
		$gender = $USER->gender==''?USER_GENDER:$USER->gender;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		
		$sql = "SELECT 
					b.gbook_id,b.isbn,b.title,b.authors,b.s_thumbnail
				FROM 
					".$CFG->prefix."block_elis2_booklist as l
				INNER JOIN
					".$CFG->prefix."user as u
				ON
					l.uid = u.id
				INNER JOIN
					".$CFG->prefix."block_elis2_book as b
				ON
					b.isbn = l.isbn
				WHERE 
					u.id != '".$USER->id."' AND u.yr = ".$user_yr." AND u.gender = '".$gender."'
				order by RAND() limit 1
					";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	$book = '';
	    	foreach($records as $book){
	    		
	    	}
	    	
			return self::book(get_string('popular','block_elis2'),'('.get_string('by_cohort','block_elis2').'/'.get_string('gender_borrows','block_elis2').')',$book,'background:#ccffcc');
		}
		return get_string('no_record','block_elis2');
	}
	
	function borrowedbyleastperson($book){
		global $USER,$DB,$CFG;
		
		$sql = "SELECT 
					b.gbook_id,b.isbn,b.title,b.authors,b.s_thumbnail,count(l.isbn) as borrow
				FROM 
					".$CFG->prefix."block_elis2_booklist as l
				INNER JOIN
					".$CFG->prefix."user as u
				ON
					l.uid = u.id
				INNER JOIN
					".$CFG->prefix."block_elis2_book as b
				ON
					b.isbn = l.isbn
				GROUP BY
					l.isbn
				ORDER BY
					borrow asc,l.timeinput desc limit ".($book==1?'1':'1,1');
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $book){
	    		
	    	}
	    	
	    	if($book->title!='')
	    		return self::book(get_string('new_book','block_elis2'),'',$book,'background:#ccccff');
	    	else
	    		return self::book(get_string('new_book','block_elis2'),'','','background:#ccccff');	
		}
		return get_string('no_record','block_elis2');
	}
	
	function popularbycohortgenderrating(){
		global $USER,$DB,$CFG;
		
		$gender = $USER->gender==''?USER_GENDER:$USER->gender;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		
		$sql = "SELECT 
					b.gbook_id,b.isbn,b.title,b.authors,b.s_thumbnail
				FROM 
					".$CFG->prefix."block_elis2_booklist as l
				INNER JOIN
					".$CFG->prefix."user as u
				ON
					l.uid = u.id
				INNER JOIN
					".$CFG->prefix."block_elis2_book as b
				ON
					b.isbn = l.isbn
				WHERE 
					u.id != '".$USER->id."' AND u.yr = ".$user_yr." AND u.gender = '".$gender."' AND is_read=1 AND rating is not null
				order by RAND() limit 1
					";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	$book = '';
	    	foreach($records as $book){
	    		
	    	}
	    	
			return self::book(get_string('popular','block_elis2'),'('.get_string('by_cohort','block_elis2').'/'.get_string('gender_ratings','block_elis2').')',$book,'background:#ccffcc');
		}
		return get_string('no_record','block_elis2');
	}
	
	function randomString($length = 5) {
		$str = "";
	 	$characters = array_merge(range('A','Z'), range('a','z'));
	 	$max = count($characters) - 1;
	 	for ($i = 0; $i < $length; $i++) {
	  		$rand = mt_rand(0, $max);
	  		$str .= $characters[$rand];
	 	}
	 	return $str;
	}
	
	function wildcard(){
	
		$q_arr['book_title'] = self::randomString(2);
		list($total_book,$book_obj) = self::get_books_from_service($q_arr,40,1);
		$book_size = sizeof($book_obj);
		$book = '';
		for($i=0;$i<$book_size;$i++){
			if($book_obj[$i]['isbn']!='' && $book_obj[$i]['id']!='' ){//&& $book_obj[$i]['smallThumbnail']!=''
				$book->title = $book_obj[$i]['title'];
				$book->authors = isset($book_obj[$i]['authors'])?implode(',',$book_obj[$i]['authors']):'';
				$book->isbn = $book_obj[$i]['isbn'];
				$book->gbook_id = $book_obj[$i]['id'];
				$book->s_thumbnail = $book_obj[$i]['smallThumbnail'];
				return self::book(get_string('wildcard','block_elis2'),'',$book,'background:#ffcccc');
			}
		}
		
		return self::book(get_string('wildcard','block_elis2'),'',$book,'background:#ffcccc');
	}
	
	function totallywildcard(){
		
		$q_arr['book_title'] = self::randomString(2);
		list($total_book,$book_obj) = self::get_books_from_service($q_arr,40,1);
		$book_size = sizeof($book_obj);
		for($i=0;$i<$book_size;$i++){
			if($book_obj[$i]['isbn']!='' && $book_obj[$i]['id']!='' ){//&& $book_obj[$i]['smallThumbnail']!=''			 
				
				$book->title = $book_obj[$i]['title'];
				$book->gbook_id = $book_obj[$i]['id'];
				$book->authors = implode(',',$book_obj[$i]['authors']);
				$book->isbn = $book_obj[$i]['isbn'];
				$book->s_thumbnail = $book_obj[$i]['smallThumbnail'];
				return self::book(get_string('totally_wildcard','block_elis2'),'',$book,'background:#ffcccc');
			}
		}


		return self::book(get_string('totally_wildcard','block_elis2'),'',$book,'background:#ffcccc');
	}
	function popularbycohortrating(){
		global $USER,$DB,$CFG;
		
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		$sql = "SELECT 
					b.gbook_id,b.isbn,b.title,b.authors,b.s_thumbnail
				FROM 
					".$CFG->prefix."block_elis2_booklist as l
				INNER JOIN
					".$CFG->prefix."user as u
				ON
					l.uid = u.id
				INNER JOIN
					".$CFG->prefix."block_elis2_book as b
				ON
					b.isbn = l.isbn
				WHERE 
					u.id != '".$USER->id."' AND u.yr = ".$user_yr." AND is_read=1 AND rating is not null
				order by RAND() limit 1
					";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	$book = '';
	    	foreach($records as $book){
	    		
	    	}
	    	
			return self::book(get_string('popular','block_elis2'),'('.get_string('by_cohort_ratings','block_elis2').')',$book,'background:#ccffcc');
		}
		return get_string('no_record','block_elis2');
	}
	
	function friendbyrating($buddy_no){
		global $USER,$DB,$CFG;
       	
		$sql = "SELECT bid".$buddy_no." FROM {block_elis2_reading_buddy} WHERE uid = '".$USER->id."'";
       	$records = $DB->get_recordset_sql($sql);
		foreach($records as $buddy){
		}
		
		$buddySelection = '<select name="buddy'.$buddy_no.'" id="buddy'.$buddy_no.'" onChange="block_elis2.updateBuddy('.$buddy_no.',this.value)" '.($buddy->{'bid'.$buddy_no}==''|| $buddy->{'bid'.$buddy_no}=='0'?'':'style="display:none"').'>';
		$buddySelection.= '<option value="">'.get_string('please_choose_a_buddy','block_elis2').'</option>';
		$sql = "SELECT id, concat(firstname,' ',lastname) as name FROM ".$CFG->prefix."user WHERE id!=".$USER->id." order by firstname";
		$users = $DB->get_recordset_sql($sql);
		$buddy_name = '';
		foreach($users as $user){
			if($buddy->{'bid'.$buddy_no}==$user->id)
				$buddy_name = '<div id="buddy'.$buddy_no.'_name">#'.$buddy_no.' '.$user->name.'</div>';
			$buddySelection.= '<option value="'.$user->id.'" '.($buddy->{'bid'.$buddy_no}==$user->id?'selected':'').'>'.$user->name.'</option>';
		}
		$buddySelection.= '</select>';
		$change_buddy_button = '<input type="button" value="'.get_string('change_buddy','block_elis2').'" onClick="document.getElementById(\'buddy'.$buddy_no.'\').style.display=\'block\';document.getElementById(\'buddy'.$buddy_no.'_name\').style.display=\'none\';">';
		
		if($buddy->{'bid'.$buddy_no}=='' || $buddy->{'bid'.$buddy_no}=='0'){
			
       		$row[1]="<td style='border-right:#666666 1px solid;text-align:center;width:190px' >&nbsp;</td>";
	        $row[2] = "<td style='border-right:#666666 1px solid;text-align:center;width:190px'>".$buddySelection."</td>";    
	        $row[3] = "<td style='border-right:#666666 1px solid;text-align:center;width:190px' >&nbsp;</td>";
	        return $row;
		}
		
		$sql = "SELECT 
					b.gbook_id,b.isbn,b.title,b.authors,b.s_thumbnail
				FROM 
					".$CFG->prefix."block_elis2_booklist as l
				INNER JOIN
					".$CFG->prefix."user as u
				ON
					l.uid = u.id
				INNER JOIN
					".$CFG->prefix."block_elis2_book as b
				ON
					b.isbn = l.isbn
				WHERE 
					u.id = '".$buddy->{'bid'.$buddy_no}."' AND is_read=1 AND rating is not null
				order by RAND() limit 1
					";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	$book = '';
	    	foreach($records as $book){
	    		
	    	}
	    	
			return self::book(get_string('reading_buddy','block_elis2'),$buddySelection.$buddy_name.'<br/>'.$change_buddy_button,$book,'background:#ffffcc');
		}
		return get_string('no_record','block_elis2');
    }
	
	function book($title1,$title2,$rec,$css){
    	global $FULLSCRIPT,$data;
    	
    	
        $row[1]="<td class=\"elis_journal_td\" style='{$css}' ><b>";
        if($rec!=''){
    		$book_link = "book.php?id=".$data->block_id."&isbn=".(isset($rec->isbn)?$rec->isbn:'')."&gbook_id=".(isset($rec->gbook_id)?$rec->gbook_id:'')."&returnurl=".$FULLSCRIPT;
        	$row[1].="<a href='".$book_link."'>";
        }
        if(isset($rec->title))
        	$row[1].=self::cut_string($rec->title, 50);
        if($rec!=''){
        	$row[1].="</a>";
        }
        if(isset($rec->authors))
        	$row[1].="</b><br />".str_replace('||',',',$rec->authors)."</td>";
        if($rec){
            
                $row[2] = "<td class=\"elis_journal_td\" style='{$css}' ><a href='".$book_link."'>".(isset($rec->s_thumbnail)&&$rec->s_thumbnail!=''?"<img height=150 src='{$rec->s_thumbnail}' />":self::gen_no_cover())."</a></td>";    
            
        }else{
            $row[2] = "<td class=\"elis_journal_td\" style='{$css}' ><br />No match found<br /></td>";    
        }
        $row[3] = "<td class=\"elis_journal_td\" style='{$css}' ><i>{$title1}<br />{$title2}</i></td>";
        
        return $row;
    }
    
	function recrow($b1,$b2,$b3,$b4,$b5){
		
        $html = "<tr style='border-left:#666666 1px solid;border-top:#666666 1px solid;'>{$b1[1]}{$b2[1]}{$b3[1]}{$b4[1]}{$b5[1]}</tr>";    
        $html .="<tr  style='border-left:#666666 1px solid;'>{$b1[2]}{$b2[2]}{$b3[2]}{$b4[2]}{$b5[2]}</tr>";    
        $html .="<tr style='border-left:#666666 1px solid;border-bottom:#666666 1px solid;'>{$b1[3]}{$b2[3]}{$b3[3]}{$b4[3]}{$b5[3]}</tr>";
       
        return $html;     
    }
    
	function cut_string($text, $length, $symbol = "..."){
	     $length_text = strlen($text);
	     $length_symbol	 = strlen($symbol);
	
	     if($length_text <= $length || $length_text <= $length_symbol || $length <= $length_symbol)
	          return($text);
	     else
	          return(substr($text, 0, $length - $length_symbol) . $symbol);
	}
	
	function verifyBookRecord($book){
		global $DB,$CFG;
		$sql = "SELECT id as book_id FROM ".$CFG->prefix."block_elis2_book  WHERE isbn='".$book['isbn']."'";
		
		$obj = $DB->get_recordset_sql($sql);
		foreach($obj as $value){
			
		}
		//if($value->book_id!='')
		{
			
			/*$update_obj = '';
			$update_obj->gbook_id = $book['id'];
			
			$update_obj->book_id = $value->book_id;
			
			
			 
			$DB->update_record('block_elis2_book',$update_obj);
			die;
			$rate_obj->rating = null;
    		$rate_obj->id = $book_obj->id;
    		$DB->update_record('block_elis2_booklist',$rate_obj);*/
		}
		
	}
	function insertBookRecord($book){

		global $DB;
		    
	    $new_book 				= new stdClass();
	    $new_book->isbn 		=  $book['isbn'];
	    $new_book->gbook_id 	=  $book['id'];
	    if($book['thumbnail']!='')
	    $new_book->thumbnail	=  $book['thumbnail'];
	    if($book['smallThumbnail']!='')
	    $new_book->s_thumbnail	=  $book['smallThumbnail'];
	    if($book['title']!='')
	    $new_book->title		=  $book['title'];
	    if($book['authors'])
	    $new_book->authors		=  implode("||",$book['authors']);
	    if($book['publisher']!='')
	    $new_book->publisher	=  $book['publisher'];
	    if(isset($book['publishedDate']) && $book['publishedDate']){
	    	if(strlen($book['publishedDate'])==4){
	    		$year = $book['publishedDate'];
	    		$month = '00';
		    	$day 	= '00'; 
	    	}else{
		    	list($year,$month,$day) = explode("-",$book['publishedDate']);
		    	if($month=='') $month = '00';
		    	if($day=='') $day 	= '00';
	    	}
		    
		    		
		    if($month=='')$month = '00';
		    if($day=='')$day = '00';
		    $new_book->publisheddate=  $year.'-'.$month.'-'.$day;
	    }
	    if($book['description']!='')
	    	$new_book->description	=  $book['description'];
	    $new_book->timeinput 	=  date("Y-m-d H:i:s");
	    	    
    	$DB->insert_record('block_elis2_book',$new_book);
	}
	
	function get_activity_submitted_answer($isbn,$activity,$q_no,$uid){
		global $DB,$CFG;
		$sql = "SELECT 
					id,q_no,answer,
				if(date_format(timeinput,'%m')='00',date_format(timeinput,'%Y'),if(date_format(timeinput,'%d')='00',date_format(timeinput,'%m/%Y'),date_format(timeinput,'%d/%m/%Y'))) as timeinput
				FROM ".$CFG->prefix."block_elis2_act_submission WHERE uid = '".$uid."' AND activity = '".$activity."' AND q_no = '".$q_no."' AND isbn='".$isbn."'";
		$return_arr = array();
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $submission){
	    		$return_arr[] = $submission;		
			}
			return $return_arr;
		}	
	}
	
	function activitySelectionBox($question,$option_arr,$eid,$isbn,$js_function='',$return_ans=false,$uid,$show_result=false){
		global $USER;
		
		list($activity,$q) = explode("_",$eid);
		$q_no = str_replace('q','',$q);
		if($uid=='')$uid = $USER->id;
		$ans_obj = self::get_activity_submitted_answer($isbn,$activity,$q_no,$uid);
		
		
		
		if($show_result==true){
			
			$opt_size = sizeof($option_arr);
			for($a=0;$a<$opt_size;$a++){
				if($ans_obj[0]->answer==$option_arr[$a][0]){
					$label = $option_arr[$a][0];
					$answer =$option_arr[$a][1];
					break;
				}
			}
			$html = '<tr><td width="30%"><label id="'.$eid.'_question"><b>'.$label.'</b></label></td>';
				$html.='<td>'.$answer;
		}else{
			$html = '<tr><td width="30%"><label id="'.$eid.'_question">'.$question.'</label></td>';
			$html.='<td>';
			$html.='<select name="'.$eid.'" id="'.$eid.'" '.$js_function.' class="elis2_act_ans">';
			$opt_size = sizeof($option_arr);
			for($a=0;$a<$opt_size;$a++){
				$html.='<option value="'.$option_arr[$a][0].'" '.(($ans_obj[0]->answer==$option_arr[$a][0])?'selected':'').'>'.$option_arr[$a][1].'</option>';
			}
			$html.='</select>';
		}
		
		if($show_result==false){
			if($ans_obj[0]->id!='')
				$html.='<input type="hidden" name="submitted_'.$eid.'" id="submitted_'.$eid.'" value="'.$ans_obj[0]->id.'" class="act_submitted_field">';
			$html.='</td></tr>';
			
			$js = 'document.getElementById(\''.$this->act_prefix.'_q1_question\').innerHTML = document.getElementById(\''.$eid.'\').value;';
		}
		if($return_ans)
			return array($js,$html,$ans_obj[0]);
		else
			return array($js,$html);
	}
	
	function activityTextboxRow($question,$eid,$isbn,$word_count,$min=0,$max=0,$uid='',$show_result=false){
		global $USER;
		$js = array();
		list($activity,$q) = explode("_",$eid);
		$q_no = str_replace('q','',$q);
		
		if($uid=='')$uid = $USER->id;
		$ans_obj = self::get_activity_submitted_answer($isbn,$activity,$q_no,$uid);
		
		
		$html = '<tr><td width="30%"><label><b>'.$question.'</b></label></td><td>';
		if($show_result){
			$html.= nl2br($ans_obj[0]->answer);
		}else{
			$html.='<textarea id="'.$eid.'" name="'.$eid.'" class="elis2_act_ans" rows="6" style="width:90%">';
			if(isset($ans_obj[0]->answer) && $ans_obj[0]->answer!='')
				$html.=$ans_obj[0]->answer;
			$html.='</textarea>';
		}
		
		if($show_result==false){
			if($word_count){
				$html.= '<br/><span style="color:red"><b>Length: <span id="'.$eid.'_wc_word"></span> Target:'.$min.'-'.$max.'</b><span>';
				$html.='<br/><div id="'.$eid.'_wc_bar"></div>';
				$html .= self::activityTextboxWCField($eid,$min,$max);
			}
			if(isset($ans_obj[0]->id) && $ans_obj[0]->id!='')
				$html.='<input type="hidden" name="submitted_'.$eid.'" id="submitted_'.$eid.'" value="'.$ans_obj[0]->id.'" class="act_submitted_field">';
			if($word_count)
				$html.='<input type="hidden" name="wc_criteria_'.$eid.'" id="wc_criteria_'.$eid.'" value="0" class="wc_criteria">';
			
			$html.='</td></tr>';
			
			if($word_count){
				$js[] = "block_elis2.elis2UpdateCounterBox('".$eid."')";
				$js[] = 'block_elis2.elis2InitCheckText("'.$eid.'")';
			}		
		}
		return array($js,$html);
	}
	
	function activityCheckboxRow($question,$cb_label,$eid,$isbn,$uid=''){
		global $USER;
		list($activity,$q) = explode("_",$eid);
		$q_no = str_replace('q','',$q);
		
		if($uid=='')$uid = $USER->id;
		$ans_obj = self::get_activity_submitted_answer($isbn,$activity,$q_no,$uid);
				
		$html = '<tr><td><label>'.$question.'</label></td><td><input type="checkbox" id="'.$eid.'" name="'.$eid.'" value="1" class="elis2_act_ans" '.(isset($ans_obj[0]->answer) && $ans_obj[0]->answer==1?'checked':'').'/>
		<label for="'.$eid.'">'.$cb_label.'</lable>';
		if(isset($ans_obj[0]->id) && $ans_obj[0]->id!='')
			$html.='<input type="hidden" name="submitted_'.$eid.'" id="submitted_'.$eid.'" value="'.$ans_obj[0]->id.'" class="act_submitted_field">';
		$html.='</td></tr>';
		
		return $html;
	}
	
	function activityTextboxWCField($id,$min,$max){
		$html= '<input type="hidden" id="'.$id.'_min_word" value="'.$min.'"/>';
		$html.= '<input type="hidden" id="'.$id.'_max_word" value="'.$max.'"/>';
		return $html;
	}
	
	function activityQuestion($text){
		$html= '<tr><td colspan="2"><b>'.$text.'</b></td></tr>';
		return $html;
	}
	
	function render_act_summary($block_id,$course_id){
		global $DB,$CFG,$USER;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
				
		//$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
		$instances = $DB->get_recordset('block_instances', array('id' => $block_id));
	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	    }
		# get act order from config for display
		$order_str = '_order';
		foreach($config as $key=>$value){
			if(substr($key,-strlen($order_str))==$order_str){
				list($dum,$act_name,$dum2) = explode('_',$key);
				$act_order[$value-1] = $act_name;
			}
		}
		if(is_array($act_order) && sizeof($act_order)>0)
			$act_arr = $act_order;
		else 
			$act_arr = get_directory_list($CFG->dirroot.'/blocks/elis2/activity','','',true);
		
		
	    
	   
	    
		return '<table>'.self::render_act_summary_icons($act_arr,$course_id,$config).'</table>';	
	}
	
	function render_act_summary_icons($act_arr,$course_id,$config){
		global $DB,$CFG,$USER;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		
		 $icon_html = '<tr id="act_summary_tr">';
	   	 $count_attempt = '';
	    
	    $instances = self::get_main_elis_block_instance();
		foreach ($instances as $instance) {
		        $instance_id = $instance->id;
		    }
	    $act_size = sizeof($act_arr);
		for($i=0;$i<$act_size;$i++){
			$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$act_arr[$i].'/class_elis_plugin_'.$act_arr[$i].'.php';
			if(is_file($class_file)){
				$class_name = 'elis_'.$act_arr[$i];
				
				$has_act_on = false;
				if($config->{'elis_'.$act_arr[$i].'_'.$user_yr})
					$has_act_on = true;
				elseif(self::is_staff($instance_id,$course_id)){
					for($ct=7;$ct<=13;$ct++) 
						if($config->{'elis_'.$act_arr[$i].'_'.$ct}){
							$has_act_on = true;
							break;
						}
						
				}	
				# if user has right to access this activity				
				if($has_act_on){
					include_once($class_file);
					$class_name = 'elis_plugin_'.$act_arr[$i];
					# check if class exists
					if(class_exists($class_name)==false)
						continue;
					# instantiate a class
					${$class_name} = new $class_name();
					$icon_html.= '<td style="padding:0px">'.${$class_name}->render_icon().'</td>';
					$count_attempt.= '<td style="font-weight:bold;font-size:22px;color:#000000;text-align:center;padding:0px" id="'.$act_arr[$i].'_icon_sum">'.self::count_submitted_activity(${$class_name}->act_prefix).'</td>'; 
				}
			}
		}
		$icon_html .= '</tr>';
		return $icon_html.$count_attempt; 
	}
	function get_book_student_submission($isbn){
		global $USER,$DB,$CFG;
		$return_arr = array();
		$sql = "SELECT 
					s.uid,s.activity,concat(u.firstname,' ',u.lastname) as name
				FROM 
					".$CFG->prefix."block_elis2_act_submission as s
				INNER JOIN
					".$CFG->prefix."user as u
				ON
					u.id = s.uid 
				WHERE 
					s.isbn='".$isbn."'
				GROUP BY
					s.uid,s.activity
				ORDER BY
					s.timeinput desc";
		
	    if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $submission){
	    		$return_arr[] = $submission;		
			}
		}
		return $return_arr;
	}
	
	function render_book_student_submission($isbn,$block_id){
		global $DB,$CFG,$USER;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		
		$html = '';
		
		//$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
		$instances = $DB->get_recordset('block_instances', array('id' => $block_id));
	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	    }
		
		$sub_obj = self::get_book_student_submission($isbn);
		
		if(!is_array($sub_obj))
			return false;
		if(sizeof($sub_obj)==0)
			return false;
				
		/*$sub_size = sizeof($sub_obj);
		
		
		for($a=0;$a<$sub_size;$a++){
			$std_arr[$sub_obj[$a]->timeinput][$sub_obj[$a]->uid][$sub_obj[$a]->q_no] = $sub_obj[$a]; 
		}
			echo '<pre>';
			
		echo '</pre>';
		die;
		$html = '<table>';
		foreach($std_arr as $time=>$obj){
			
			foreach($obj as $sid=>$std_obj){
				$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$std_obj[1]->activity.'/class_elis_plugin_'.$std_obj[1]->activity.'.php';
				
				if(is_file($class_file)){
					$class_name = 'elis_plugin'.$std_obj[1]->activity;
					# if user has right to access this activity
					
					if($config->{'elis_'.$std_obj[1]->activity.'_'.$user_yr}){
						include_once($class_file);
						$class_name = 'elis_plugin_'.$std_obj[1]->activity;
						# check if class exists
						if(class_exists($class_name)==false)
							continue;
						# instantiate a class
						${$class_name} = new $class_name();
						if(method_exists(${$class_name},'render_result'))
							$html.= ${$class_name}->render_result($std_obj);
				
					}
				}
			}
		}*/
		$sub_size = sizeof($sub_obj);
		for($a=0;$a<$sub_size;$a++){
			$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$sub_obj[$a]->activity.'/class_elis_plugin_'.$sub_obj[$a]->activity.'.php';
				
				if(is_file($class_file)){
					$class_name = 'elis_plugin'.$sub_obj[$a]->activity;
					# if user has right to access this activity
					
					if($config->{'elis_'.$sub_obj[$a]->activity.'_'.$user_yr}){
						include_once($class_file);
						$class_name = 'elis_plugin_'.$sub_obj[$a]->activity;
						# check if class exists
						if(class_exists($class_name)==false)
							continue;
						# instantiate a class
						${$class_name} = new $class_name();
						if(method_exists(${$class_name},'render_result')){
							${$class_name}->isbn = $isbn; 
							$html.= ${$class_name}->render_result($sub_obj[$a]->uid,$sub_obj[$a]->name);
						}
				
					}
				}
		}
		$html .= '</table>';
		return $html;
	}
	
	function is_index_page(){
		global $COURSE; 
		return $COURSE->id==1;
	}
	
	function get_course_participants($course_id){
		global $DB,$CFG;
		//$context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);
        //$admin_context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
		
		$sql = 'SELECT 
					u.id
				FROM 
					'.$CFG->prefix.'user u
				INNER JOIN 
					'.$CFG->prefix.'role_assignments ra ON u.id = ra.userid
				INNER JOIN 
					{role} r ON ra.roleid = r.id
				INNER JOIN 
					'.$CFG->prefix.'context c ON ra.contextid = c.id
				WHERE 
					c.contextlevel = '.CONTEXT_COURSE.'
				AND 
					c.instanceid = '.$course_id.'
				AND 
					r.shortname = "student"';
		
		if ($records = $DB->get_recordset_sql($sql)) {
			$participant_arr = array(); 
	    	foreach($records as $participant){
	    		$participant_arr[] = $participant->id;
	    	}
	    	return $participant_arr;
		}		
	}
	
	function get_activity_report_table($course_id,$from,$to){

		$act_obj		= self::get_activity_stats($course_id,$from,$to);
		$act_size 	= sizeof($act_obj);
		$act_html	= '<table border="1" width="100%" height="100%"><tr><td>'.get_string('name','block_elis2').'</td><td>'.get_string('activities','block_elis2').'</td></tr>';
		if($act_size==0){
			$act_html.='<tr><td colspan="2">'.get_string('no_record','block_elis2').'</td></tr>';
		}
		else{ 
			for($i=0;$i<$act_size;$i++){
				$act_html	.= '<tr><td>'.$act_obj[$i]->name.'</td><td>'.$act_obj[$i]->total.'</td></tr>';
			}
		}
		$act_html	.= '</table>';
		return $act_html;	
	}
	
	
	function get_reading_stats($course_id,$from,$until){
		global $DB,$CFG;
		
		$participant_obj = elis2_lib::get_course_participants($course_id);
		
		$cond = $from=='0'?" AND bl.timeinput <= $until":" AND bl.timeinput between $from and $until";
		$sql="SELECT
				    b.title,b.authors,concat(u.firstname,' ',u.lastname) as name, u.id,bl.timeinput as borrow_time,b.isbn
				FROM
				    ".$CFG->prefix."block_elis2_book AS b
				INNER JOIN
				    ".$CFG->prefix."block_elis2_booklist as bl
				ON
				    b.isbn = bl.isbn
				INNER JOIN
				    ".$CFG->prefix."user AS u
				ON
				    u.id = bl.uid
				WHERE
					1 ".$cond."
				AND
					u.id in (".implode(",",$participant_obj).")
				ORDER BY
					u.lastname,u.firstname";
		
		$records = $DB->get_recordset_sql($sql);
			 
	    foreach($records as $stats){
	    	$stats_obj[] = $stats;
	    }
	    return $stats_obj;
	}
	
	function get_main_elis_block_instance(){
		global $DB;
				 
		return $DB->get_recordset('block_instances', array('blockname' => 'elis2','pagetypepattern'=>'site-index'));
	}
	
	function get_reading_report_table($course_id,$from,$to){
		global $DB,$CFG,$USER;
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		
		$reading_obj	= self::get_reading_stats($course_id,$from,$to);
		$reading_size 	= sizeof($reading_obj);
		
		$reading_html	= '<table border="1" width="100%" height="100%">';
		if($reading_size==0){
			$reading_html.='<tr><td colspan="2">'.get_string('no_record','block_elis2').'</td></tr>';
		}
		else{ 
			for($i=0;$i<$reading_size;$i++){
				$std_arr[$reading_obj[$i]->id][] = $reading_obj[$i]; 
			}
			
			$instances = self::get_main_elis_block_instance(); 
			
		    foreach ($instances as $instance) {
		        $config = unserialize(base64_decode($instance->configdata));
		    }
			
			foreach($std_arr as $uid=>$std){
				$reading_html.='<tr><td colspan="2"><b>'.$std[0]->name.'</b></td></tr>';
				$std_read_size = sizeof($std);	
				for($i=0;$i<$std_read_size;$i++){
					$submitted_act = self::check_submitted_activity($std[$i]->isbn,$uid);
					
					
					if($submitted_act){
						$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$submitted_act.'/class_elis_plugin_'.$submitted_act.'.php';
						if(is_file($class_file)){
							$class_name = 'elis_'.$submitted_act;
											
							include_once($class_file);
							$class_name = 'elis_plugin_'.$submitted_act;
							# check if class exists
							if(class_exists($class_name)==false)
								continue;
							# instantiate a class
							${$class_name} = new $class_name();
							${$class_name}->isbn = $std[$i]->isbn; 		
							$act_html = ${$class_name}->render_result($uid);
						}					
					}else{
						$act_html = get_string('no_activity','block_elis2');	
					}
					
					$reading_html.='<tr>
									<td width="30%" valign="top"><b>'.$std[$i]->title.'</b><br/>'.str_replace('||',', ',$std[$i]->authors).'<br/>'.$std[$i]->borrow_time.'</td>
									<td>'.$act_html.'</td></tr>';
				//	$reading_html.='<tr><td colspan="2"><hr/></td></tr>';
				}	
				$reading_html.='<tr><td colspan="2"><hr/></td></tr>';
			}
		}
		
		$reading_html	.= '</table>';
		return $reading_html;	
	}
	
	function get_popular_report_table($course_id,$from,$to){
		$popluar_obj		= self::get_popular_stats($course_id,$from,$to);
		$popluar_size 	= sizeof($popluar_obj);
		$popluar_html	= '<table border="1" width="100%" height="100%"><tr><td>'.get_string('book','block_elis2').'</td><td>'.get_string('borrows','block_elis2').'</td></tr>';
		if($popluar_size==0){
			$popluar_html.='<tr><td colspan="2">'.get_string('no_record','block_elis2').'</td></tr>';
		}
		else{ 
			for($i=0;$i<$popluar_size;$i++){
				$popluar_html	.= '<tr><td>'.$popluar_obj[$i]->title.'</td><td>'.$popluar_obj[$i]->total.'</td></tr>';
			}
		}
		$popluar_html	.= '</table>';
		return $popluar_html;	
	}
	
	function get_borrow_report_table($course_id,$from,$to){
		$borrow_obj		= self::get_borrow_stats($course_id,$from,$to);
		$borrow_size 	= sizeof($borrow_obj);
		$borrow_html	= '<table border="1" width="100%" height="100%"><tr><td>'.get_string('name','block_elis2').'</td><td>'.get_string('borrows','block_elis2').'</td></tr>';
		if($borrow_size==0){
			$borrow_html.='<tr><td colspan="2">'.get_string('no_record','block_elis2').'</td></tr>';
		}
		else{
			for($i=0;$i<$borrow_size;$i++){
				$borrow_html	.= '<tr><td>'.$borrow_obj[$i]->name.'</td><td>'.$borrow_obj[$i]->total.'</td></tr>';
			}
		}
		$borrow_html	.= '</table>';
		return $borrow_html;	
	}
	
	function get_borrow_stats($course_id,$from,$until){
		global $DB, $CFG;
		$participant_obj = elis2_lib::get_course_participants($course_id);
		$sql = "SELECT
					count(b.id) as total, concat(u.firstname,' ',u.lastname) as name
				FROM
					".$CFG->prefix."block_elis2_booklist AS b
				INNER JOIN
					".$CFG->prefix."user AS u
				ON
					u.id = b.uid
				WHERE
					b.timeinput between $from and $until
				AND
					u.id in (".implode(",",$participant_obj).")		
				GROUP BY 
					u.id 
				ORDER BY
					total desc";
		
		$records = $DB->get_recordset_sql($sql);
			 
	    foreach($records as $stats){
	    	$stats_obj[] = $stats;
	    }
	    return $stats_obj;
	}
	
	function get_borrow_gender_stats($course_id,$from,$until){
		global $DB,$CFG;
		$participant_obj = elis2_lib::get_course_participants($course_id);
		
		if(sizeof($participant_obj)>0){
			$sql = "SELECT
						u.gender,count(u.id) as total
					FROM
						".$CFG->prefix."block_elis2_booklist AS b
					INNER JOIN
						".$CFG->prefix."user AS u
					ON
						u.id = b.uid
					WHERE
						b.timeinput between $from and $until
					AND
						u.id in (".implode(",",$participant_obj).")		
					GROUP BY 
						u.gender";
			
			$records = $DB->get_recordset_sql($sql);
				 
		    foreach($records as $stats){
		    	$stats_obj[$stats->gender] = $stats->total;
		    }
		    return $stats_obj;
		}
	}
	
	function get_popular_stats($course_id,$from,$until){
		global $DB,$CFG;
		
		$cond = $from=='0'?" AND bl.timeinput <= $until":" AND bl.timeinput between $from and $until";
		$participant_obj = elis2_lib::get_course_participants($course_id);
		$sql = "SELECT
					count(b.id) as total, b.title
				FROM
					".$CFG->prefix."block_elis2_book AS b
				INNER JOIN
					".$CFG->prefix."block_elis2_booklist AS bl
				ON
					b.isbn = bl.isbn
				INNER JOIN
					".$CFG->prefix."user as u 
				ON
					u.id = bl.uid	
				WHERE
					1 ".$cond."
				AND
					u.id in (".implode(",",$participant_obj).")		
				GROUP BY 
					b.id 
				ORDER BY
					total desc 
				LIMIT 30";
		
		$records = $DB->get_recordset_sql($sql);
			 
	    foreach($records as $stats){
	    	$stats_obj[] = $stats;
	    }
	    return $stats_obj;
	}
	
	function get_activity_stats($course_id,$from,$until){
		global $DB,$CFG;
		$participant_obj = elis2_lib::get_course_participants($course_id);
		
		$cond = $from=='0'?" AND b.timeinput <= $until":" AND b.timeinput between $from and $until";
		$sql = "SELECT
					count(distinct activity) as total, concat(u.firstname,' ',u.lastname) as name
				FROM
					".$CFG->prefix."block_elis2_act_submission AS b
				INNER JOIN
					".$CFG->prefix."user AS u
				ON
					u.id = b.uid
				WHERE
					1 ".$cond."
				AND
					u.id in (".implode(",",$participant_obj).")		
				GROUP BY 
					u.id 
				ORDER BY
					total desc";
		$records = $DB->get_recordset_sql($sql);
			 
	    foreach($records as $stats){
	    	$stats_obj[] = $stats;
	    }
	    return $stats_obj;
	}
	
	
	function add_date($date,$day)//add days
    {
        $sum = strtotime(date("Ymd", strtotime("$date")) . " $day days");
        $dateTo=date('Ymd',$sum);
        return $dateTo;
    }
    
    function get_report_period_selection(){
		global $DB;
	    
	    $array=array(14=>'14 days',30=>'30 days',60=>'60 days',365=>'1 year',0=>'All');
    	$today=date("Ymd");
    	
    	$period_html = '<select name="period" id="period" onChange="block_elis2.elis2LoadReportData(\'\',document.getElementById(\'curr_type\').value)">';
	    foreach($array as $k=>$v){
	    	if($k==0)
	    		$dt=0;
	        else
	        	$dt = elis2_lib::add_date($today,0-$k);
	        $period_html .= '<option value="'.$dt.'||'.$today.'">'.$v.'</option>';
	    }

	    /*$ayear = date("Y");
	    $terms = $DB->get_recordset_sql("select term, min(dateno) as mn,max(dateno) as mx 
	                                from kgv_dates 
	                                where ayear=$ayear group by term");
    	foreach($terms as $t){
	        $period_html .= '<option value="'.$t->mn.'||'.$t->mx.'">'.$t->term.'</option>'; 
	    }*/
	    
	    $period_html .= '</select>';
	    
	    return $period_html;
	     
	}
	
	function format_date($date){
		//return %d/%m/%Y
		$day 	= substr($date,6,2);
		$month	= substr($date,4,2);
		$year	= substr($date,0,4);
		return $day.'/'.$month.'/'.$year;
	}
	
	function has_report_right($instance_id,$course_id){
		$context = get_context_instance(CONTEXT_BLOCK, $instance_id);
        $admin_context = get_context_instance(CONTEXT_COURSE, $course_id);

        return (has_capability('moodle/block:edit', $context, $_SESSION['USER']->id, false)||has_capability('moodle/site:config', $admin_context));
	}
	
	function is_staff($instance_id,$course_id){
		$context = get_context_instance(CONTEXT_BLOCK, $instance_id);
        $admin_context = get_context_instance(CONTEXT_COURSE, $course_id);

        return (has_capability('moodle/block:edit', $context, $_SESSION['USER']->id, false)||has_capability('moodle/site:config', $admin_context));
	}
	
	function render_chart($M,$F){
	      
	   require_once("graphlib.php");
	       
	   $names[0] = 'Male';
	   $names[1] = 'Female';
	    
	   $buckets1[0]= $M;
	   $buckets1[1]= $F;
	     
	   $graph = new graph(300,200);
	   //$graph->parameter['title'] = "Stanine profile"; //strip_tags(format_string($survey->name,true));
	   //$graph->parameter['title_size'] = 0;
	   $graph->parameter['bar_spacing'] = 0;
	   $graph->parameter['bar_size']    =0.8; 
	   $graph->parameter['y_grain_left']    =5; 
	   //$graph->parameter['stack']=1;
	          
	   $graph->x_data               = $names; 
	   
	   // set-up multicolour plot
	   foreach($buckets1 as $i=>$b){
	        $cs = str_split($stanines[$b]->colour,2);
	        $red = hexdec($cs[0]);
	        $green = hexdec($cs[1]);  
	        $blue = hexdec($cs[2]);  
	        $graph->colour['col'.str_replace(' ','_',$names[$i])] = ImageColorAllocate($graph->image, $red, $green, $blue);  
	   }
	   //dbg();
	   $graph->y_data['answers1']   = $buckets1;
	   $graph->y_format['answers1'] = array('colour' => "green", 'bar'=>'fill');
	   //$graph->y_data['answers2']   = $buckets2;
	   //$graph->y_format['answers2'] = array('colour' => "amber", 'bar'=>'fill');
	  // $graph->y_tick_labels = $options;
	   $graph->parameter['legend']        = 'none';
	   //$graph->parameter['legend_border'] = 'black';
	   //$graph->parameter['legend_offset'] = 4;
	  
	   $graph->y_order = array('answers1');
	
	   //$graph->parameter['y_max_left']= 100;
	   $graph->parameter['y_label_left']= 'Borrows';
	   $graph->parameter['y_axis_gridlines']= 6;
	   $graph->parameter['y_resolution_left']= 2;
	   //$graph->parameter['y_decimal_left']= 0;
	   $graph->parameter['x_axis_angle']  = 0;
	   $graph->parameter['x_axis_gridlines'] = 'auto';
	   $graph->draw();
	}
    
}
?>