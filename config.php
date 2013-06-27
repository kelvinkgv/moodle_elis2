<?php 
include_once($CFG->dirroot.'/blocks/elis2/lib.php');

$yrs_arr = elis2_lib::get_esf_years();
$act_arr = get_directory_list($CFG->dirroot.'/blocks/elis2/activity','','',true);

$yr_size = sizeof($yrs_arr);
$act_size = sizeof($act_arr);

$mform->addElement('header', 'configactivity', get_string('config_header_msg','block_elis2'));
$mform->addElement('html','<b>'.get_string('drag_instruction','block_elis2').'</b>');
$mform->addElement('html','<table id="act_table"><tr><td></td>');
$PAGE->requires->js('/blocks/elis2/elis2.js');
$PAGE->requires->js_init_call('block_elis2.elis2InitConfigDND');

for($k=0;$k<$yr_size;$k++){
	$mform->addElement('html','<td>'.$yrs_arr[$k]['yr'].'</td>');
}
$mform->addElement('html','</tr>');

# get act order for display
$order_str = '_order';
foreach($this->block->config as $key=>$value){
	if(substr($key,-strlen($order_str))==$order_str){
		list($dum,$act_name,$dum2) = explode('_',$key);
		$act_order[$value-1] = $act_name;
	}
}
if(is_array($act_order) && sizeof($act_order)>0)
	$act_arr = $act_order;

# loop the activity. By default (alphabetically) or according to the config order
for($i=0;$i<$act_size;$i++){
	$class_file = $CFG->dirroot.'/blocks/elis2/activity/'.$act_arr[$i].'/class_elis_plugin_'.$act_arr[$i].'.php';
	if(is_file($class_file)){
		include_once($class_file);
		$class_name = 'elis_plugin_'.$act_arr[$i];
		
		# check if class exists
		if(class_exists($class_name)==false)
			continue;
		
		# instantiate a class
		${$class_name} = new $class_name();
	
		$mform->addElement('html','<tr id="drag_'.$act_arr[$i].'" class="drag"><td style="cursor:move">'.${$class_name}->title.'</td>');
		for($k=0;$k<$yr_size;$k++){
			$mform->addElement('html','<td>');
			
			if(${$class_name}->allowed($yrs_arr[$k]['year']))
				$mform->addElement('advcheckbox', 'config_elis_'.$act_arr[$i].'_'.$yrs_arr[$k]['year'],'','',array('value'=>1));
			else
				$mform->addElement('html', '-');
			$mform->addElement('html','</td>');	
		}
		$mform->addElement('hidden', 'config_elis_'.$act_arr[$i].'_order','',array('id'=>$act_arr[$i].'_order'));
		$mform->setDefault('config_elis_'.$act_arr[$i].'_order', $i+1);
		$mform->addElement('html','</tr>');
		
		# check for extra config variable in activity and store them in an array
		if(method_exists(${$class_name},'extra_config')){
			$config_field_arr[] = ${$class_name}->extra_config($this->block->instance->id);
		}
	}
}
$mform->addElement('html','</table>');

# display extra config variable in activity if any
$config_field_arr_size = sizeof($config_field_arr);
if($config_field_arr_size>0){
	$mform->addElement('header', 'configactivity', get_string('other_act_config_msg','block_elis2'));
	for($a=0;$a<$config_field_arr_size;$a++){
		$config_field_obj = $config_field_arr[$a];
		$config_field_size = sizeof($config_field_obj); 
		for($x=0;$x<$config_field_size;$x++){
			$type = isset($config_field_obj[$x]['type'])?$config_field_obj[$x]['type']:'';
			$name = isset($config_field_obj[$x]['name'])?$config_field_obj[$x]['name']:'';
			$caption = isset($config_field_obj[$x]['caption'])?$config_field_obj[$x]['caption']:'';
			$mform->addElement($type,$name,$caption);
			
			if(isset($config_field_obj[$x]['data_type']) && $config_field_obj[$x]['data_type']!='')
			$mform->setType('config_elis2twitterconsumerkey', $config_field_obj[$x]['data_type']);
		}
	}
}
?>