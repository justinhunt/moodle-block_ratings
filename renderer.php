<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Block ratings renderer.
 * @package   block_ratings
 * @copyright 2014 Justin Hunt (poodllsupport@gmail.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ratings_renderer extends plugin_renderer_base {

	public function fetch_rating_history_item($panelid,$assiginfo, $assigid, $assigname, $ratearea, $rating, $rerate=true,$parentmode=false, $bigmode=false) {
		global $CFG;
		$itemheading = html_writer::tag('span',$assigname,  array('class'=>'block_ratings-itembutton-titletext'));
		$itemtext = html_writer::tag('span',get_string($ratearea . '_' . $rating, 'block_ratings'),  array('class'=>'block_ratings-item-ratingtext'));
		if($rerate && !$parentmode){
			$itembuttonurl = "M.block_ratings.helper.showpanel('$panelid','$assiginfo')";
		}else{
			$itembuttonurl = "";
		}

		$itembigbutton_html = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'yui3-button block_ratings-item-button','id'=>'block_ratings-item-' . $ratearea .'-' . $assigid,
		  		'onclick'=>$itembuttonurl,
		  		'src'=>$CFG->wwwroot . '/blocks/ratings/pix/' . $ratearea . '0' . $rating . '.png'));
		  		
		$itemsmallbutton_html = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>' block_ratings-item-button','id'=>'block_ratings-item-' . $ratearea .'-' . $assigid,
		  		'onclick'=>$itembuttonurl,
		  		'src'=>$CFG->wwwroot . '/blocks/ratings/pix/' . $ratearea . '0' . $rating . '.png'));
		  		
		//$itembuttonlabel = get_string($ratearea . '_' . $rating, 'block_ratings');
		//$itembuttonlabel_html = html_writer::link('#', $itembuttonlabel,array('class'=>'block_rating-item-buttonlabel','onclick'=>$itembuttonurl));
		if($bigmode){
			return html_writer::tag('div', $itembigbutton_html  . '<br />' . $itemheading  . '<hr />',array('class'=>'block_ratings-bigitembutton-container'));
		}else{
			return html_writer::tag('div', $itemsmallbutton_html  .  $itemheading  . '<hr />',array('class'=>'block_ratings-itembutton-container'));
		}
	}
	
	public function fetch_no_items_message() {
		$message = get_string('norecentitems', 'block_ratings');
		return html_writer::tag('span',$message,  array('class'=>'block_ratings-noitems'));
	}


	public function fetch_rating_form($panelid,$ratearea) {
		global $CFG;
		$buttons = array();
		for ($rating=1;$rating<6;$rating++){
			
			$buttonurl = "M.block_ratings.helper.sendmessage('$panelid','$rating')";

			$buttonlabel = get_string($ratearea . '_' . $rating, 'block_ratings');
			$buttonlabel_html = html_writer::link('#', $buttonlabel,array('class'=>'block_ratings-buttonlabel','onclick'=>$buttonurl));

			$button_html = html_writer::empty_tag('input', array('type'=>'image',
		  		'class'=>'yui3-button block_ratings-button','id'=>'block_ratings_' . $rating,
		  		'onclick'=>$buttonurl,
		  		'src'=>$CFG->wwwroot . '/blocks/ratings/pix/' . $ratearea . '0' . $rating . '.png'));
		  	$button_container = html_writer::tag('div', $button_html . $buttonlabel_html,array('class'=>'block_ratings-button-container'));
		  	$buttons[]  = $button_container;
		}
		$buttons_html = implode('',$buttons);
		$idontknow_html = html_writer::link('#', get_string('idontknow', 'block_ratings'),array('class'=>'block_ratings-idontknow','onclick'=>"M.block_ratings.helper.hidepanel('$panelid')"));
		$buttons_container = html_writer::tag('div', $buttons_html,array('class'=>'block_ratings-buttons-container'));
		$form_container = html_writer::tag('div', $buttons_container . $idontknow_html,array('class'=>'block_ratings-form-container', 'style'=>'display: none'));
		$rating_form = $form_container; 
		return  $rating_form;
    }


}
