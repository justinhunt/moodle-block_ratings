<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
global $USER,$DB,$CFG;

//make sure we are actually real
require_login();
require_sesskey();


$action = required_param('action', PARAM_TEXT); // what to do 
$courseid =  required_param('courseid', PARAM_INT);
$activityid = optional_param('activityid', 0, PARAM_INT); 
$itemid = optional_param('itemid', 0, PARAM_INT); 
$ratearea = optional_param('ratearea', '', PARAM_TEXT); 
$panelid = optional_param('panelid', '', PARAM_TEXT);
$rating = optional_param('rating', 0, PARAM_INT); 
$parentmode = optional_param('parentmode', false, PARAM_BOOL); 
$heading = optional_param('heading', '', PARAM_TEXT); 
if (! $course = $DB->get_record("course", array("id"=>$courseid))) {
   print_error("Course ID not found");
}

switch($action){
	case 'update':
		$rate = new stdClass;
		$rate->courseid = $courseid;
		$rate->activityid = $activityid;
		$rate->itemid = $itemid;
		$rate->ratearea = $ratearea;
		$rate->userid = $USER->id;
		$rate->rating = $rating;
		$rate->time = time();

  
		$rec = $DB->get_record('block_ratings', 
			array('courseid'=>$courseid, 'activityid'=>$activityid, 
			'itemid'=>$itemid, 'ratearea'=>$ratearea, 'userid'=>$USER->id
			));
		
		if(!$rec){
			$rate->id = $DB->insert_record( 'block_ratings', $rate );
		}else{
			$rate->id = $rec->id;
			$DB->update_record('block_ratings',$rate);
		}
		$return =array('action'=>$action);
		echo json_encode($return);
		break;
	
	case 'fetchrecentcomplete':
		//get the course for this rating
		$course = get_course($courseid);
		
		//rateable, should get from ajax request really
		$config = get_config('block_ratings');
		if(!is_array($config->rateable)){
			$rateable = explode(',',$config->rateable);
		}else{
			$rateable = $config->rateable;
		}
		
		//update our completionlog
		block_ratings_update_completion_log($course,$USER, $rateable);

		//search the log for new completions
		$recentlyfinished = false;
		$current_assig_json =false;
		if(!$parentmode){
			$records = $DB->get_records('block_ratings_log',array('userid'=>$USER->id, 'new'=>1, 'courseid'=>$courseid));
			if($records){	
				$rec = array_shift($records);
				$DB->set_field('block_ratings_log', 'new', 0, array('id'=>$rec->id));
				$recentlyfinished = $rec->activityid;
			}
		}
	
		//if we have the new completion, fetch json data for that mod
		if($recentlyfinished){
			$mods = get_array_of_activities($courseid);
			foreach($mods as $mod){
				if($mod->cm == $recentlyfinished) {
				 	$current_assig_json = block_ratings_fetch_assignment_info_json($course->id,$recentlyfinished,$ratearea, $mod);
				 	break;
				}
			}
		}
	
		//return details to callback JS on page
		$return =array('action'=>$action,'panelid'=>$panelid, 'currentassig'=>$current_assig_json);
		echo json_encode($return);
		break;
}
?>


