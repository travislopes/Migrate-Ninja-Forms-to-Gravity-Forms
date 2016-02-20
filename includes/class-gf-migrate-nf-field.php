<?php

class GF_Migrate_NF_Field {

	public static $field    = null;
	public static $nf_field = array();

	/**
	 * Convert a Ninja Form field to a Gravity Forms field.
	 *
	 * @access public
	 * @param array $nf_field - The Ninja Forms field
	 * @return array $field - The new Gravity Forms field
	 */
	public static function convert_field( $nf_field ) {
		
		self::$nf_field = $nf_field;
		
		// Determine what the Ninja Forms field will be converted to.
		switch ( self::$nf_field['type'] ) {
			
			case '_number':
			
				self::convert_number_field();
				
				break;
			
			case '_text':
				
				if ( '1' === self::$nf_field['user_email'] ) {
					self::convert_email_field();
				} else if ( 'date' === self::$nf_field['mask'] ) {
					self::convert_date_field();
				} else if ( 'currency' === self::$nf_field['mask'] ) {
					self::convert_number_field();
				} else if ( '(999) 999-9999' === self::$nf_field['mask'] ) {
					self::convert_phone_field();
				} else {
					self::convert_text_field();
				}
				
				break;
			
			case '_textarea':
			
				self::convert_textarea_field();
				
				break;
			
		}
		
		return self::$field;
		
	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms date field.
	 *
	 * @access public
	 */
	public static function convert_date_field() {
		
		// Create a new Phone field.
		self::$field = new GF_Field_Date();
		
		// Add standard properties.
		self::add_standard_properties();

		// Add Phone specific properties.
		self::$field->dateFormat = 'dd/mm/yyyy';
		
	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms email field.
	 *
	 * @access public
	 */
	public static function convert_email_field() {
		
		// Create a new Email field.
		self::$field = new GF_Field_Email();
		
		// Add standard properties.
		self::add_standard_properties();
		
	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms number field.
	 *
	 * @access public
	 */
	public static function convert_number_field() {
		
		// Create a new Number field.
		self::$field = new GF_Field_Number();
		
		// Add standard properties.
		self::add_standard_properties();

		// Add Number specific properties.
		self::$field->rangeMin = self::$nf_field['number_min'];
		self::$field->rangeMax = self::$nf_field['number_max'];
		
		// Add currency property if needed.
		if ( 'currency' === self::$nf_field['mask'] ) {
			self::$field->numberFormat = 'currency';
		}
		
	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms phone field.
	 *
	 * @access public
	 */
	public static function convert_phone_field() {
		
		// Create a new Phone field.
		self::$field = new GF_Field_Phone();
		
		// Add standard properties.
		self::add_standard_properties();

		// Add Phone specific properties.
		self::$field->phoneFormat = 'standard';
		
	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms text field.
	 *
	 * @access public
	 */
	public static function convert_text_field() {
		
		// Create a new Text field.
		self::$field = new GF_Field_Text();
		
		// Add standard properties.
		self::add_standard_properties();
		
	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms textarea field.
	 *
	 * @access public
	 */
	public static function convert_textarea_field() {
		
		// Create a new Textarea field.
		self::$field = new GF_Field_Textarea();
		
		// Add standard properties.
		self::add_standard_properties();
		
		// Add Textarea specific properties.
		self::$field->useRichTextEditor = rgar( self::$nf_field, 'textarea_rte' );
		
	}

	/**
	 * Adds standard Gravity Forms field properties.
	 * 
	 * @access public
	 */
	public static function add_standard_properties() {
		
		// Set properties.
		self::$field->id           = rgar( self::$nf_field, 'id');
		self::$field->label        = rgar( self::$nf_field, 'label' );
		self::$field->adminLabel   = rgar( self::$nf_field, 'admin_label' );
		self::$field->isRequired   = rgar( self::$nf_field, 'req' );
		self::$field->cssClass     = rgar( self::$nf_field, 'class' );
		self::$field->defaultValue = rgar( self::$nf_field, 'default_value_type' ) === '_custom' ? rgar( $nf_field, 'default_value' ) : null;		
		
	}

}