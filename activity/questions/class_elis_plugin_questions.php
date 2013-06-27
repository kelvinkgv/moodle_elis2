<?php
 
class elis_plugin_questions{ //extends block_base {

	function __construct() {
        global $PAGE;
        $this->title		= get_string('questions_activity', 'block_elis2');
        $this->act_prefix	= 'questions';
        $this->isbn			= '';
        $this->explain_text 		= 'What questions would you ask if you met a character from your book?';
        $this->explain_recommended	= 'Fiction';
        
    }
	function allowed($cohort){
		return in_array($cohort,array(7,8,9,10,11));
	}
	
	function render_icon(){
		global $CFG;
		return '<img src="'.$CFG->wwwroot.'/blocks/elis2/activity/'.$this->act_prefix.'/img/icon.jpg" title="'.$this->title.'">';
	}
	
	function render_form($show_result=false,$uid=''){
		$js = array();
 		$html = '<table>'; 
			
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('If you could interview one character from your book. Who would it be?:',$this->act_prefix.'_q1',$this->isbn,'','','',$uid,$show_result);
		$html.=$r_html;
		
		$html.=elis2_lib::activityQuestion('What three questions would you most like to ask them?');
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Question 1:',$this->act_prefix.'_q2',$this->isbn,true,1,50,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Question 2:',$this->act_prefix.'_q3',$this->isbn,true,1,50,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Question 3:',$this->act_prefix.'_q4',$this->isbn,true,1,50,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		if($show_result==false)
		$html .= elis2_lib::activityCheckboxRow('Spoiler Alert','Please tick if your activity review contains information that might spoil the book for others ',$this->act_prefix.'_q5',$this->isbn,$uid);
		$html .= '</table>';
		
		return array($html,$js);
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