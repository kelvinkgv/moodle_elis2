<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Manage files in folder in private area.
 *
 * @package   moodle
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
            
require('../../config.php');
require_once("book_form.php");
require_once("lib.php");
require_once("$CFG->dirroot/repository/lib.php");
            
$blockid = optional_param('id', 0, PARAM_INT);
require_login();
if (isguestuser()) {
    die(); 
}

$returnurl		= optional_param('returnurl', '', PARAM_URL);
$isbn			= required_param('isbn', PARAM_TEXT);
$gbook_id		= required_param('gbook_id', PARAM_TEXT);

if (empty($returnurl)) {
    $returnurl = new moodle_url('add.php');
}

$context = get_context_instance(CONTEXT_BLOCK, $blockid);


$title = get_string('book_review','block_elis2');
$PAGE->set_url('/blocks/elis2/add.php');
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('mydashboard');

$data = new stdClass();
$data->block_id = $blockid;
$data->returnurl = $returnurl;
$data->contextid = $context->id;
$data->isbn		= $isbn;
$data->gbook_id		= $gbook_id;

list($total_book,$book_obj) = elis2_lib::get_books_from_service(array('id'=>$gbook_id),1,1);
if($book_obj[0]['isbn']!=$data->isbn){
	list($total_book,$book_obj) = elis2_lib::get_books_from_service(array('book_isbn'=>$isbn),1,1);
	if($book_obj[0]['isbn']!=$data->isbn)
		die;
}
		    	
foreach($book_obj as $book){
		    
}
if(elis2_lib::bookAlreadyAdded2Master($isbn)==false)
	elis2_lib::insertBookRecord($book);
else
	elis2_lib::verifyBookRecord($book);	

$options = array('subdirs'=>false, 'maxbytes'=>$CFG->userquota, 'maxfiles'=>-1, 'accepted_types'=> array('*.gif', '*.jpg'), 'return_types'=>FILE_INTERNAL);

$fs = get_file_storage();

// grab the block config data
if ($configdata = $DB->get_field('block_instances', 'configdata', array('id' => $blockid))) {
            $config = unserialize(base64_decode($configdata));
}

$mform = new elis2_book_form(null, array('data'=>$data, 'options'=>$options, 'config'=>$config));

if ($mform->is_cancelled()) {
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');


$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

?>
