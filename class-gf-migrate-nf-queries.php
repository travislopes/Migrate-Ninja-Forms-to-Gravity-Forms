<?php
/**
 * Used for pulling Ninja Forms data
 */
class GF_Migrate_NF_Queries {

	/**
	 * This could turn out to be a massive query if dataset is huge.
	 * Might want to change this up in the future.
	 */
	public static function get_table_data( $tablename ) {

		global $wpdb;
		$tablename  = $wpdb->prefix . $tablename;
		$results = $wpdb->get_results( "SELECT * FROM $tablename" );

		return $results;
	}

}