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
 * @copyright  Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//get our ratings mod
require_once($CFG->dirroot . '/blocks/ratings/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/family/lib.php');

class block_ratings extends block_list {

    function init() {
        $this->title = get_string('blocktitle', 'block_ratings');
    }

    function get_content() {
        global $CFG,  $DB, $USER;
      
        $course = $this->page->course;
        $renderer = $this->page->get_renderer('block_ratings');

	if (isset($this->config)){
		$config = $this->config;
	} else{	
		$config = get_config('block_ratings');
	}
	//there is a discrepancy between admin and instance settings
	//se we make sure they look the same here.
	if(!is_array($config->rateable)){
		$config->rateable = explode(',',$config->rateable);
	}
	
	//late completion was added later. Lets add it here to be safe
	if(!property_exists($config,'latecompletion')){
		$config->latecompletion = get_config('block_ratings')->latecompletion;
	}
	
	
		//Get the ratings user.
		$childid =  optional_param('childid',0, PARAM_INT); //the userid of the user whose homework we are showing		
		$ratingsuser = false;
		if($childid && local_family_is_users_child($childid)){
			$ratingsuser = $DB->get_record('user',array('id'=>$childid));
		}else{
			$ratingsuser = $USER;
		}
		if(!$ratingsuser){return;}
		
		//if we are logged in as a user who is NOT the child we set parentmode. to deactivate links
		$parentmode=!($ratingsuser->id==$USER->id);
		//always false at the moment. Later might use to retain JS but not show block
		$hidemode = false;
		
		//try to get the ratings course the user is enrolled in for My Moodle page
        //If a user is on more than one course, there will need to be some change to this
        //global $COURSE should work though, if in the course itself
        if(!$course || $course->id<2){
        	$ratingscourses = block_ratings_fetch_user_courses($ratingsuser->id,10);
        	if(count($ratingscourses) > 0){
        		$ratingscourse = array_pop($ratingscourses);
        	}else{
        		$ratingscourse = false;
				return;
        	}
        }else{
        	$ratingscourse = $course;
        }
		

	//set a title for this ratearea
	//in first phase we won't need this
	//$this->title = get_string('blocktitle4area', 'block_ratings', get_string($this->config->ratearea, 'block_ratings'));
	
	//our rate area
	$ratearea = $config->ratearea;
	
	//Fetch all the mods in the course
	$mods = get_array_of_activities($ratingscourse->id);
	$jsargses = array();
	foreach($mods as $mod){
		$jsargses[$mod->cm] = block_ratings_fetch_assignment_info_json($ratingscourse->id,$mod->cm ,$ratearea, $mod);
	}
	
	//set up our panelid and class
	$panelclass = 'block_ratings_popup';
	$panelid = $panelclass . '_' . $ratearea;
	
	//setup our output data
	if ($this->content !== null) {
		return $this->content;
	}

	if (empty($this->instance)) {
		$this->content = '';
		return $this->content;
	}

	$this->content = new stdClass();
	$this->content->items = array();
	$this->content->icons = array();
	$this->content->footer = '';
	
	
	//update our completionlog
	block_ratings_update_completion_log($ratingscourse,$ratingsuser, $config->rateable);
	
	//Is there a recently completed mod, we should show a rating form for? just handle the first one
	//if we are in parent mode, don't show any popup
	$recentlyfinished = false;
	if(!$parentmode){
		$records = $DB->get_records('block_ratings_log',array('userid'=>$ratingsuser->id, 'new'=>1, 'courseid'=>$ratingscourse->id));
		if($records){	
			$rec = array_shift($records);
			$DB->set_field('block_ratings_log', 'new', 0, array('id'=>$rec->id));
			$recentlyfinished = $rec->activityid;
		}
	}
	
	$unrated=0;
	if($recentlyfinished){
		$current_assig_json = $jsargses[$recentlyfinished];
		$this->content->items[]  = $renderer->fetch_rating_history_item($panelid,$current_assig_json,$recentlyfinished,$mods[$recentlyfinished]->name,$ratearea,$unrated,null,null,$config->bigicons);
	}else{
		$current_assig_json=null;
	}
	
	//set the js to the page
	$options = array();
	$options['headercontent'] = get_string('popup_headercontent', 'block_ratings');
	$options['panelclass'] = $panelclass;
	$options['panelid'] = $panelid;
	$options['width'] = 740;
	$options['height'] = 240;
	$options['parentmode'] = $parentmode;
	$options['courseid'] = $ratingscourse->id;
	$options['ratearea'] = $ratearea;
	$options['latecompletion'] = $config->latecompletion;
	$options['rateable'] = $config->rateable;
	$options['currentassig'] = $current_assig_json;
				
	
	//We need this so that we can require json,panel and transition yui libs
	$jsmodule = array(
		'name'     => 'block_ratings',
		'fullpath' => '/blocks/ratings/module.js',
		'requires' => array('panel', 'transition', 'json')
	);
		
	//setup our JS call
	$this->page->requires->js_init_call('M.block_ratings.helper.makepanel', array($options),false,$jsmodule);

	//create our ratings dialog
	$newcontent = $renderer->fetch_rating_form($panelid, $ratearea);
	$dialog = html_writer::div($newcontent, 'block_ratings_popup', array('id'=>$panelid));


	// user/index.php expect course context, so get one if page has module context.
	$currentcontext = $this->page->context->get_course_context(false);

	//fetch any existing ratings to show in block
	if(!property_exists($config, 'maxitems')){
		$config->maxitems = 5;
	}
	$recs = $DB->get_records('block_ratings',
		array('userid'=>$ratingsuser->id, 'courseid'=>$ratingscourse->id, 'ratearea'=>$config->ratearea),
		'time DESC','*');
	$activityids=array();
	if($recs){
		foreach($recs as $rec){
			$activityids[]=$rec->activityid;
			if(!$config->show_old_ratings){
				continue;
			}
			
			if(!array_key_exists( $rec->activityid, $mods)){
				continue;
			}
			
			//continue if we already have a rating item for this (recently completed output above)
			if($rec->activityid == $recentlyfinished){
				continue;
			}
			
			$assiginfo = block_ratings_fetch_assignment_info_json($ratingscourse->id, $rec->activityid, $ratearea ,$mods[$rec->activityid]);
			 if(!$hidemode && count($this->content->items) < $config->maxitems){
			 	$this->content->items[]  = $renderer->fetch_rating_history_item($panelid,$assiginfo,$rec->activityid,$mods[$rec->activityid]->name,$ratearea,$rec->rating, $config->allow_rerate,$parentmode,$config->bigicons);
			}
		}
	}
	
	//add a message if we have no ratings at all but we are not hidden
	if(!$hidemode && count($this->content->items)==0){
		$this->content->items[]  = $renderer->fetch_no_items_message();
	}
		
		//fetch any finished but unrated ones to show in block
		if(count($activityids) > 0){
			$notclause = ' AND activityid NOT IN (' . implode(',',$activityids) . ')';
		}else {
			$notclause  = '';
		}
		
		$unrated_recs = $DB->get_records_sql('SELECT * FROM {block_ratings_log} WHERE userid = ' . $ratingsuser->id. 
			' AND courseid = ' . $ratingscourse->id . 
			$notclause );
		if($unrated_recs){
			foreach($unrated_recs as $rec){
				if(!array_key_exists( $rec->activityid, $mods)){
					continue;
				}
				//continue if we already have a rating item for this (recently completed output above)
				if($rec->activityid == $recentlyfinished){
					continue;
				}
				
				$assiginfo = block_ratings_fetch_assignment_info_json($ratingscourse->id, $rec->activityid, $ratearea ,$mods[$rec->activityid]);
				 if(!$hidemode){
					$this->content->items[]  = $renderer->fetch_rating_history_item($panelid,$assiginfo,$rec->activityid,$mods[$rec->activityid]->name,$ratearea,$unrated,true,$parentmode,$config->bigicons);
				}
			}
		}
        
        //don't output anything if the block is empty
        if(!$recentlyfinished && count($this->content->items)==0){
        	return null;
        }else{
        	//$this->content->items[]  = $dialog;
        	$this->content->footer= $dialog; 
        	return $this->content;
        }
    }	

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
    /*
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
                     */
         return array('all' => false,
						'my'=>true,
                     'course-view' => true);
    }

    public function instance_allow_multiple() {
          return true;
    }
    
       /**
     * Returns true if this block has instance config.
     *
     * @return bool
     **/
    public function instance_allow_config() {
        return true;
    }

	  /**
     * Returns true if this block has admin config.
     *
     * @return bool
     **/
    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
             
                 // do something
                  
                      return true;
    }
}
