<?php

/*
Plugin Name: Migrate Ninja Forms to Gravity Forms
Plugin URI: http://travislop.es/plugins/migrate-ninja-forms-to-gravity-forms/
Description: Imports content from Ninja Forms into Gravity Forms
Version: 0.1
Author: travislopes
Author URI: http://travislop.es
Text Domain: migrate-ninja-forms-to-gravity-forms
*/

define( 'GF_MIGRATE_NINJAFORMS_VERSION', '0.1' );

add_action( 'gform_loaded', array( 'GF_Migrate_NinjaForms_Bootstrap', 'load' ), 5 );

class GF_Migrate_NinjaForms_Bootstrap {

	/**
	 * Register Add-On with Gravity Forms.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-migrate-nf.php' );

		GFAddOn::register( 'GF_Migrate_NF' );

	}

}

function gf_migrate_ninjaforms() {
	return GF_Migrate_NF::get_instance();
}
