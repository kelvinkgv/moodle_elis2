<?php
 class elis_plugin_climax{// extends block_base {
	
 	function __construct() {
 		global $PAGE,$CFG;
 		
 		require_once($CFG->dirroot.'/blocks/elis2/lib.php');
        $this->title		= get_string('climax_activity', 'block_elis2');
        $this->act_prefix	= 'climax';
        $this->isbn			= '';
        $this->explain_text 		= 'What was the most exciting incident in the book?';
        $this->explain_recommended	= 'Fiction / Non-fiction';
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
		list($r_js,$r_html) =  elis2_lib::activityTextboxRow('What was the most exciting incident in the book (explain in 50-70 words)',$this->act_prefix.'_q1',$this->isbn,true,$min,$max,$uid,$show_result);
		$js = array_merge($js,$r_js);
		$html.=$r_html;
		
		if($show_result==false)
		$html .= elis2_lib::activityCheckboxRow('Spoiler Alert','Please tick if your activity review contains information that might spoil the book for others',$this->act_prefix.'_q2',$this->isbn,$uid);
		$html .= '</table>';
		
		return array($html,$js);
	}
	
 	function render_result($uid,$name){

		$html.='<table>';
		$html.='<tr><td>'.$this->render_icon().' '.$this->title.'</td></tr>';
		$html.='<tr><td>'.$name.'</td></tr>';
		list($form_html) = $this->render_form(1,$uid);
		$html.='<tr><td>'.$form_html.'</td></tr>';
		$html.='</table>';
		return $html;
	}
}

?>