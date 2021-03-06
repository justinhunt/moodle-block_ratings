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
 * The ratings block helper functions and callbacks
 *
 * @package   block_ratings
 * @copyright 2014 Justin Hunt <poodllsupport@google.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns list of courses passedin user is enrolled in and can access
 *
 *
 * @param string $userid
 * @param int $limit max number of courses
 * @return array
 */
function block_ratings_fetch_user_courses($userid, $limit=1) {
		global $DB;

	$sort = 'visible DESC,sortorder ASC';
	$user = $DB->get_record('user', array('id'=>$userid));

    // Guest account does not have any courses
    if (isguestuser() or !isloggedin()) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
                        'shortname', 'fullname', 'idnumber',
                        'startdate', 'visible',
                        'groupmode', 'groupmodeforce', 'cacherev');

   
    $fields = $basefields;
    if (in_array('*', $fields)) {
        $fields = array('*');
    }

    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $rawsorts = explode(',', $sort);
        $sorts = array();
        foreach ($rawsorts as $rawsort) {
            $rawsort = trim($rawsort);
            if (strpos($rawsort, 'c.') === 0) {
                $rawsort = substr($rawsort, 2);
            }
            $sorts[] = trim($rawsort);
        }
        $sort = 'c.'.implode(',c.', $sorts);
        $orderby = "ORDER BY $sort";
    }

    $wheres = array("c.id <> :siteid");
    $params = array('siteid'=>SITEID);

    if (isset($user->loginascontext) and $user->loginascontext->contextlevel == CONTEXT_COURSE) {
        // list _only_ this course - anything else is asking for trouble...
        $wheres[] = "courseid = :loginas";
        $params['loginas'] = $user->loginascontext->instanceid;
    }

    $coursefields = 'c.' .join(',c.', $fields);
    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;
    $wheres = implode(" AND ", $wheres);

    //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
    $sql = "SELECT $coursefields $ccselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                     WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                   ) en ON (en.courseid = c.id)
           $ccjoin
             WHERE $wheres
          $orderby";
    $params['userid']  = $user->id;
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['now1']    = round(time(), -2); // improves db caching
    $params['now2']    = $params['now1'];

    $courses = $DB->get_records_sql($sql, $params, 0, $limit);

    // preload contexts and check visibility
    foreach ($courses as $id=>$course) {
        context_helper::preload_from_record($course);
        if (!$course->visible) {
            if (!$context = context_course::instance($id, IGNORE_MISSING)) {
                unset($courses[$id]);
                continue;
            }
            if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                unset($courses[$id]);
                continue;
            }
        }
        $courses[$id] = $course;
    }


	//return the courses
    return $courses;


  }//end of function


function block_ratings_fetch_assignment_info_json($courseid, $cmid, $ratearea, $mod){
		$args = new stdClass();
		$args->courseid=$courseid;
		$args->activityid=$mod->cm;
		$args->activityname=htmlspecialchars($mod->name, ENT_QUOTES);
		$args->itemid=1;
		$args->ratearea=$ratearea;
		$jsargs = json_encode($args);
		return $jsargs;
	}

function block_ratings_update_completion_log($course,$ratingsuser, $rateable){
		global $DB, $USER;
		
		//$DB->delete_records('block_ratings_log');
		//$DB->delete_records('local_rating');
		
		$where = "courseid = " . $course->id . " AND userid = " . $ratingsuser->id;
		$loggedactivityids = $DB->get_fieldset_select('block_ratings_log','activityid',$where);
		if(!$loggedactivityids ){$loggedactivityids =array();}
		
		$completion = new completion_info($course);
		
		$modinfo = get_fast_modinfo($course, $ratingsuser->id);
		$coursemods = $modinfo->cms;
		//$coursemods = get_array_of_activities($course->id);
		
		//print_r($loggedactivityids );
		$newactarray = array_flip($loggedactivityids);
		//print_r($newactarray);
		//echo('<br />');
		foreach($coursemods as $coursemod){

			if(!array_key_exists($coursemod->id,$newactarray)){
				//if this is rateable
				if(in_array($coursemod->modname, $rateable)){

					//$data = $completion->get_data($mod, false, $ratingsuser->id);
					$data = $completion->get_data($coursemod, true, $ratingsuser->id);
					if($data->completionstate == COMPLETION_COMPLETE){
						$log = new stdClass();
						$log->userid=$ratingsuser->id;
						$log->courseid=$course->id;
						$log->activityid=$coursemod->id;
						$log->new=1;
						$log->logdate=time();
						$DB->insert_record('block_ratings_log',$log);
					}//end of if complete
				}//end of if rateable
			}//end of if arraykeyexists
		}//end of for each
	}