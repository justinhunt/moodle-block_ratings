<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $USER,$DB,$CFG;

//make sure we are actually real
require_login();

$courseid = $_REQUEST['courseid'];
$activityid = $_REQUEST['activityid'];
$itemid = $_REQUEST['itemid'];
$ratearea = $_REQUEST['ratearea'];
$rating = $_REQUEST['rating'];
$heading = $_REQUEST['heading'];
if (! $course = $DB->get_record("course", array("id"=>$courseid))) {
   print_error("Course ID not found");
}
$rate = new stdClass;
$rate->courseid = $courseid;
$rate->activityid = $activityid;
$rate->itemid = $itemid;
$rate->ratearea = $ratearea;
$rate->userid = $USER->id;
$rate->rating = $rating;
$rate->time = time();

//modify Justin: to allow re rating
//if(!$DB->record_exists('local_rating', array('courseid'=>$courseid, 'activityid'=>$activityid, 'itemid'=>$itemid, 'ratearea'=>$ratearea, 'userid'=>$USER->id)))
//	{$rate->id = $DB->insert_record( 'local_rating', $rate );}
    
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
   
echo ('ok');
?>


