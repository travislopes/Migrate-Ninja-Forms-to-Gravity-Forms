<?php

GFForms::include_addon_framework();

class GF_Migrate_NF extends GFAddOn {
	
	protected $_version                  = GF_MIGRATE_NINJAFORMS_VERSION;
	protected $_min_gravityforms_version = '1.9.10';
	protected $_slug                     = 'migrate-ninja-forms-to-gravity-forms';
	protected $_path                     = 'migrate-ninja-forms-to-gravity-forms/migrate-nf.php';
	protected $_full_path                = __FILE__;
	protected $_url                      = 'http://www.gravityforms.com';
	protected $_title                    = 'Migrate Ninja Forms';
	protected $_short_title              = 'Migrate Ninja Forms';
	private static $_instance            = null;

	/**
	 * Get instance of this class.
	 * 
	 * @access public
	 * @static
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}
	
	public function init_frontend() {
		
		echo '<pre>';
		$nf_forms = $this->get_nf_forms();
		foreach ( $nf_forms as $nf_form ) {
			$form = $this->convert_form( $nf_form );
			//GFAPI::add_form( $form );
		}
		echo '</pre>';
		
	}

	/**
	 * Convert a Ninja Form to a Gravity Form.
	 * 
	 * @access public
	 * @param mixed $ninja_form
	 * @return array $form
	 */
	public function convert_form( $ninja_form ) {
		
		//var_dump( $ninja_form );
		
		// Create a new Gravity Forms form object.
		$form = array(
			'title'         => rgar( $ninja_form->settings, 'form_title' ), // Form title
			'requireLogin'  => rgar( $ninja_form->settings, 'logged_in' ), // Require login
			'fields'        => array(),
			'confirmations' => array(),
			'notifications' => array()
		);
		
		// Prepare fields.
		foreach ( $ninja_form->fields as $field ) {
			$form = $this->prepare_field( $form, $field );
		}
		
		foreach ( $form['fields'] as $i => &$field ) {
			$field->id = $i;
		}
		
		return $form;
		
	}
	
	/**
	 * Convert a Ninja Form field to a Gravity Forms field.
	 * 
	 * @access public
	 * @param array $form - The new Gravity Forms form object
	 * @param array $field - The Ninja Forms field
	 * @return array $form
	 */
	public function prepare_field( $form, $nf_field ) {
		
		// Get list of supported Gravity Forms fields.
		$supported_fields = $this->get_supported_fields();
		
		//var_dump( $nf_field );
				
		// If this field is not supported, skip it.
		if ( ! rgar( $supported_fields, $nf_field['type'] ) ) {
			return $form;
		}
	
		if ( $nf_field['type'] === '_text' ) {
			
			if ( $nf_field['data']['user_email'] == '1' ) {
				$form['fields'][] = $this->prepare_email_field( $nf_field );
			}
		
		} else {
			
			$form['fields'][] = call_user_func_array( array( $this, 'prepare_' . rgar( $supported_fields, $nf_field['type'] ) . '_field' ), array( $nf_field ) );
			
		}
		

		return $form;		
		
	}
	
	/**
	 * Prepare a Gravity Forms email field.
	 * 
	 * @access public
	 * @param array $nf_field
	 * @return object $field
	 */
	public function prepare_email_field( $nf_field ) {
		
		// Create a new Email field.
		$field = new GF_Field_Email();
		
		// Set basic attributes.
		$field->label             = rgars( $nf_field, 'data/label' );
		$field->adminLabel        = rgars( $nf_field, 'data/admin_label' );
		$field->isRequired        = rgars( $nf_field, 'data/req' );
		$field->cssClass          = rgars( $nf_field, 'data/class' );
		$field->defaultValue      = rgars( $nf_field, 'data/default_value' );
		$field->useRichTextEditor = rgars( $nf_field, 'data/textarea_rte' );
		
		return $field;
		
	}
	
	/**
	 * Prepare a Gravity Forms textarea field.
	 * 
	 * @access public
	 * @param array $nf_field
	 * @return object $field
	 */
	public function prepare_textarea_field( $nf_field ) {
		
		// Create a new Textarea field.
		$field = new GF_Field_Textarea();
		
		// Set basic attributes.
		$field->label        = rgars( $nf_field, 'data/label' );
		$field->adminLabel   = rgars( $nf_field, 'data/admin_label' );
		$field->isRequired   = rgars( $nf_field, 'data/req' );
		$field->cssClass     = rgars( $nf_field, 'data/class' );
		$field->defaultValue = rgars( $nf_field, 'data/default_value_type' ) === '_custom' ? rgars( $nf_field, 'data/default_value' ) : null;
		
		return $field;
		
	}
	
	/**
	 * Get an array of supported Gravity Forms fields and their class names.
	 * 
	 * @access public
	 * @return array
	 */
	public function get_supported_fields() {
		
		return array(
			'_text'     => 'GF_Field_Text',
			'_textarea' => 'textarea'
		);
		
	}

	/**
	 * Get an array of Ninja Forms forms.
	 * 
	 * @access public
	 * @return array $forms
	 */
	public function get_nf_forms() {
		
		// Create return array.
		$forms = array();
		
		// Initialize a NF_Forms instance.
		$nf_forms = new NF_Forms();
		
		foreach ( $nf_forms->get_all() as $form_id ) {
			
			$forms[] = new NF_Form( $form_id );
			
		}
		
		// Return forms.
		return $forms;
		
	}

}