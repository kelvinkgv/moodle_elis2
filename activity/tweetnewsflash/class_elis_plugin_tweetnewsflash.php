<?php
 
class elis_plugin_tweetnewsflash{ //extends block_base {

	function __construct() {
        global $PAGE;
        $this->title				= get_string('tweetnewsflash_activity', 'block_elis2');
        $this->act_prefix			= 'tweetnewsflash';
        $this->isbn					= '';
        $this->tweet_field  		= $this->act_prefix.'_q1';
        $this->explain_text 		= 'Recall an exciting moment or event from your book. Pretend it\'s happening now - tell the world about it using a tweet.
							   			<br>N.B. Your work may be tweeted from a real Twitter account - you can follow \'elisatkgv\' to receive tweets from your fellow ELIS users.';
        $this->explain_recommended	= 'Fiction';
        
    }
    
	function allowed($cohort){
		return in_array($cohort,array(7,8,9,10,11));
	}
	    
    function extra_config($block_id){
    	global $DB,$CFG;
    	# to be called by block config.php
    	
    	
    	require_once($CFG->dirroot . '/blocks/elis2/twitter/lib/EpiCurl.php');
	    require_once($CFG->dirroot . '/blocks/elis2/twitter/lib/EpiOAuth.php');
	    require_once($CFG->dirroot . '/blocks/elis2/twitter/lib/EpiTwitter.php');
	
        $instances = $DB->get_recordset('block_instances', array('id' => $block_id));
	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	    }		
	        	
    	$field[] = array('type'=>'text','name'=>'config_elis2twitterconsumerkey','caption'=>get_string('twitter_consumer_key', 'block_elis2'),'data_type'=>PARAM_TEXT);
    	$field[] = array('type'=>'text','name'=>'config_elis2twitterconsumersecret','caption'=>get_string('twitter_consumer_secret', 'block_elis2'),'data_type'=>PARAM_TEXT);
    	if(!isset($config->elis2twitteroauthtoken) || $config->elis2twitteroauthtoken==''){
    		if($config->elis2twitterconsumerkey!='' && $config->elis2twitterconsumersecret!=''){
		    	$twitterObj = new EpiTwitter($config->elis2twitterconsumerkey, $config->elis2twitterconsumersecret);
		    	$url = $twitterObj->getAuthorizationUrl();
			    $field[] = array('type'=>'html','name'=>'<a href="'.$url.'" target="_blank">Sign In with Twitter</a>');	
    		}else{
    			$field[] = array('type'=>'html','name'=>'Please type in the consumer key and secret');
    		}
    		
	    }
    	
    	return $field; 
    }
    
	function render_icon(){
		global $CFG;
		return '<img src="'.$CFG->wwwroot.'/blocks/elis2/activity/'.$this->act_prefix.'/img/icon.jpg" title="'.$this->title.'">';
	}
	
	function render_form($show_result=false,$uid=''){
		
	
		$js = array();
 		$html = '<table>'; 
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Recall an exciting moment or event from your book. Pretend it\'s happening now - tell the world about it using a tweet. DO NOT WRITE A BOOK REVIEW:',$this->act_prefix.'_q1',$this->isbn,true,0,115,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		if($show_result==false)
		$html .= elis2_lib::activityCheckboxRow('Spoiler Alert','Please tick if your activity review contains information that might spoil the book for others ',$this->act_prefix.'_q2',$this->isbn,$uid);
		$html .= '</table>';
		
		return array($html,$js);
	
	
	}
	
	function tweetit($msg,$block_id){
	    global $CFG,$DB;
	    
	    require_once($CFG->dirroot . '/blocks/elis2/twitter/lib/EpiCurl.php');
	    require_once($CFG->dirroot . '/blocks/elis2/twitter/lib/EpiOAuth.php');
	    require_once($CFG->dirroot . '/blocks/elis2/twitter/lib/EpiTwitter.php');
	
        //$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
        $instances = $DB->get_recordset('block_instances', array('id' => $block_id));
	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	    }		
	    
        $twitterObj = new EpiTwitter($config->elis2twitterconsumerkey, $config->elis2twitterconsumersecret);
	    
	    if(!empty($config->elis2twitteroauthsecret)){
	        $twitterObj->setToken($config->elis2twitteroauthtoken, $config->elis2twitteroauthsecret);          
	        $twitterInfo= $twitterObj->get_accountVerify_credentials();
	        
	        $twitterInfo->response;
	                    
	        $username = $twitterInfo->screen_name;
	        $profilepic = $twitterInfo->profile_image_url;
	
	        $update_status = $twitterObj->post_statusesUpdate(array('status' => stripslashes($msg)));
	        
	        $temp = $update_status->response; 
	        
	        return true;
	    }else{
	        return false;
	    }
	}
	
	function render_result($uid,$name){

		$html='<table>';
		$html.='<tr><td>'.$this->render_icon().' '.$this->title.'</td></tr>';
		$html.='<tr><td>'.$name.'</td></tr>';
		list($form_html) = $this->render_form(1,$uid);
		$html.='<tr><td>'.$form_html.'</td></tr>';
		$html.='</table>';
		return $html;
	}		
}

?>