<?php
/**
Plugin Name: Migrate Ninja Forms to Gravity Forms
Plugin URI: http://travislop.es/plugins/migrate-ninja-forms-to-gravity-forms/
Description: Imports content from Ninja Forms into Gravity Forms
Version: 0.2
Author: Travis Lopes
Author URI: http://travislop.es
Text Domain: migrate-ninja-forms-to-gravity-forms
 */

define( 'GF_MIGRATE_NINJAFORMS_VERSION', '0.1.3' );

// If Gravity Forms is loaded, bootstrap the Ninja Forms Migration Add-On.
add_action( 'gform_loaded', array( 'GF_Migrate_NinjaForms_Bootstrap', 'load' ), 5 );

/**
 * Class GF_Migrate_NinjaForms_Bootstrap
 *
 * Handles the loading of the Ninja Forms Migration Add-On and registers with the Add-On framework.
 */
class GF_Migrate_NinjaForms_Bootstrap {

	/**
	 * If the Add-On Framework exists, Ninja Forms Migration Add-On is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-migrate-nf.php' );

		GFAddOn::register( 'GF_Migrate_NF' );

	}

}

/**
 * Returns an instance of the GF_Migrate_NF class
 *
 * @see    GF_Migrate_NF::get_instance()
 *
 * @return object GF_Migrate_NF
 */
function gf_migrate_ninjaforms() {
	return GF_Migrate_NF::get_instance();
}
