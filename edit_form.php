<?php

class block_ratings_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
    	//$config = get_config('block_ratings');

        // Section header title according to language file.
        $mform->addElement('header', 'editheader', get_string('editsettings', 'block_ratings'));

        // Allow the user to rerate.
        $mform->addElement('selectyesno', 'config_allow_rerate', get_string('label_rerate', 'block_ratings'),get_string('desc_rerate', 'block_ratings'));
        $mform->setDefault('config_allow_rerate', get_config('allow_rerate', 'block_ratings'));       
     
         // Show the block (or not)
        $mform->addElement('selectyesno', 'config_show_old_ratings', get_string('label_show_old_ratings', 'block_ratings'),get_string('desc_show_old_ratings', 'block_ratings'));
        $mform->setDefault('config_show_old_ratings', get_config('show_old_ratings', 'block_ratings'));
          
        // Ratings Area
        $options = array('difficulty'=>get_string('difficulty','block_ratings'), 'fun'=>get_string('fun','block_ratings'));
        $mform->addElement('select', 'config_ratearea', get_string('label_ratearea', 'block_ratings'),$options);
        $mform->setType('config_ratearea', PARAM_TEXT);
        $mform->setDefault('config_ratearea', get_config('ratearea', 'block_ratings'));


    }
}
