<?php // $Id: upgrade.php 905 2012-12-05 05:36:52Z malu $/

defined('MOODLE_INTERNAL') || die;

/**
 *  Sharing Cart upgrade
 *  
 *  @global moodle_database $DB
 */
function xmldb_block_ratings_upgrade($oldversion = 0)
{
	global $DB;
	
	$dbman = $DB->get_manager();
	
	 if ($oldversion < 2014072800) {

        // Define table local_rating to be created.
        $table = new xmldb_table('block_ratings');

        // Adding fields to table local_rating.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('activityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('ratearea', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('rating', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_rating.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_rating.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ratings savepoint reached.
        upgrade_plugin_savepoint(true, 2014072800, 'block', 'ratings');
    }
	 if ($oldversion < 2014110702) {
		// Define table local_rating to be created.
        $table = new xmldb_table('block_ratings');
		$field = new xmldb_field('latecompletion', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		
		// Conditionally launch add field filename.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		 // online PoodLL savepoint reached
        upgrade_plugin_savepoint(true, 2014110702, 'block', 'ratings');
		
		
	 }
	return true;
}
