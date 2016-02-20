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

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Register required files and filters.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		require_once 'includes/class-gf-migrate-nf-api.php';

		parent::init();

	}

	public function init_frontend() {

		parent::init_frontend();

		//echo '<pre>';
		//foreach ( GF_Migrate_NF_API::get_forms() as $nf_form ) {
		//	$form = $this->convert_form( $nf_form );
		//	var_dump( $form );
		//echo '<hr />';
		//}
		//echo '</pre>';

	}

	/**
	 * Convert a Ninja Form to a Gravity Form.
	 * 
	 * @access public
	 * @param mixed $ninja_form
	 * @return array $form
	 */
	public function convert_form( $ninja_form ) {

		// Create a new Gravity Forms form object.
		$form = array(
			'title'         => rgar( $ninja_form, 'form_title' ), // Form title
			'requireLogin'  => rgar( $ninja_form, 'logged_in' ), // Require login
			'fields'        => array(),
			'confirmations' => array(),
			'notifications' => array(),
		);

		// Prepare fields.
		foreach ( $ninja_form['fields'] as $field ) {
			$form = $this->prepare_field( $form, $field );
		}

		// Prepare notifications.
		foreach ( $ninja_form['notifications'] as $nf_notification ) {
			$form = $this->convert_notification( $form, $nf_notification );
		}

		return $form;

	}

	/**
	 * Convert a Ninja Forms notification to a Gravity Forms notification/confirmation.
	 *
	 * @access public
	 * @param array $form - The new Gravity Forms form object
	 * @param array $nf_notification - The Ninja Forms notification
	 * @return array $form
	 */
	public function convert_notification( $form, $nf_notification ) {

		// If notification type is redirect, convert to confirmation.
		if ( 'redirect' === $nf_notification['type'] ) {

			// Create a new confirmation.
			$confirmation = array(
				'id'       => uniqid(),
				'isActive' => boolval( $nf_notification['active'] ),
				'name'     => $nf_notification['name'],
				'type'     => 'redirect',
				'url'      => $nf_notification['redirect_url'],
			);

			// Add confirmation to form.
			$form['confirmations'][ $confirmation['id'] ] = $confirmation;

		}

		// If notification type is success message, convert to confirmation.
		if ( 'success_message' === $nf_notification['type'] ) {

			// Create a new confirmation.
			$confirmation = array(
				'id'       => uniqid(),
				'isActive' => boolval( $nf_notification['active'] ),
				'name'     => $nf_notification['name'],
				'type'     => 'message',
				'message'  => $this->convert_to_merge_tags( $form, $nf_notification['success_msg'] ),
			);

			// Add confirmation to form.
			$form['confirmations'][ $confirmation['id'] ] = $confirmation;

		}

		// If notification type is email, convert to notification.
		if ( 'email' === $nf_notification['type'] ) {

			// Create a new notification.
			$notification = array(
				'id'       => uniqid(),
				'isActive' => boolval( $nf_notification['active'] ),
				'name'     => $nf_notification['name'],
				'message'  => $this->convert_to_merge_tags( $form, $nf_notification['email_message'] ),
				'subject'  => $this->convert_from_backticks( $form, $nf_notification['email_subject'] ),
				'to'       => $this->convert_from_backticks( $form, $nf_notification['bcc'] ),
				'toType'   => 'email',
				'from'     => $this->convert_from_backticks( $form, $nf_notification['from_address'] ),
				'fromName' => $this->convert_from_backticks( $form, $nf_notification['from_name'] ),
				'replyTo'  => $this->convert_from_backticks( $form, $nf_notification['reply_to'] ),
				'bcc'      => $this->convert_from_backticks( $form, $nf_notification['bcc'] ),
			);
			
			// Add notification to form.
			$form['notifications'][ $notification['id'] ] = $notification;

		}

		return $form;

	}

	/**
	 * Convert a Ninja Form field to a Gravity Forms field.
	 *
	 * @access public
	 * @param array $form - The new Gravity Forms form object
	 * @param array $nf_field - The Ninja Forms field
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
			
			if ( $nf_field['user_email'] == '1' ) {
				$form['fields'][ $nf_field['id'] ] = $this->prepare_email_field( $nf_field );
			}
		
		} else {
			
			$form['fields'][ $nf_field['id'] ] = call_user_func_array( array( $this, 'prepare_' . rgar( $supported_fields, $nf_field['type'] ) . '_field' ), array( $nf_field ) );
			
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
		$field->id           = rgar( $nf_field, 'id');
		$field->label        = rgar( $nf_field, 'label' );
		$field->adminLabel   = rgar( $nf_field, 'admin_label' );
		$field->isRequired   = rgar( $nf_field, 'req' );
		$field->cssClass     = rgar( $nf_field, 'class' );
		$field->defaultValue = rgar( $nf_field, 'default_value' );
		
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
		$field->id                = rgar( $nf_field, 'id');
		$field->label             = rgar( $nf_field, 'label' );
		$field->adminLabel        = rgar( $nf_field, 'admin_label' );
		$field->isRequired        = rgar( $nf_field, 'req' );
		$field->cssClass          = rgar( $nf_field, 'class' );
		$field->defaultValue      = rgar( $nf_field, 'default_value_type' ) === '_custom' ? rgar( $nf_field, 'default_value' ) : null;
		$field->useRichTextEditor = rgar( $nf_field, 'textarea_rte' );
		
		return $field;
		
	}

	/**
	 * Converts any Ninja Forms shortcodes in a string to Gravity Forms merge tags.
	 * 
	 * @access public
	 * @param array $form
	 * @param string $text (default: '')
	 * @return string $text
	 */
	public function convert_to_merge_tags( $form, $text = '' ) {
		
		// If no text was provided, return it.
		if ( rgblank( $text ) ) {
			return $text;
		}
		
		// Convert all fields shortcode.
		$text = str_replace( '[ninja_forms_all_fields]', '{all_fields}', $text );
		
		// Search for other Ninja Forms shortcodes.
		preg_match_all( "/(\\[ninja_forms_field id=([0-9].*)\\])/mi", $text, $matches );
		
		// Loop through each shortcode match and convert to merge tags.
		foreach ( $matches[0] as $i => $shortcode ) {
			
			// Get the field id.
			$field_id = $matches[2][ $i ];
			
			// Make sure the field exists.
			if ( ! isset ($form['fields'][ $field_id ] ) ) {
				continue;
			}
			
			// Prepare merge tag.
			$merge_tag = '{' . $form['fields'][ $field_id ]->label . ':' . $field_id . '}';
			
			// Replace shortcode.
			$text = str_replace( $shortcode, $merge_tag, $text );
			
		}
		
		return $text;
		
	}
	
	/**
	 * Convert backticks separated list to a comma separated list.
	 * 
	 * @access public
	 * @param array $form
	 * @param string $text (default: '')
	 * @return string $text
	 */
	public function convert_from_backticks( $form, $text = '' ) {

		// If no text was provided, return it.
		if ( rgblank( $text ) ) {
			return $text;
		}

		// Explode the string.
		$exploded = explode( '`', $text );

		// Convert fields to merge tags where needed.
		foreach ( $exploded as &$part ) {

			// If this is not a field part, skip it.
			if ( strpos( $part, 'field_' ) !== 0 ) {
				continue;
			}

			// Get the field ID.
			$field_id = str_replace( 'field_', '', $part );

			// Make sure the field exists.
			if ( ! isset ( $form['fields'][ $field_id ] ) ) {
				continue;
			}

			// Replace part with merge tag.
			$part = '{' . $form['fields'][ $field_id ]->label . ':' . $field_id . '}';

		}

		// Implode it.
		$text = implode( ',', $exploded );

		return $text;

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
			'_textarea' => 'textarea',
		);

	}

}
