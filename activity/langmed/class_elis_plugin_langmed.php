<?php
 
class elis_plugin_langmed{ //extends block_base {

	function __construct() {
        global $PAGE;
        $this->title	= get_string('langmed_activity', 'block_elis2');
        $this->act_prefix	= 'langmed';
        $this->isbn		= '';
        $this->explain_text 		= 'Complete the phrase in your study language: I chose this book because ...';
        $this->explain_recommended	= 'Fiction / Non-fiction';
        
    }
	function allowed($cohort){
		return in_array($cohort,array(7,8,9,10,11));
	}
	
	function render_icon(){
		global $CFG;
		return '<img src="'.$CFG->wwwroot.'/blocks/elis2/activity/langmed/img/icon.jpg" title="'.$this->title.'">';
	}
	
	function render_form($show_result=false,$uid=''){
		$js = array();
 		$min = 70;
 		$max = 100;
 		
		$html = '<table>';
		$option_arr[0] = array('Mandarin','用中文完成以下句子：我选这本书因为...');
		$option_arr[1] = array('French','Complétez la phrase suivante en français: J\'ai choisi ce livre parce que...');
		$option_arr[2] = array('Spanish','Completa las siguientes frases en español: Eligí este libro porque ...');
		$option_arr[3] = array('German','Ergänze den folgenden Satz auf deutsch: Ich habe dieses Buch gewählt, weil..');
		$js_function = 'onChange="document.getElementById(\''.$this->act_prefix.'_q1_question\').innerHTML = this.value;';
		$js_function.= 'if(this.value==\'Mandarin\'){elis2_count_chinese = true;}';
		$js_function.= 'else{elis2_count_chinese = false;}';
		$js_function.= '"';
		
		
		list($r_js,$r_html,$ans_obj) = elis2_lib::activitySelectionBox('Mandarin',$option_arr,$this->act_prefix.'_q1',$this->isbn,$js_function,true,$uid,$show_result);
		$html.=$r_html;
		if($ans_obj->answer=='')
			$js = array_merge($js,array('elis2SetChiCount()'));
		else{
			if($ans_obj->answer=='Mandarin')
				$js = array_merge($js,array('elis2SetChiCount()'));
		}
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('',$this->act_prefix.'_q2',$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;		
		$js = array_merge($js,$r_js);
		
		if($show_result==false)
		$html .= elis2_lib::activityCheckboxRow('Spoiler Alert','Please tick if your activity review contains information that might spoil the book for others ',$this->act_prefix.'_q3',$this->isbn,$uid);
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