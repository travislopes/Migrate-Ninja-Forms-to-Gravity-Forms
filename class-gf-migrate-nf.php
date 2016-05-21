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

	/**
	 * Add "Migrate Ninja Forms" tab to Migrate/Export page.
	 *
	 * Callback from gform_export_menu filer, defined within init()
	 *
	 * @since  0.1
	 * @see    $this->init()
	 * @access public
	 * @param  array $tabs Tabs from the Migrate/Export page.
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
		$forms = GF_Migrate_NF_API::get_forms();

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
			$html .= '<li>';
			$html .= '<input type="checkbox" name="ninja_form_id[]" id="gf_form_id_' . esc_attr( $form_id ) . '" value="' . esc_attr( $form_id ) . '" />';
			$html .= '<label for="gf_form_id_' . esc_attr( $form_id ) . '">' . $form['form_title'] . '</label>';
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
		$converted_forms = $this->migrate_forms( $ninja_form_ids );

		// Display success message.
		$form_text = count( $converted_forms ) > 1 ? __( 'forms', 'migrate-ninja-forms-to-gravity-forms' ) : __( 'form', 'migrate-ninja-forms-to-gravity-forms' );
		GFCommon::add_message( sprintf( __( "Gravity Forms migrated %d {$form_text} successfully.", 'migrate-ninja-forms-to-gravity-forms' ), count( $converted_forms ) ) );

	}

	/**
	 * Migrates forms and submissions from Ninja Forms to Gravity Forms.
	 *
	 * @since  0.1
	 * @access public
	 * @param  array $form_ids The Ninja Forms form IDs being migrated.
	 *
	 * @return array $converted_forms List of new Gravity Forms form IDs.
	 */
	public function migrate_forms( $form_ids = array() ) {

		// Setup array for converted form IDs.
		$converted_forms = array();

		// Get Ninja Forms.
		$ninja_forms = GF_Migrate_NF_API::get_forms( $form_ids );

		// Convert and save each form.
		foreach ( $ninja_forms as $ninja_form_id => $ninja_form ) {

			// Convert form.
			$form = $this->convert_form( $ninja_form );

			// Save form and capture new ID.
			$form_id = GFAPI::add_form( $form );

			// If form could not be created, log and continue.
			if ( is_wp_error( $form_id ) ) {

				// Log error.
				$this->log_error( __METHOD__ . '(): Form #' . $ninja_form_id . ' could not be migrated; ' . $form_id->get_error_message() );

				continue;

			}

			$form['id'] = $form_id;

			// Convert submissions.
			$entries = $this->convert_submissions( $ninja_form, $form );

			// Save entries.
			GFAPI::add_entries( $entries, $form_id );

			// Add form ID to converted forms.
			$converted_forms[] = $form_id;

		}

		// Return converted form IDs.
		return $converted_forms;

	}

	/**
	 * Converts a Ninja Form to a Gravity Form.
	 *
	 * @since  0.1
	 * @access public
	 * @param  array $ninja_form The Ninja Forms form being converted.
	 *
	 * @return array $form The newly created Gravity Forms form object.
	 */
	public function convert_form( $ninja_form ) {

		// Create a new Gravity Forms form object.
		$form = array(
			'title'                => rgar( $ninja_form, 'form_title' ), // Form title.
			'requireLogin'         => rgar( $ninja_form, 'logged_in' ), // Require login.
			'labelPlacement'       => 'top_label',
			'description'          => '',
			'descriptionPlacement' => 'below',
			'fields'               => array(),
			'confirmations'        => array(),
			'notifications'        => array(),
		);

		// Prepare fields.
		foreach ( $ninja_form['fields'] as $_field ) {

			// Convert field.
			$field = GF_Migrate_NF_Field::convert_field( $_field );

			// Save to fields array if converted.
			if ( ! is_null( $field ) ) {
				$form['fields'][] = $field;
			}

			// If field is a submit field, push label to button form property.
			if ( '_submit' === $_field['type'] ) {
				$form['button'] = array(
					'type' => 'text',
					'text' => $_field['label'],
				);
			}

		}

		// Prepare notifications.
		foreach ( $ninja_form['notifications'] as $nf_notification ) {
			$form = $this->convert_notification( $form, $nf_notification );
		}

		// If no confirmations exist, add the default notification.
		if ( empty( $form['confirmations'] ) ) {

			$confirmation_id                           = uniqid();
			$form['confirmations'][ $confirmation_id ] = array(
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

		return $form;

	}

	/**
	 * Convert a Ninja Forms notification to a Gravity Forms notification/confirmation.
	 *
	 * @since  0.1
	 * @access public
	 * @param  array $form            The new Gravity Forms form object.
	 * @param  array $nf_notification The Ninja Forms notification.
	 *
	 * @return array $form The Gravity Forms form object
	 */
	public function convert_notification( $form, $nf_notification ) {

		// If notification type is redirect, convert to confirmation.
		if ( 'redirect' === $nf_notification['type'] ) {

			// Create a new confirmation.
			$confirmation = array(
				'id'       => uniqid(),
				'isActive' => '1' === $nf_notification['active'] ? true : false,
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
				'isActive' => '1' === $nf_notification['active'] ? true : false,
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
				'isActive' => '1' === $nf_notification['active'] ? true : false,
				'name'     => $nf_notification['name'],
				'message'  => $this->convert_to_merge_tags( $form, $nf_notification['email_message'] ),
				'subject'  => $this->convert_from_backticks( $form, $nf_notification['email_subject'], false ),
				'to'       => $this->convert_from_backticks( $form, $nf_notification['to'] ),
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
	 * Convert Ninja Forms submissions to Gravity Forms entries.
	 *
	 * @since  0.1
	 * @access public
	 * @param  array $ninja_form The Ninja Forms form being converted.
	 * @param  array $form       The new Gravity Forms form object.
	 *
	 * @return array $entries Containing multiple Gravity Forms entry objects
	 */
	public function convert_submissions( $ninja_form, $form ) {

		// Create array to story entries.
		$entries = array();

		// Get submissions.
		$submissions = GF_Migrate_NF_API::get_submissions( $ninja_form );

		// Add needed information to submissions and push to entries array.
		if ( ! empty( $submissions ) ) {

			foreach ( $submissions as $entry ) {

				// Add missing information.
				$entry['form_id']    = $form['id'];
				$entry['is_starred'] = 0;
				$entry['is_read']    = 0;
				$entry['ip']         = null;
				$entry['user_agent'] = esc_html__( 'Ninja Forms Migration', 'migrate-ninja-forms-to-gravity-forms' );

				// Convert any list data.
				foreach ( $ninja_form['fields'] as $field ) {

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

	/**
	 * Converts any Ninja Forms shortcodes in a string to Gravity Forms merge tags.
	 *
	 * @since  0.1
	 * @access public
	 * @param  array  $form The Gravity Forms form object.
	 * @param  string $text The Ninja Forms merge tag. (default: '')
	 *
	 * @return string $text The Gravity Forms merge tag
	 */
	public function convert_to_merge_tags( $form, $text = '' ) {

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

			// Make sure the field exists.
			if ( ! isset( $form['fields'][ $field_id ] ) ) {
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
	 * Backticks in lists are bad, and you should feel bad.
	 *
	 * @since  0.1
	 * @access public
	 * @param  array  $form The Gravity Forms form object.
	 * @param  string $text The string to convert.
	 * @param  bool   $csv  Convert to CSV. (default: true)
	 *
	 * @return string $text
	 */
	public function convert_from_backticks( $form, $text = '', $csv = true ) {

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
			if ( ! isset( $form['fields'][ $field_id ] ) ) {
				continue;
			}

			// Replace part with merge tag.
			$part = '{' . $form['fields'][ $field_id ]->label . ':' . $field_id . '}';

		}

		// Implode it.
		$text = implode( ( $csv ? ',' : ' ' ), $exploded );

		return $text;

	}

}
