<?php
 
class elis_plugin_langchallenging{ //extends block_base {

	function __construct() {
        global $PAGE;
        $this->title		= get_string('langchallenging_activity', 'block_elis2');
        $this->act_prefix	= 'langchallenging';
        $this->isbn			= '';
        $this->explain_text 		= 'Write a short review of your book in your study language. ';
        $this->explain_recommended	= 'Fiction / Non-fiction';
        
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
 		$min = 30;
 		$max = 50;
 		
		$html = '<table>';
		$option_arr[0] = array('Mandarin','用中文写一个读书报告，请写30-50字。');
		$option_arr[1] = array('French','Ecrivez une critique de votre livre en français - vous devriez écrire 30-50 mots.');
		$option_arr[2] = array('Spanish','Escribe una reseña de tu libro en español - debe escribir entre 30 y 50 palabras.');
		$option_arr[3] = array('German','Schreib eine Rezension über dein Buch auf deutsch - schreib 30 bis 50 Wörter.');
		$js_function = 'onChange="document.getElementById(\''.$this->act_prefix.'_q1_question\').innerHTML = this.value;';
		$js_function.= 'if(this.value==\'Mandarin\'){elis2_count_chinese = true;}';
		$js_function.= 'else{elis2_count_chinese = false;}';
		$js_function.= '"';
		
		list($r_js,$r_html,$ans_obj) = elis2_lib::activitySelectionBox('Mandarin',$option_arr,$this->act_prefix.'_q1',$this->isbn,$js_function,true,$uid,$show_result);
		$html.=$r_html;
		if($ans_obj->answer=='')
			$js = array_merge($js,array('block_elis2.elis2SetChiCount()'));
		else{
			if($ans_obj->answer=='Mandarin')
				$js = array_merge($js,array('block_elis2.elis2SetChiCount()'));
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
		$html.='<tr><td width="25%">'.$this->render_icon().' '.$this->title.'</td></tr>';
		$html.='<tr><td>'.$name.'</td></tr>';
		list($form_html) = $this->render_form(1,$uid);
		$html.='<tr><td>'.$form_html.'</td></tr>';
		$html.='</table>';
		return $html;
	}	
}

?>