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
 * ratings block caps.
 *
 * @package    block_ratings
 * @copyright  Justin Hunt <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('ratingsheader',
                                         get_string('label_ratingsconfig', 'block_ratings'),
                                         get_string('desc_ratingsconfig', 'block_ratings')));
                                         
$yesnooptions = array(0 => 'No',1 => 'Yes');

$settings->add(new admin_setting_configselect('block_ratings/show_old_ratings', 
		get_string('label_show_old_ratings', 'block_ratings'),  
		get_string('desc_show_old_ratings', 'block_ratings'), 
		1, $yesnooptions));
		
$settings->add(new admin_setting_configselect('block_ratings/allow_rerate', 
		get_string('label_rerate_block', 'block_ratings'),  
		get_string('desc_rerate_block', 'block_ratings'), 
		1, $yesnooptions));
                                        
/*
$settings->add(new admin_setting_configcheckbox('ratings/allow_rerate',
                                                get_string('label_rerate', 'block_ratings'),
                                                get_string('desc_rerate', 'block_ratings'),
                                                1));

$settings->add(new admin_setting_configcheckbox('ratings/show_old_ratings',
                                                get_string('label_show_old_ratings', 'block_ratings'),
                                                get_string('desc_show_old_ratings', 'block_ratings'),
                                                1));
*/
                                                
 $options = array('difficulty'=>get_string('difficulty','block_ratings'), 'fun'=>get_string('fun','block_ratings'));
 $settings->add(new admin_setting_configselect('block_ratings/ratearea', get_string('label_ratearea', 'block_ratings'),
                            get_string('desc_ratearea', 'block_ratings'),
                            'difficulty',$options));                                             