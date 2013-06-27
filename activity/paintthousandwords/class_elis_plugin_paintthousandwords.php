<?php
 
class elis_plugin_paintthousandwords{ //extends block_base {

	function __construct() {
        global $PAGE;
        $this->title		= get_string('paintthousandwords_activity', 'block_elis2');
        $this->act_prefix	= 'paintthousandwords';
        $this->isbn			= '';
        $this->explain_text 		= 'Provide a link to an image of a piece of visual art (painting, sculpture, etc..) that you feel fits in with the mood and themes of your book.';
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
		
 		$html = '<table>'; 
			
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Provide a link to an image of a piece of visual art (painting, sculpture, etc..) that you feel fits in with the mood and themes of your book.:',$this->act_prefix.'_q1',$this->isbn,false);
		$html.=$r_html;
		
		list($r_js,$r_html) = elis2_lib::activityTextboxRow('Explain why you chose it in 50-70 words.:',$this->act_prefix.'_q2',$this->isbn,true,50,70,$uid,$show_result);
		$html.=$r_html;
		$js = array_merge($js,$r_js);
		
		$html .= elis2_lib::activityCheckboxRow('Spoiler Alert','Please tick if your activity review contains information that might spoil the book for others ',$this->act_prefix.'_q3',$this->isbn,true);
		$html .= '</table>';
		
		return array($html,$js);
	}
}

?>