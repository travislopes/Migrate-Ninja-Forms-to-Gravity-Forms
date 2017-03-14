<?php

GFForms::include_addon_framework();

/**
 * Class GF_Migrate_NF
 *
 * Uses the Gravity Forms Add-On Framework use native elements
 *
 * @since 0.1
 */
class GF_Migrate_NF extends GFAddOn {

	/**
	 * Migrate Ninja Forms to Gravity Forms version number
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_version Plugin version number
	 */
	protected $_version = GF_MIGRATE_NINJAFORMS_VERSION;

	/**
	 * Minimum supported version of Gravity Forms
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version of Gravity Forms this plugin supports
	 */
	protected $_min_gravityforms_version = '1.9.10';

	/**
	 * The plugin slug.  Primarily used in the directory name
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_slug The slug (no slimy, and not a bug)
	 */
	protected $_slug = 'migrate-ninja-forms-to-gravity-forms';

	/**
	 * Path to the plugin file, relative to the wp-content/plugins directory
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_path The path.
	 */
	protected $_path = 'migrate-ninja-forms-to-gravity-forms/migrate-nf.php';

	/**
	 * The absolute path to the main class file.
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_full_path The path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * The URL for more information about this plugin
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_url A super cool site that all the cool kids go to
	 */
	protected $_url = 'http://travislop.es/plugins/migrate-ninja-forms-to-gravity-forms/';

	/**
	 * The title of this plugin
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_title The title
	 */
	protected $_title = 'Migrate Ninja Forms';

	/**
	 * The shorter title
	 *
	 * @since  0.1
	 * @access protected
	 * @var    string $_short_title The title.  In case the other one was too long for you ;)
	 */
	protected $_short_title = 'Migrate Ninja Forms';

	/**
	 * The instance of this class.  Used to instantiate.
	 *
	 * @since  0.1
	 * @access protected
	 * @var    object $_instance The instance
	 */
	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @since  0.1
	 * @access public
	 * @static
	 *
	 * @return object $_instance The instance of this object
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
	 * @since  0.1
	 * @access public
	 */
	public function init() {

		// Load needed classes.
		require_once 'includes/class-gf-migrate-nf-api.php';
		require_once 'includes/class-gf-migrate-nf-field.php';

		// Add "Migrate Ninja Forms" tab to Migrate/Export page.
		add_filter( 'gform_export_menu', array( $this, 'add_migrate_tab' ), 10, 1 );

		// Render "Migrate Ninja Forms" tab.
		add_action( 'gform_export_page_migrate_ninja_forms', array( $this, 'migrate_forms_page' ), 10, 0 );

		parent::init();

	}





	// # MIGRATION PAGE ------------------------------------------------------------------------------------------------

	/**
	 * Add "Migrate Ninja Forms" tab to Migrate/Export page.
	 *
	 * Callback from gform_export_menu filer, defined within init()
	 *
	 * @since  0.1
	 * @see    $this->init()
	 * @access public
	 *
	 * @param array $tabs Tabs from the Migrate/Export page.
	 *
	 * @return array $tabs The tab listing, with our additional tab
	 */
	public function add_migrate_tab( $tabs ) {

		// Add tab for Migrate Ninja Forms page.
		$tabs['99'] = array(
			'name'  => 'migrate_ninja_forms',
			'label' => esc_html__( 'Migrate Ninja Forms', 'migrate-ninja-forms-to-gravity-forms' ),
		);

		return $tabs;

	}

	/**
	 * Renders "Migrate Ninja Forms" tab content
	 *
	 * Fired from action call in init()
	 *
	 * @since  0.1
	 * @see    $this->init()
	 * @access public
	 */
	public function migrate_forms_page() {

		$html = '';

		// Handle migration form submission.
		$this->maybe_migrate_forms();

		// Get all forms from Ninja Forms.
		$forms = gf_migrate_ninjaforms_api()->get_forms();

		// Display page header.
		$page_title = esc_html__( 'Migrate Ninja Forms', 'migrate-ninja-forms-to-gravity-forms' );
		GFExport::page_header( $page_title );

		// Add instructions.
		$html .= '<p>' . esc_html__( 'Select the Ninja Forms forms you would like to migrate. When you click the Migrate button below, Gravity Forms will migrate the forms and their submissions.', 'migrate-ninja-forms-to-gravity-forms' ) . '</p>';
		$html .= '<div class="hr-divider"></div>';

		// Start migrate form.
		$html .= '<form id="gform_ninja_forms_migrate" method="post" style="margin-top:10px;">';
		$html .= wp_nonce_field( 'gform_ninja_forms_migrate', 'gform_ninja_forms_migrate_nonce', true, false );

		// Open table.
		$html .= '<table class="form-table">';
		$html .= '<tr valign="top">';
		$html .= '<th scope="row"><label for="migrate_forms">' . esc_html__( 'Select Forms', 'migrate-ninja-forms-to-gravity-forms' ) . '</label></th>';

		// Add forms.
		$html .= '<td><ul>';
		foreach ( $forms as $form_id => $form ) {

			$form_title = $form->get_setting( 'title' ) ? $form->get_setting( 'title' ) : $form->get_setting( 'form_title' );

			$html .= '<li>';
			$html .= '<input type="checkbox" name="ninja_form_id[]" id="gf_form_id_' . esc_attr( $form_id ) . '" value="' . esc_attr( $form_id ) . '" />';
			$html .= '<label for="gf_form_id_' . esc_attr( $form_id ) . '">' . $form_title . '</label>';
			$html .= '</li>';
		}
		$html .= '</td></ul>';

		// Close table.
		$html .= '</tr></table>';

		// Add submit button.
		$html .= '<br /><br /><input type="submit" value="' . esc_attr__( 'Migrate Forms', 'migrate-ninja-forms-to-gravity-forms' ) . '" name="migrate_forms" class="button button-primary" />';

		// Close migrate form.
		$html .= '</form>';

		// Display page contents.
		echo $html;

		// Display page footer.
		GFExport::page_footer();

	}

	/**
	 * Handles the "Migrate Ninja Forms" page form submission.
	 *
	 * Checks the following:
	 *  User has permissions
	 *  Form was submitted
	 *  Nonce exists
	 *  A form was selected for migration
	 *
	 * Then, begins the migration.
	 *
	 * @since  0.1
	 * @access public
	 */
	public function maybe_migrate_forms() {

		// Check user permissions.
		if ( ! GFCommon::current_user_can_any( 'gravityforms_edit_forms' ) ) {
			wp_die( 'You do not have permission to access this page' );
		}

		// If form wasn't submitted, return.
		if ( ! rgpost( 'migrate_forms' ) ) {
			return;
		}

		// Verify nonce.
		check_admin_referer( 'gform_ninja_forms_migrate', 'gform_ninja_forms_migrate_nonce' );

		// If no forms were chosen to be migrated, return.
		$ninja_form_ids = rgpost( 'ninja_form_id' );
		if ( empty( $ninja_form_ids ) ) {
			return;
		}

		// Migrate forms.
		if ( is_a( gf_migrate_ninjaforms_api(), 'GF_Migrate_NF2_API' ) ) {
			$converted_forms = $this->migrate_nf2_forms( $ninja_form_ids );
		} else {
			$converted_forms = $this->migrate_nf3_forms( $ninja_form_ids );
		}

		// Display success message.
		$form_text = count( $converted_forms ) > 1 ? __( 'forms', 'migrate-ninja-forms-to-gravity-forms' ) : __( 'form', 'migrate-ninja-forms-to-gravity-forms' );
		GFCommon::add_message( sprintf( __( "Gravity Forms migrated %d {$form_text} successfully.", 'migrate-ninja-forms-to-gravity-forms' ), count( $converted_forms ) ) );

	}





	// # NINJA FORMS 2 MIGRATION ---------------------------------------------------------------------------------------

	/**
	 * Migrates forms and submissions from Ninja Forms 2 to Gravity Forms.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param array $form_ids The Ninja Forms form IDs being migrated.
	 *
	 * @return array $converted_forms List of new Gravity Forms form IDs.
	 */
	public function migrate_nf2_forms( $form_ids = array() ) {

		// Initialize converted form IDs array.
		$converted_forms = array();

		// If no form IDs were provided, return.
		if ( empty( $form_ids ) ) {
			return $converted_forms;
		}

		// Loop through form IDs.
		foreach ( $form_ids as $ninja_form_id ) {

			// Get form.
			$nf_form = gf_migrate_ninjaforms_api()->get_form( $ninja_form_id );

			// Convert form.
			$gf_form = $this->convert_nf2_form( $nf_form );
			
			// Convert submissions.
			$entries = $this->convert_nf2_submissions( $nf_form, $gf_form );

			// Save entries.
			GFAPI::add_entries( $entries, $gf_form['id'] );

			// Add form ID to converted forms.
			$converted_forms[] = $gf_form['id'];
			
		}

		// Return converted form IDs.
		return $converted_forms;

	}

	/**
	 * Converts a Ninja Forms 2 form to a Gravity Forms form.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param  array $nf_form The Ninja Forms form being converted.
	 *
	 * @return array $form
	 */
	public function convert_nf2_form( $nf_form ) {

		// Create a new Gravity Forms form object.
		$gf_form = array(
			'title'                => $nf_form->get_setting( 'form_title' ), // Form title.
			'requireLogin'         => $nf_form->get_setting( 'logged_in' ), // Require login.
			'labelPlacement'       => 'top_label',
			'description'          => '',
			'descriptionPlacement' => 'below',
			'fields'               => array(),
			'confirmations'        => array(),
			'notifications'        => array(),
		);

		// Prepare fields.
		foreach ( $nf_form->fields as $nf_field ) {

			// If field is a submit field, push label to button form property.
			if ( '_submit' === $nf_field['type'] ) {

				$gf_form['button'] = array(
					'type' => 'text',
					'text' => $nf_field['data']['label'],
				);

				continue;

			}

			// Convert field.
			$gf_field = GF_Migrate_NF2_Field::convert_field( $nf_field );

			// If field could not be converted, skip it.
			if ( empty( $gf_field ) ) {
				continue;
			}

			// Add to fields array.
			$gf_form['fields'][] = $gf_field;

		}

		// Convert field objects.
		$gf_form = GFFormsModel::convert_field_objects( $gf_form );

		// Save form.
		$gf_form['id'] = GFAPI::add_form( $gf_form ); 

		// Prepare notifications.
		foreach ( $nf_form->notifications as $nf_notification ) {
			$gf_form = $this->convert_nf2_notification( $gf_form, $nf_notification );
		}

		// If no confirmations exist, add the default notification.
		if ( empty( $form['confirmations'] ) ) {

			// Generate confirmation ID.
			$confirmation_id = uniqid();

			// Add confirmation.
			$gf_form['confirmations'][ $confirmation_id ] = array(
				'id'          => $confirmation_id,
				'name'        => __( 'Default Confirmation', 'gravityforms' ),
				'isDefault'   => true,
				'type'        => 'message',
				'message'     => __( 'Thanks for contacting us! We will get in touch with you shortly.', 'gravityforms' ),
				'url'         => '',
				'pageId'      => '',
				'queryString' => '',
			);

		}
		
		// Update form.
		GFAPI::update_form( $gf_form );

		return $gf_form;

	}

	/**
	 * Convert a Ninja Forms 2 notification to a Gravity Forms notification/confirmation.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param array $gf_form         The new Gravity Forms form object.
	 * @param array $nf_notification The Ninja Forms notification.
	 *
	 * @return array $form The Gravity Forms form object
	 */
	public function convert_nf2_notification( $gf_form, $nf_notification ) {

		switch ( $nf_notification['type'] ) {

			// Convert to notification.
			case 'email':

				// Create a new notification.
				$notification = array(
					'id'       => uniqid(),
					'isActive' => '1' === $nf_notification['active'] ? true : false,
					'name'     => $nf_notification['name'],
					'message'  => $this->convert_to_merge_tags( $gf_form, $nf_notification['email_message'] ),
					'subject'  => $this->convert_from_backticks( $gf_form, $nf_notification['email_subject'], false ),
					'to'       => $this->convert_from_backticks( $gf_form, $nf_notification['to'] ),
					'toType'   => 'email',
					'from'     => $this->convert_from_backticks( $gf_form, $nf_notification['from_address'] ),
					'fromName' => $this->convert_from_backticks( $gf_form, $nf_notification['from_name'] ),
					'replyTo'  => $this->convert_from_backticks( $gf_form, $nf_notification['reply_to'] ),
					'bcc'      => $this->convert_from_backticks( $gf_form, $nf_notification['bcc'] ),
				);

				// Add notification to form.
				$gf_form['notifications'][ $notification['id'] ] = $notification;

				break;

			// Convert to confirmation.
			case 'redirect':

				// Create a new confirmation.
				$confirmation = array(
					'id'       => uniqid(),
					'isActive' => '1' === $nf_notification['active'] ? true : false,
					'name'     => $nf_notification['name'],
					'type'     => 'redirect',
					'url'      => $nf_notification['redirect_url'],
				);

				// Add confirmation to form.
				$gf_form['confirmations'][ $confirmation['id'] ] = $confirmation;

				break;

			// Convert to confirmation.
			case 'success_message':

				// Create a new confirmation.
				$confirmation = array(
					'id'       => uniqid(),
					'isActive' => '1' === $nf_notification['active'] ? true : false,
					'name'     => $nf_notification['name'],
					'type'     => 'message',
					'message'  => $this->convert_to_merge_tags( $form, $nf_notification['success_msg'] ),
				);

				// Add confirmation to form.
				$gf_form['confirmations'][ $confirmation['id'] ] = $confirmation;

				break;

		}

		return $gf_form;

	}

	/**
	 * Convert Ninja Forms 2 submissions to Gravity Forms entries.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param array $nf_form The Ninja Forms form being converted.
	 * @param array $gf_form The new Gravity Forms form object.
	 *
	 * @return array $entries Containing multiple Gravity Forms entry objects
	 */
	public function convert_nf2_submissions( $nf_form, $gf_form ) {

		// Create array to story entries.
		$entries = array();

		// Get submissions.
		$submissions = gf_migrate_ninjaforms_api()->get_submissions( $nf_form->form_id );

		// Add needed information to submissions and push to entries array.
		if ( ! empty( $submissions ) ) {

			foreach ( $submissions as $entry ) {

				// Add missing information.
				$entry['form_id']    = $gf_form['id'];
				$entry['is_starred'] = 0;
				$entry['is_read']    = 0;
				$entry['ip']         = null;
				$entry['user_agent'] = esc_html__( 'Ninja Forms Migration', 'migrate-ninja-forms-to-gravity-forms' );

				// Convert any list data.
				foreach ( $nf_form->fields as $field ) {

					// If this is not a list field, skip it.
					if ( '_list' !== rgar( $field, 'type' ) ) {
						continue;
					}

					// Get the entry value.
					$entry_value = rgar( $entry, $field['id'] );

					// If entry value is blank or value isn't serialized, skip it.
					if ( rgblank( $entry_value ) || ( ! rgblank( $entry_value ) && ! is_serialized( $entry_value ) ) ) {
						continue;
					}

					// Unseralize the entry value.
					$entry_value = maybe_unserialize( $entry_value );

					// Remove empty array values.
					$entry_value = array_filter( $entry_value );

					// Implode the entry value.
					$entry_value = implode( ',', $entry_value );

					// Reassign value back to the entry object.
					$entry[ $field['id'] ] = $entry_value;

				}

				// Push to entries array.
				$entries[] = $entry;

			}

		}

		// Return entries.
		return $entries;

	}





	// # NINJA FORMS 3 MIGRATION ---------------------------------------------------------------------------------------

	/**
	 * Migrates forms and submissions from Ninja Forms 2 to Gravity Forms.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param array $form_ids The Ninja Forms form IDs being migrated.
	 *
	 * @return array $converted_forms List of new Gravity Forms form IDs.
	 */
	public function migrate_nf3_forms( $form_ids = array() ) {

		// Initialize converted form IDs array.
		$converted_forms = array();

		// If no form IDs were provided, return.
		if ( empty( $form_ids ) ) {
			return $converted_forms;
		}

		// Loop through form IDs.
		foreach ( $form_ids as $ninja_form_id ) {

			// Get form.
			$nf_form = gf_migrate_ninjaforms_api()->get_form( $ninja_form_id );

			// Convert form.
			$gf_form = $this->convert_nf3_form( $nf_form );
			
			// Convert submissions.
			$entries = $this->convert_nf3_submissions( $nf_form, $gf_form );

			// Save entries.
			GFAPI::add_entries( $entries, $gf_form['id'] );

			// Add form ID to converted forms.
			$converted_forms[] = $gf_form['id'];
			
		}

		// Return converted form IDs.
		return $converted_forms;

	}

	/**
	 * Converts a Ninja Forms 3 form to a Gravity Forms form.
	 *
	 * @since  0.2
	 * @access public
	 *
	 * @param array $nf_form The Ninja Forms form being converted.
	 *
	 * @return array $form
	 */
	public function convert_nf3_form( $nf_form ) {

	echo '<pre>';
	
	//	var_dump( $nf_form->get_settings() );
		
		// Create a new Gravity Forms form object.
		$gf_form = array(
			'title'                => $nf_form->get_setting( 'title' ),
			'requireLogin'         => $nf_form->get_setting( 'logged_in' ),
			'requireLoginMessage'  => $nf_form->get_setting( 'not_logged_in_msg' ),
			'description'          => '',
			'descriptionPlacement' => 'below',
			'labelPlacement'       => 'top_label',
			'limitEntries'         => $nf_form->get_setting( 'sub_limit_number' ) ? true : false,
			'limitEntriesCount'    => $nf_form->get_setting( 'sub_limit_number' ),
			'limitEntriesMessage'  => $nf_form->get_setting( 'sub_limit_msg' ),
			'cssClass'             => $nf_form->get_setting( 'wrapper_class' ),
			'fields'               => array(),
			'confirmations'        => array(),
			'notifications'        => array(),
		);
		
		// Modify label placement.
		if ( in_array( $nf_form->get_setting( 'default_label_pos' ), array( 'left', 'right' ) ) ) {
			$gf_form['labelPlacement'] = $nf_form->get_setting( 'default_label_pos' ) . '_label';
		}
	
		// Get form fields.
		$nf_fields = Ninja_Forms()->form( $nf_form->get_id() )->get_fields();
	
		// Prepare fields.
		foreach ( $nf_fields as $nf_field ) {

			// If field is a submit field, push label to button form property.
			if ( 'submit' === $nf_field->get_setting( 'type' ) ) {

				$gf_form['button'] = array(
					'type' => 'text',
					'text' => $nf_field->get_setting( 'label' ),
				);

				continue;

			}

			// Convert field.
			$gf_field = GF_Migrate_NF3_Field::convert_field( $nf_field );

			// If field could not be converted, skip it.
			if ( empty( $gf_field ) ) {
				continue;
			}

			// Add to fields array.
			$gf_form['fields'][] = $gf_field;

		}

		// Convert field objects.
		$gf_form = GFFormsModel::convert_field_objects( $gf_form );

	var_dump( $gf_form );
	die();

/*

		// Save form.
		$gf_form['id'] = GFAPI::add_form( $gf_form ); 

		// Prepare notifications.
		foreach ( $nf_form->notifications as $nf_notification ) {
			$gf_form = $this->convert_nf2_notification( $gf_form, $nf_notification );
		}

		// If no confirmations exist, add the default notification.
		if ( empty( $form['confirmations'] ) ) {

			// Generate confirmation ID.
			$confirmation_id = uniqid();

			// Add confirmation.
			$gf_form['confirmations'][ $confirmation_id ] = array(
				'id'          => $confirmation_id,
				'name'        => __( 'Default Confirmation', 'gravityforms' ),
				'isDefault'   => true,
				'type'        => 'message',
				'message'     => __( 'Thanks for contacting us! We will get in touch with you shortly.', 'gravityforms' ),
				'url'         => '',
				'pageId'      => '',
				'queryString' => '',
			);

		}
		
		// Update form.
		GFAPI::update_form( $gf_form );
*/
	
	}

	/**
	 * Convert a Ninja Forms 3 notification to a Gravity Forms notification/confirmation.
	 *
	 * @since  0.2
	 * @access public
	 *
	 * @param array $gf_form         The new Gravity Forms form object.
	 * @param array $nf_notification The Ninja Forms notification.
	 *
	 * @return array $form The Gravity Forms form object
	 */
	public function convert_nf3_notification( $gf_form, $nf_notification ) {}

	/**
	 * Convert Ninja Forms 3 submissions to Gravity Forms entries.
	 *
	 * @since  0.2
	 * @access public
	 *
	 * @param array $nf_form The Ninja Forms form being converted.
	 * @param array $gf_form The new Gravity Forms form object.
	 *
	 * @return array $entries Containing multiple Gravity Forms entry objects
	 */
	public function convert_nf3_submissions( $nf_form, $gf_form ) {}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Converts any Ninja Forms shortcodes in a string to Gravity Forms merge tags.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param array  $gf_form The Gravity Forms form object.
	 * @param string $text    The Ninja Forms merge tag. (default: '')
	 *
	 * @uses GFFormsModel::get_field()
	 *
	 * @return string
	 */
	public function convert_to_merge_tags( $gf_form, $text = '' ) {

		// If no text was provided, return it.
		if ( rgblank( $text ) ) {
			return $text;
		}

		// Convert all fields shortcode.
		$text = str_replace( '[ninja_forms_all_fields]', '{all_fields}', $text );

		// Search for other Ninja Forms shortcodes.
		preg_match_all( '/(\\[ninja_forms_field id=([0-9].*)\\])/mi', $text, $matches );

		// Loop through each shortcode match and convert to merge tags.
		foreach ( $matches[0] as $i => $shortcode ) {

			// Get the field id.
			$field_id = $matches[2][ $i ];

			// Get field.
			$field = GFFormsModel::get_field( $gf_form, $field_id );

			// Make sure the field exists.
			if ( ! $field ) {
				continue;
			}

			// Prepare merge tag.
			$merge_tag = '{' . $field->label . ':' . $field_id . '}';

			// Replace shortcode.
			$text = str_replace( $shortcode, $merge_tag, $text );

		}

		return $text;

	}

	/**
	 * Convert backticks separated list to a comma separated list.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param array  $gf_form The Gravity Forms form object.
	 * @param string $text    The string to convert.
	 * @param bool   $csv     Convert to CSV. (default: true)
	 *
	 * @uses GFFormsModel::get_field()
	 *
	 * @return string
	 */
	public function convert_from_backticks( $gf_form, $text = '', $csv = true ) {

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

			// Get field.
			$field = GFFormsModel::get_field( $gf_form, $field_id );

			// Make sure the field exists.
			if ( ! $field ) {
				continue;
			}

			// Replace part with merge tag.
			$part = '{' . $field->label . ':' . $field_id . '}';

		}

		// Implode it.
		$text = implode( ( $csv ? ',' : ' ' ), $exploded );

		return $text;

	}

}
