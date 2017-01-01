<?php

class GF_Migrate_NF2_Field {

	/**
	 * Stores the Gravity Forms field information for the applicable field
	 *
	 * @since  0.1
	 * @access public
	 * @static
	 * @var array $field The Gravity Forms field properties.
	 */
	public static $field = array();

	/**
	 * The Ninja Forms field data that is being converted
	 *
	 * @since  0.1
	 * @access public
	 * @static
	 * @var array $field The Ninja Forms field data
	 */
	public static $nf_field = array();

	/**
	 * Convert a Ninja Forms field to a Gravity Forms field.
	 *
	 * @since  0.1
	 * @access public
	 * @param  array $nf_field The Ninja Forms field.
	 *
	 * @return array $field - The new Gravity Forms field
	 */
	public static function convert_field( $nf_field ) {

		// Reset field.
		self::$field = array();

		// Stores the field to be converted within the class property for use later
		self::$nf_field = $nf_field;

		// Determine what the Ninja Forms field will be converted to.
		switch ( self::$nf_field['type'] ) {

			case '_checkbox':
				self::convert_single_checkbox_field();
				break;

			case '_desc':
				self::convert_html_field();
				break;

			case '_hidden':
				self::convert_hidden_field();
				break;

			case '_list':

				$list_type = rgars( self::$nf_field, 'data/list_type' );

				if ( 'checkbox' === $list_type ) {
					self::convert_checkbox_field();
				} else if ( 'dropdown' === $list_type ) {
					self::convert_select_field();
				} else if ( 'multi' === $list_type ) {
					self::convert_select_field( true );
				} else if ( 'radio' === $list_type ) {
					self::convert_radio_field();
				}

				break;

			case '_number':
				self::convert_number_field();
				break;

			case '_profile_pass':
				self::convert_password_field();
				break;

			case '_text':
				if ( '1' === self::$nf_field['data']['user_email'] ) {
					self::convert_email_field();
				} else if ( 'date' === self::$nf_field['data']['mask'] ) {
					self::convert_date_field();
				} else if ( 'currency' === self::$nf_field['data']['mask'] ) {
					self::convert_number_field();
				} else if ( '(999) 999-9999' === self::$nf_field['data']['mask'] || '1' === self::$nf_field['data']['user_phone'] ) {
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
	 * Convert Ninja Forms field to a Gravity Forms checkbox field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_checkbox_field() {

		// Create a new Checkbox field.
		self::$field['type'] = 'checkbox';

		// Add standard properties.
		self::add_standard_properties();

		// Loop through field options.
		foreach ( self::$nf_field['data']['list']['options'] as $i => $option ) {

			// Get checkbox ID.
			$id = $i + 1;

			// Skip multiple of 10 on checkbox ID.
			if ( 0 === $id % 10 ) {
				$id++;
			}

			// Add option choices.
			self::$field['choices'][] = array(
				'text'       => $option['label'],
				'value'      => $option['value'],
				'isSelected' => $option['selected'],
			);

			// Add option input.
			self::$field['inputs'][] = array(
				'id'    => self::$field['id'] . '.' . $id,
				'label' => $option['label'],
			);

		}

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms date field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_date_field() {

		// Create a new Phone field.
		self::$field['type'] = 'date';

		// Add standard properties.
		self::add_standard_properties();

		// Add Phone specific properties.
		self::$field['dateFormat'] = 'dd/mm/yyyy';

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms email field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_email_field() {

		// Create a new Email field.
		self::$field['type'] = 'email';

		// Add standard properties.
		self::add_standard_properties();

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms hidden field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_hidden_field() {

		// Create a new Hidden field.
		self::$field['type'] = 'hidden';

		// Add standard properties.
		self::add_standard_properties();

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms HTML field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_html_field() {

		// Create a new HTML field.
		self::$field['type'] = 'html';

		// Add standard properties.
		self::$field['id']       = rgar( self::$nf_field, 'id' );
		self::$field['label']    = rgars( self::$nf_field, 'data/label' );
		self::$field['cssClass'] = rgars( self::$nf_field, 'data/class' );

		// Add HTML specific properties.
		self::$field['content'] = rgars( self::$nf_field, 'data/default_value' );

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms number field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_number_field() {

		// Create a new Number field.
		self::$field['type'] = 'number';

		// Add standard properties.
		self::add_standard_properties();

		// Add Number specific properties.
		self::$field['rangeMin'] = rgars( self::$nf_field, 'data/number_min' );
		self::$field['rangeMax'] = rgars( self::$nf_field, 'data/number_max' );

		// Add currency property if needed.
		if ( rgars( self::$nf_field, 'data/mask' ) && 'currency' === self::$nf_field['data']['mask'] ) {
			self::$field['numberFormat'] = 'currency';
		}

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms password field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_password_field() {

		// Create a new Password field.
		self::$field['type'] = 'password';

		// Add standard properties.
		self::add_standard_properties();

		// Add Password specific properties.
		self::$field['inputs'] = array(
			array(
				'id'          => '1',
				'label'       => esc_html__( 'Enter Password', 'gravityforms' ),
				'name'        => '',
				'customLabel' => self::$nf_field['data']['label'],
			),
			array(
				'id'          => '1.2',
				'label'       => esc_html__( 'Confirm Password', 'gravityforms' ),
				'name'        => '',
				'customLabel' => self::$nf_field['data']['re_pass'],
			),
		);

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms phone field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_phone_field() {

		// Create a new Phone field.
		self::$field['type'] = 'phone';

		// Add standard properties.
		self::add_standard_properties();

		// Add Phone specific properties.
		self::$field['phoneFormat'] = 'standard';

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms radio field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_radio_field() {

		// Create a new Radio field.
		self::$field['type'] = 'radio';

		// Add standard properties.
		self::add_standard_properties();

		// Loop through field options.
		foreach ( self::$nf_field['data']['list']['options'] as $option ) {

			// Add option choice.
			self::$field['choices'][] = array(
				'text'  => $option['label'],
				'value' => $option['value'],
			);

			// If option is selected, set as default value.
			if ( '1' === $option['selected'] ) {
				self::$field['defaultValue'] = ! empty( $option['value'] ) ? $option['value'] : $option['text'];
			}

		}

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms select field.
	 *
	 * @since  0.1
	 * @access public
	 * @param  bool $multi Is field a Multi Select field? Default false
	 */
	public static function convert_select_field( $multi = false ) {

		// Create a new Select field.
		self::$field['type'] = $multi ? 'multiselect' : 'select';

		// Add standard properties.
		self::add_standard_properties();

		// Loop through field options.
		foreach ( self::$nf_field['data']['list']['options'] as $option ) {

			// Add option.
			self::$field['choices'][] = array(
				'text'  => $option['label'],
				'value' => $option['value'],
			);

			// If option is selected, set as default value.
			if ( '1' === $option['selected'] ) {
				self::$field['defaultValue'] = ! empty( $option['value'] ) ? $option['value'] : $option['text'];
			}

		}

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms checkbox field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_single_checkbox_field() {

		// Create a new Checkbox field.
		self::$field['type'] = 'checkbox';

		// Add standard properties.
		self::add_standard_properties();

		// Add choices property.
		self::$field['choices'] = array(
			array(
				'text'       => self::$nf_field['data']['label'],
				'value'      => '',
				'isSelected' => 'unchecked' === self::$nf_field['data']['default_value'] ? null : '1',
			),
		);
		
		// Remove unchecked default value.
		if ( 'unchecked' === self::$nf_field['data']['default_value'] ) {
			self::$field['default_value'] = null;
		}

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms text field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_text_field() {

		// Create a new Text field.
		self::$field['type'] = 'text';

		// Add standard properties.
		self::add_standard_properties();

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms textarea field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_textarea_field() {

		// Create a new Textarea field.
		self::$field['type'] = 'textarea';

		// Add standard properties.
		self::add_standard_properties();

		// Add Textarea specific properties.
		self::$field['useRichTextEditor'] = rgars( self::$nf_field, 'data/textarea_rte' );

	}

	/**
	 * Adds standard Gravity Forms field properties.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function add_standard_properties() {

		// Set properties.
		self::$field['id']           = rgar( self::$nf_field, 'id' );
		self::$field['label']        = rgars( self::$nf_field, 'data/label' );
		self::$field['adminLabel']   = rgars( self::$nf_field, 'data/admin_label' );
		self::$field['isRequired']   = rgars( self::$nf_field, 'data/req' );
		self::$field['cssClass']     = rgars( self::$nf_field, 'data/class' );
		self::$field['description']  = rgars( self::$nf_field, 'data/desc_text' );
		self::$field['defaultValue'] = rgars( self::$nf_field, 'data/default_value_type' ) === '_custom' || rgars( self::$nf_field, 'data/default_value_type' ) === '' ? rgars( self::$nf_field, 'data/default_value' ) : null;

	}

}

class GF_Migrate_NF3_Field {

	/**
	 * Stores the Gravity Forms field information for the applicable field
	 *
	 * @since  0.2
	 * @access public
	 * @static
	 * @var array $field The Gravity Forms field properties.
	 */
	public static $gf_field = array();

	/**
	 * The Ninja Forms field data that is being converted
	 *
	 * @since  0.2
	 * @access public
	 * @static
	 * @var array $field The Ninja Forms field data
	 */
	public static $nf_field = array();

	/**
	 * Convert a Ninja Forms field to a Gravity Forms field.
	 *
	 * @since  0.2
	 * @access public
	 * @param  array $nf_field The Ninja Forms field.
	 *
	 * @return array $field - The new Gravity Forms field
	 */
	public static function convert_field( $nf_field ) {

		// Reset field.
		self::$gf_field = array();

		// Stores the field to be converted within the class property for use later
		self::$nf_field = $nf_field;
		
		// If Ninja Forms field is supported, convert it.
		if ( is_callable( array( 'GF_Migrate_NF3_Field', 'convert_' . self::$nf_field->get_setting( 'type' ) . '_field' ) ) ) {
			call_user_func( array( 'GF_Migrate_NF3_Field', 'convert_' . self::$nf_field->get_setting( 'type' ) . '_field' ) );
		}
		
		return self::$gf_field;
		
	}
	
	/**
	 * Convert Ninja Forms field to a Gravity Forms textarea field.
	 *
	 * @since  0.2
	 * @access public
	 */
	public static function convert_textarea_field() {
		
		// Define field type.
		self::$gf_field['type'] = 'textarea';
		
		// Add standard properties.
		self::add_standard_properties();
		
		// Add textarea properties.
		self::$gf_field['useRichTextEditor'] = self::$nf_field->get_setting( 'textarea_rte' );
		self::$gf_field['placeholder']       = self::$nf_field->get_setting( 'placeholder' );
		
		// Add maximum characters.
		if ( self::$nf_field->get_setting( 'input_limit' ) && 'characters' === self::$nf_field->get_setting( 'input_limit_type' ) ) {
			self::$gf_field['maxLength'] = self::$nf_field->get_setting( 'input_limit' );
		}
		
	}
	
	/**
	 * Adds standard Gravity Forms field properties.
	 *
	 * @since  0.2
	 * @access public
	 */
	public static function add_standard_properties() {
		
		// Set properties.
		self::$gf_field['id']           = self::$nf_field->get_id();
		self::$gf_field['label']        = self::$nf_field->get_setting( 'label' );
		self::$gf_field['adminLabel']   = self::$nf_field->get_setting( 'admin_label' );
		self::$gf_field['isRequired']   = self::$nf_field->get_setting( 'required' );
		self::$gf_field['cssClass']     = self::$nf_field->get_setting( 'container_class' );
		self::$gf_field['description']  = self::$nf_field->get_setting( 'desc_text' );
		self::$gf_field['defaultValue'] = self::$nf_field->get_setting( 'default' );

	}

}
