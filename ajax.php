<?php 

require_once('../../config.php');
require_once("lib.php");

require_login($SITE);

$task = required_param('task', PARAM_TEXT);
$output = array();
switch ($task) {
	
    case 'add_book_2_list':
    	$isbn		= required_param('add_isbn', PARAM_TEXT);
    	$id			= required_param('id', PARAM_TEXT);
    	$tr_ct		= required_param('tr_ct', PARAM_INT);
    	$blockid 	= required_param('blockid', PARAM_INT);
    	
    	$max_rate		= optional_param('max_rate', '', PARAM_INT);
    	$new_list		= new stdClass();
	    $new_list->uid  = $USER->id;
	    $new_list->isbn	= $isbn;
	    
	    $new_list->timeinput = date("Y-m-d H:i:s");
	    if(elis2_lib::bookAlreadyAdded2List($new_list->isbn)==false)
	    	$DB->insert_record('block_elis2_booklist',$new_list);

		list($total_book,$book_obj) = elis2_lib::get_books_from_service(array('book_isbn'=>$new_list->isbn),1,1);
		    	
		foreach($book_obj as $book){
		    
		}
	    if(elis2_lib::bookAlreadyAdded2Master($isbn)==false){
		    elis2_lib::insertBookRecord($book);
		}else
			elis2_lib::verifyBookRecord($book);
	    $output['return_html'] = '<a href="javascript:block_elis2.removeBookFromList('.$isbn.',\''.$book['id'].'\',\''.$max_rate.'\')"><img src="images/button-cross.png" title="'.get_string('remove_book','block_elis2').'"></a>';
	    
		$new_list->publisheddate	=  (isset($book['publisheddate']))?$book['publisheddate']:'';
		$new_list->s_thumbnail		=  ($book['smallThumbnail'])?$book['smallThumbnail']:'';
		$new_list->title			=  ($book['title'])?$book['title']:'';
		$new_list->id				=  ($book['id'])?$book['id']:'';
		$new_list->authors			=  ($book['authors'])?implode("||",$book['authors']):'';
		$new_list->gbook_id			= $new_list->id;
		
	    $output['new_record_html'] 	= elis2_lib::gen_mybooklist_record($new_list,$tr_ct,$blockid); 
	    	
    break;
    
    case 'remove_book_from_list':
    	$isbn 		= required_param('delete_isbn', PARAM_TEXT);
    	$id			= required_param('id', PARAM_TEXT);
    	$max_rate	= optional_param('max_rate', '', PARAM_INT);
    	
    	if(elis2_lib::check_submitted_activity($isbn,$USER->id)==false)
    		$output['result'] 		= $DB->delete_records('block_elis2_booklist',array('isbn'=>$isbn,'uid'=>$USER->id));
    	$output['return_html'] 	= '<a href="javascript:block_elis2.addBook2List(\''.$isbn.'\',\''.$id.'\','.$max_rate.')"><img src="images/button-add.png" title="'.get_string("add_2_booklist","block_elis2").'"></a>';
    break;
    
    case 'rate_book':
    	$score		= required_param('score', PARAM_INT);
    	$isbn 		= required_param('isbn', PARAM_TEXT);
    	$small_img	= optional_param('small_img', '', PARAM_INT);
    	$max_rate	= required_param('max_rate', PARAM_INT);
    	    	
    	# retrieve the booklist record first
		$sql = "SELECT id FROM {block_elis2_booklist} WHERE uid = '".$USER->id."' AND isbn = '".$isbn."'";
		if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $book_obj){
	    		
			}
		}
		
    	$rate_obj->rating	= $score;
    	$rate_obj->isbn		= $isbn;
    	$rate_obj->uid		= $USER->id;
    	$rate_obj->id		= $book_obj->id; 
   	
    	$DB->update_record('block_elis2_booklist',$rate_obj);
    	
    	$book_obj = elis2_lib::get_book_info_with_my_rating($isbn);
    	$output['book_rating_div_html'] = elis2_lib::gen_book_rating_images($book_obj,$max_rate);
    	$output['return_html'] = elis2_lib::gen_my_rated_images($isbn,$score,$max_rate,$small_img);
    	
    break;
    
    case 'remove_rate':
    	$isbn 		= required_param('isbn', PARAM_TEXT);
    	$max_rate 	= required_param('max_rate', PARAM_INT);
    	$small_img 	= optional_param('small_img', '', PARAM_INT);
    	
		# retrieve the booklist record first
		$sql = "SELECT id FROM {block_elis2_booklist} WHERE uid = '".$USER->id."' AND isbn = '".$isbn."'";
		if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $book_obj){
	    		
			}
		}
    	
    	$rate_obj->rating = null;
    	$rate_obj->id = $book_obj->id;
    	
    	$DB->update_record('block_elis2_booklist',$rate_obj); 
    	
    	$book_obj = elis2_lib::get_book_info_with_my_rating($isbn);
    	$output['book_rating_div_html'] = elis2_lib::gen_book_rating_images($book_obj,$max_rate);
    	$output['return_html'] = '';
    	for($a=1;$a<=$max_rate;$a++)
			$output['return_html'].= elis2_lib::gen_rating_off_image($a,$isbn,$max_rate,$small_img);
    break;
    
    case 'update_read_status':
    	$blockid = required_param('blockid', PARAM_INT);
    	$read = required_param('read', PARAM_INT);
    	$isbn = required_param('isbn', PARAM_TEXT);
    	$ct   = required_param('ct', PARAM_INT);
    	
		# retrieve the booklist record first
		$sql = "SELECT id FROM {block_elis2_booklist} WHERE uid = '".$USER->id."' AND isbn = '".$isbn."'";
		if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $book_obj){
	    		
			}
		}
    	
		# update record
    	$read_obj->is_read = $read;
    	$read_obj->id = $book_obj->id;
    	$DB->update_record('block_elis2_booklist',$read_obj);
    	
    	if($read){
    		//$output['return_html'] = elis2_lib::gen_activity_icon_with_rating($isbn);
    		$output['return_html'] = elis2_lib::gen_rating($isbn,$blockid);
			$output['return_html'] .= '<p id="activity_'.$ct.'_p">';
			$output['return_html'] .= elis2_lib::gen_activity_icon($isbn,$ct,$blockid);
			$output['return_html'] .= '</p>';
    	}
    	else{
			$rate_obj->rating = null;
    		$rate_obj->id = $book_obj->id;
    		$DB->update_record('block_elis2_booklist',$rate_obj);
    		
    		$output['return_html'] = '';
    	}
    break;
    
    case 'update_buddy':
    	$buddy_no	= required_param('buddy_no', PARAM_INT);
    	$bid		= required_param('bid', PARAM_INT);
    	
		# retrieve buddylist id, if any
		$sql = "SELECT id FROM {block_elis2_reading_buddy} WHERE uid = '".$USER->id."'";
		
		if ($records = $DB->get_recordset_sql($sql)) {
	    	foreach($records as $buddy){
	    		
			}
		}
		
    	if($buddy->id==''||is_null($buddy->id)){
    		$buddy_obj->bid1 = $buddy_no==1?$bid:'';
    		$buddy_obj->bid2 = $buddy_no==2?$bid:'';
    		$buddy_obj->bid3 = $buddy_no==3?$bid:'';
    		$buddy_obj->uid = $USER->id;
    		$buddy_obj->timeinput = date("Y-m-d H:i:s");
    		$DB->insert_record('block_elis2_reading_buddy',$buddy_obj);

    	}else{
    		$buddy_obj->{'bid'.$buddy_no} = $bid;
    		$buddy_obj->id = $buddy->id; 
    		
    		$DB->update_record('block_elis2_reading_buddy',$buddy_obj);	
    	}
    	
    	
    	$output['return_html'] = $buddy_no.'/'.$bid;	
    break;
    
    case 'get_book_avg_rating':
    	
    	$isbn		= required_param('isbn', PARAM_TEXT);
    	$max_rate	= required_param('max_rate', PARAM_INT);
    	$book_obj 	= elis2_lib::get_book_info_with_my_rating($isbn);
    	
    	$output['book_rating_div_html'] = elis2_lib::gen_book_rating_images($book_obj,$max_rate);
    	
    	
    break;
    
    case 'render_act_form':
    	$act_name 	= required_param('act_name', PARAM_TEXT);
    	$isbn 		= required_param('isbn', PARAM_TEXT);
    	
    	$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$act_name.'/class_elis_plugin_'.$act_name.'.php';
			if(is_file($class_file)){
				$class_name = 'elis_plugin_'.$act_name;
				# if user has right to access this activity
				include_once($class_file);
				${$class_name}			= new $class_name();
				${$class_name}->isbn	= $isbn;
				list($output['return_html'],$output['return_js']) = ${$class_name}->render_form();
				
			}
    break;
    
    case 'render_explain':
    	$blockid = required_param('blockid', PARAM_INT);
    	$output['return_html'] = '<h2 align="center">'.get_string('activity_explained','block_elis2').'</h2>
					<table width="700" align="center"> 
					<tbody><tr style="border:1px solid blue"><td width="70">'.get_string('name','block_elis2').'</td><td width="40">'.get_string('icon','block_elis2').'</td>
					<td width="400">'.get_string('description','block_elis2').'</td>
					<td width="125">'.get_string('recommended_for','block_elis2').'</td></tr>';
		$act_arr = get_directory_list($CFG->dirroot.'/blocks/elis2/activity','','',true);
		$act_size = sizeof($act_arr); 
		
		$user_yr = $USER->yr==''?USER_YR:$USER->yr;
		# get config		
		//$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
		$instances = $DB->get_recordset('block_instances', array('id' => $blockid));

	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	        $instance_id = $instance->id;
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
		
		for($i=0;$i<$act_size;$i++){
			$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$act_arr[$i].'/class_elis_plugin_'.$act_arr[$i].'.php';
			if(is_file($class_file)){
				$class_name = 'elis_'.$act_arr[$i];
				
				# if user has right to access this activity
				$has_act_on = false;
				if($config->{'elis_'.$act_arr[$i].'_'.$user_yr})
					$has_act_on = true;
				elseif(elis2_lib::is_staff($instance_id,$COURSE->id)){
					for($ct=7;$ct<=13;$ct++) 
						if($config->{'elis_'.$act_arr[$i].'_'.$ct}){
							$has_act_on = true;
							break;
						}
						
				}	
				if($has_act_on){
					include_once($class_file);
					$class_name = 'elis_plugin_'.$act_arr[$i];
					# check if class exists
					if(class_exists($class_name)==false)
						continue;
					# instantiate a class
					${$class_name} = new $class_name();
					$output['return_html'] .='<tr style="border:1px solid blue"><td>'.${$class_name}->title.'</td><td>'.${$class_name}->render_icon().'</td>
								  <td>'.${$class_name}->explain_text.'</td>
								  <td>'.${$class_name}->explain_recommended.'</td></tr>'; 
				}
			}
		}
    	
		$output['return_html'] .= '</table>';
    	
    break;
    case 'submit_activity':
    	$blockid = required_param('blockid', PARAM_INT);
    	$str 		= required_param('str', PARAM_TEXT);
    	$json_arr	= json_decode($str);
    	$q_size 	= sizeof($json_arr->qid);
    	$ans_size	= sizeof($json_arr->ans);
    	
    	if($q_size>0 && $q_size == $ans_size){
    		for($a=0;$a<$q_size;$a++){
    			list($act,$q) = explode('_',$json_arr->qid[$a]);
    			$q_no = str_replace('q','',$q);
    			
    			if(isset($json_arr->submitted[$a]) && $json_arr->submitted[$a]!=''){
    				$update_submit_obj				= new stdClass();
		    		$update_submit_obj->activity  	= $act;
		    		$update_submit_obj->q_no		= $q_no;
		    		$update_submit_obj->answer		= $json_arr->ans[$a];
		    		$update_submit_obj->isbn		= $json_arr->isbn;
					$update_submit_obj->uid  		= $USER->id;	    		
					$update_submit_obj->timemodified= date("Y-m-d H:i:s");
    				$update_submit_obj->id 			= $json_arr->submitted[$a];
    				
					$DB->update_record('block_elis2_act_submission',$update_submit_obj);
				}
				else{
					$submit_obj				= new stdClass();
		    		$submit_obj->activity  	= $act;
		    		$submit_obj->q_no		= $q_no;
		    		$submit_obj->answer		= $json_arr->ans[$a];
		    		$submit_obj->isbn		= $json_arr->isbn;
					$submit_obj->uid  		= $USER->id;	    		
					$submit_obj->timeinput = date("Y-m-d H:i:s");
					$submit_obj->timemodified= date("Y-m-d H:i:s");
    				$DB->insert_record('block_elis2_act_submission',$submit_obj);
    				
					if($act=='tweetnewsflash'){
						
						$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$act.'/class_elis_plugin_'.$act.'.php';
						if(is_file($class_file)){
							include_once($class_file);
							
							$class_name = 'elis_plugin_'.$act;
							# check if class exists
							if(class_exists($class_name)==false)
								continue;
								
							# instantiate a class
							${$class_name} = new $class_name();
							
							if($json_arr->qid[$a] == ${$class_name}->tweet_field){
								${$class_name}->tweetit($json_arr->ans[$a],$blockid);
							}
						}
	    			}
    				
				}	
			}
    		$output['return_html'] = get_string('answer_submitted','block_elis2');
    		$output['result'] = 1;
    	}else{
    		$output['return_html'] = get_string('failed_to_submit','block_elis2');
    		$output['result'] = -1;
    	}
    		
    break;
    
    case 'delete_activity':
    	$str 		= required_param('str', PARAM_TEXT);
    	$json_arr	= json_decode($str);
    	$q_size 	= sizeof($json_arr->qid);
    	$result 	= array();
    	if($q_size>0){
    		for($a=0;$a<$q_size;$a++){
    			$result[] = $DB->delete_records('block_elis2_act_submission',array('id'=>$json_arr->qid[$a],'uid'=>$USER->id));
    		}
    	}
    	$output['return_html'] = in_array(false,$result)||sizeof($result)==0?get_string('failed_to_delete','block_elis2'):get_string('deleted','block_elis2');
    	$output['delete_result'] = in_array(false,$result)||sizeof($result)==0?-1:1;
    break;
    
    case 'get_act_icon':
    	$blockid = required_param('blockid', PARAM_INT);
    	$isbn	= required_param('isbn', PARAM_TEXT);
    	$ct		= required_param('ct', PARAM_INT);
    	
		$output['return_html'] = elis2_lib::gen_activity_icon($isbn,$ct,$blockid);
    break;
    
    case 'get_report_data':
    	$type			= required_param('type', PARAM_TEXT);
    	$period			= required_param('period', PARAM_TEXT);
    	$course_id		= required_param('course_id', PARAM_INT);
    	list($from,$to)	= explode("||",$period);
		$to.='235959';
    	switch($type){
    		case 'Stats':
    			$gender_stats_obj = elis2_lib::get_borrow_gender_stats($course_id,$from,$to);
    			
		    	$report_html 	= '<table width="100%">';
		    	
				if($from!='0' && $to!='0')
					$report_html .= '<tr><td colspan="3"><h3>Statistics from '.elis2_lib::format_date($from).' to '.elis2_lib::format_date($to).'</h3></td></tr>';
		    	$report_html .= '<tr><td colspan="3"><img src="ajax.php?task=render_graph&M='.$gender_stats_obj['M'].'&F='.$gender_stats_obj['F'].'" /></td></tr>
		    					   <tr><td>'.get_string('borrows','block_elis2').'</td><td>'.get_string('activities','block_elis2').'</td><td>'.get_string('popular','block_elis2').' ('.get_string('top_30','block_elis2').')</td></tr>
		    					   <tr><td width="33%" valign="top">'.elis2_lib::get_borrow_report_table($course_id,$from,$to).'</td>
		    					   <td width="33%" valign="top">'.elis2_lib::get_activity_report_table($course_id,$from,$to).'</td>
		    					   <td width="33%" valign="top">'.elis2_lib::get_popular_report_table($course_id,$from,$to).'</td></tr>
		    					   </table>';
		    break;
    		case 'Reading':
    			$report_html 	= '<table width="100%">';
    			if($from!='0' && $to!='0')
					$report_html 	.= '<tr><td><h3>Statistics from '.elis2_lib::format_date($from).' to '.elis2_lib::format_date($to).'</h3></td></tr>';
		    		$report_html 	.= '<tr><td valign="top">'.elis2_lib::get_reading_report_table($course_id,$from,$to).'</td>
		    					   </table>';
    	}
    	$output['return_html'] = $report_html;
    break;
    
    case 'render_graph':
    	$f = required_param('F', PARAM_INT);
    	$m = required_param('M', PARAM_INT);
    echo elis2_lib::render_chart($m,$f);
}
echo json_encode($output);

?>
