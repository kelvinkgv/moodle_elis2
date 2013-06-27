<?php
 class elis_plugin_keyfacts{// extends block_base {
	
 	function __construct() {
        global $PAGE;
        $this->title		= get_string('keyfacts_activity', 'block_elis2');
        $this->act_prefix	= 'keyfacts';
        $this->isbn			= '';
        $this->explain_text 		= 'What were the key pieces of information in your book?';
        $this->explain_recommended	= 'Non-fiction';
    }
    
 	function allowed($cohort){
		return in_array($cohort,array(10,11,12));
	}
	
	function render_icon(){
		global $CFG;
		return '<img src="'.$CFG->wwwroot.'/blocks/elis2/activity/'.$this->act_prefix.'/img/icon.jpg" title="'.$this->title.'">';
	}
	
	function render_form($show_result=false,$uid=''){
		$js = array();
 		$min = 5;
 		$max = 25;
 		
		$html = '<table>';
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('What were the key pieces of information contained in this book? (give 5)',$this->act_prefix.'_q1',$this->isbn,'','','',$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Number 1',$this->act_prefix.'_q2',$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
				
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Number 2',$this->act_prefix.'_q3',$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Number 3',$this->act_prefix.'_q4',$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Number 4',$this->act_prefix.'_q5',$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Number 5',$this->act_prefix.'_q6',$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
				
		if($show_result==false)
		$html .= elis2_lib::activityCheckboxRow('Spoiler Alert','Please tick if your activity review contains information that might spoil the book for others ',$this->act_prefix.'_q7',$this->isbn,$uid);
		$html .= '</table>';
		
		return array($html,$js);
	}
	
 	function render_result($uid,$name){

		$html.='<table>';
		$html.='<tr><td width="25%">'.$this->render_icon().' '.$this->title.'</td></tr>';
		$html.='<tr><td>'.$name.'</td></tr>';
		list($form_html) = $this->render_form(1,$uid);
		$html.='<tr><td>'.$form_html.'</td></tr>';
		$html.='</table>';
		return $html;
	}
}

?>