<?php
 class elis_plugin_hero{// extends block_base {
	
 	function __construct() {
        global $PAGE,$CFG;
        
        require_once($CFG->dirroot.'/blocks/elis2/lib.php');
        $this->title		= get_string('hero_activity', 'block_elis2');
        $this->act_prefix	= 'hero';
        $this->isbn			= '';
        $this->explain_text 		= 'Which character would you most like to be, and why?';
        $this->explain_recommended	= 'Fiction';
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
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Which character would you most like to be?',$this->act_prefix.'_q1',$this->isbn,'','','',$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		$eid = $this->act_prefix.'_q2';
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Reason 1',$eid,$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
				
		$eid = $this->act_prefix.'_q3';
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Reason 2',$eid,$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
				
		$eid = $this->act_prefix.'_q4';
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Reason 3',$eid,$this->isbn,true,$min,$max,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
				
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