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

	public static function get_object_meta( $object_id ) {

		global $wpdb;
		$tablename = $wpdb->prefix . 'nf_objectmeta';
		$results = $wpdb->get_results( "SELECT meta_key, meta_value FROM $tablename WHERE object_id = $object_id" );

		$meta_array = array();
		foreach( $results as $result ) {
			$meta_array[$result->meta_key] = $result->meta_value;
		}

		return $meta_array;
	}

}