<?php

class GF_Migrate_NF_Field {

	/**
	 * Stores the Gravity Forms field information for the applicable field
	 *
	 * @since  0.1
	 * @access public
	 * @static
	 * @var mixed $field The Gravity Forms field object
	 */
	public static $field = null;

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

				$list_type = rgar( self::$nf_field, 'list_type' );

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

				if ( '1' === self::$nf_field['user_email'] ) {
					self::convert_email_field();
				} else if ( 'date' === self::$nf_field['mask'] ) {
					self::convert_date_field();
				} else if ( 'currency' === self::$nf_field['mask'] ) {
					self::convert_number_field();
				} else if ( '(999) 999-9999' === self::$nf_field['mask'] || '1' === self::$nf_field['user_phone'] ) {
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
		self::$field = new GF_Field_Checkbox();

		// Add standard properties.
		self::add_standard_properties();

		// Add choices property.
		self::$field->choices = array();

		// Loop through field options.
		foreach ( self::$nf_field['list']['options'] as $i => $option ) {

			// Get checkbox ID.
			$id = $i + 1;

			// Add option choices.
			self::$field->choices[] = array(
				'text'       => $option['label'],
				'value'      => $option['value'],
				'isSelected' => $option['selected'],
			);

			// Add option input.
			self::$field->inputs[] = array(
				'id'    => self::$field->id . '.' . $id,
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
		self::$field = new GF_Field_Date();

		// Add standard properties.
		self::add_standard_properties();

		// Add Phone specific properties.
		self::$field->dateFormat = 'dd/mm/yyyy';

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms email field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_email_field() {

		// Create a new Email field.
		self::$field = new GF_Field_Email();

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
		self::$field = new GF_Field_Hidden();

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
		self::$field = new GF_Field_HTML();

		// Add standard properties.
		self::$field->id           = rgar( self::$nf_field, 'id' );
		self::$field->label        = rgar( self::$nf_field, 'label' );
		self::$field->cssClass     = rgar( self::$nf_field, 'class' );

		// Add HTML specific properties.
		self::$field->content = self::$nf_field['default_value'];

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms number field.
	 *
	 * @since 0.1
	 * @access public
	 */
	public static function convert_number_field() {

		// Create a new Number field.
		self::$field = new GF_Field_Number();

		// Add standard properties.
		self::add_standard_properties();

		// Add Number specific properties.
		self::$field->rangeMin = rgar( self::$nf_field, 'number_min' );
		self::$field->rangeMax = rgar( self::$nf_field, 'number_max' );

		// Add currency property if needed.
		if ( rgar( self::$nf_field, 'mask' ) && 'currency' === self::$nf_field['mask'] ) {
			self::$field->numberFormat = 'currency';
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
		self::$field = new GF_Field_Password();

		// Add standard properties.
		self::add_standard_properties();

		// Add Password specific properties.
		self::$field->inputs = array(
			array(
				'id'          => '1',
				'label'       => esc_html__( 'Enter Password', 'gravityforms' ),
				'name'        => '',
				'customLabel' => self::$nf_field['label'],
			),
			array(
				'id'          => '1.2',
				'label'       => esc_html__( 'Confirm Password', 'gravityforms' ),
				'name'        => '',
				'customLabel' => self::$nf_field['re_pass'],
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
		self::$field = new GF_Field_Phone();

		// Add standard properties.
		self::add_standard_properties();

		// Add Phone specific properties.
		self::$field->phoneFormat = 'standard';

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms radio field.
	 *
	 * @0.1
	 * @access public
	 */
	public static function convert_radio_field() {

		// Create a new Radio field.
		self::$field = new GF_Field_Radio();

		// Add standard properties.
		self::add_standard_properties();

		// Add choices property.
		self::$field->choices = array();

		// Loop through field options.
		foreach ( self::$nf_field['list']['options'] as $option ) {

			// Add option choice.
			self::$field->choices[] = array(
				'text'  => $option['label'],
				'value' => $option['value'],
			);

			// If option is selected, set as default value.
			if ( '1' === $option['selected'] ) {
				self::$field->defaultValue = ! empty( $option['value'] ) ? $option['value'] : $option['text'];
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
		self::$field = $multi ? new GF_Field_MultiSelect() : new GF_Field_Select();

		// Add standard properties.
		self::add_standard_properties();

		// Add choices property.
		self::$field->choices = array();

		// Loop through field options.
		foreach ( self::$nf_field['list']['options'] as $option ) {

			// Add option.
			self::$field->choices[] = array(
				'text'  => $option['label'],
				'value' => $option['value'],
			);

			// If option is selected, set as default value.
			if ( '1' === $option['selected'] ) {
				self::$field->defaultValue = ! empty( $option['value'] ) ? $option['value'] : $option['text'];
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
		self::$field = new GF_Field_Checkbox();

		// Add standard properties.
		self::add_standard_properties();

		// Add choices property.
		self::$field->choices = array(
			array(
				'text'       => self::$nf_field['label'],
				'value'      => '',
				'isSelected' => 'unchecked' === self::$nf_field['default_value'] ? '0' : '1',
			),
		);

	}

	/**
	 * Convert Ninja Forms field to a Gravity Forms text field.
	 *
	 * @since 0.1
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
	 * @since 0.1
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
	 * @since 0.1
	 * @access public
	 */
	public static function add_standard_properties() {

		// Set properties.
		self::$field->id           = rgar( self::$nf_field, 'id' );
		self::$field->label        = rgar( self::$nf_field, 'label' );
		self::$field->adminLabel   = rgar( self::$nf_field, 'admin_label' );
		self::$field->isRequired   = rgar( self::$nf_field, 'req' );
		self::$field->cssClass     = rgar( self::$nf_field, 'class' );
		self::$field->defaultValue = rgar( self::$nf_field, 'default_value_type' ) === '_custom' || rgar( self::$nf_field, 'default_value_type' ) === '' ? rgar( self::$nf_field, 'default_value' ) : null;

	}

}
