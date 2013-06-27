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
 * Form for editing elis2 block instances.
 *
 * @package   block_elis2
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.banners GNU GPL v3 or later
 */

/**
 * Form for editing elis2 block instances.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.banners GNU GPL v3 or later
 */
class block_elis2_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Fields for editing elis2 block title and contents.
        global $CFG,$PAGE;
        
        $mform->addElement('header', 'configrating', get_string('elis_configuration','block_elis2'));
        $mform->addElement('text', 'config_maxrate', get_string('maxrate', 'block_elis2'));
        $mform->setType('config_maxrate', PARAM_INT); 
        $mform->setDefault('config_maxrate', 5);
        
        /*$mform->addElement('text', 'config_elis2twitterconsumerkey', get_string('twitter_consumer_key', 'block_elis2'));
        $mform->setType('config_elis2twitterconsumerkey', PARAM_TEXT); 
        
        $mform->addElement('text', 'config_elis2twitterconsumersecret', get_string('twitter_consumer_secret', 'block_elis2'));
        $mform->setType('config_elis2twitterconsumersecret', PARAM_TEXT);
        
        $mform->addElement('text', 'config_elis2twitteroauthtoken', get_string('twitter_oauth_token', 'block_elis2'));
        $mform->setType('config_elis2twitteroauthtoken', PARAM_TEXT); 
        
        $mform->addElement('text', 'config_elis2twitteroauthsecret', get_string('twitter_oauth_secret', 'block_elis2'));
        $mform->setType('config_elis2twitteroauthsecret', PARAM_TEXT);*/
        
        
                
        include_once($CFG->dirroot.'/blocks/elis2/config.php');
    }
    function set_data($defaults) {
        parent::set_data($defaults);
    }

}
