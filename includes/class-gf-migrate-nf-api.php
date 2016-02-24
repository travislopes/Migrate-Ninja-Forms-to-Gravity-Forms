<?php

class GF_Migrate_NF_API {

	public static $objects_table       = 'nf_objects';
	public static $objects_meta_table  = 'nf_objectmeta';
	public static $relationships_table = 'nf_relationships';
	public static $form_fields_table   = 'ninja_forms_fields';

	/**
	 * Get all Ninja Forms.
	 *
	 * @access public
	 * @static
	 * @param int|array $form_ids (default: null)
	 * @return array $forms
	 */
	public static function get_forms( $form_ids = null ) {

		global $wpdb;

		// Prepare return array.
		$forms = array();

		// If form IDs are defined, prepare them for use.
		if ( ! rgblank( $form_ids ) ) {

			if ( ! is_array( $form_ids ) ) {
				$form_ids = explode( ',', $form_ids );
			}

		} else {

			// Get table name.
			$objects_table = self::$objects_table;

			// Get form IDs.
			$form_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}{$objects_table} WHERE `type` = '%s';", 'form' ) );

		}

		// Get forms.
		if ( ! empty( $form_ids ) ) {

			foreach ( $form_ids as $form_id ) {

				// Get form.
				$form = self::get_form( $form_id );

				// If form is an array, push it to the forms array.
				if ( is_array( $form ) ) {
					$forms[ $form_id ] = $form;
				}

			}

		}

		// Return forms.
		return $forms;

	}

	/**
	 * Get a Ninja Form.
	 *
	 * @access public
	 * @static
	 * @param int $form_id (default: null)
	 * @return array $form
	 */
	public static function get_form( $form_id = null ) {

		global $wpdb;

		// Create the return array.
		$form = array();

		// If no form ID is provided, return.
		if ( rgblank( $form_id ) ) {
			return $form;
		}

		// Get form meta.
		$meta_table = self::$objects_meta_table;
		$form_meta  = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->prefix}{$meta_table} WHERE `object_id` = '%d';", $form_id ) );

		// If form meta is empty, return null.
		if ( empty( $form_meta ) ) {
			return null;
		}

		// Prepare form meta.
		foreach ( $form_meta as $meta ) {
			$form[ $meta->meta_key ] = $meta->meta_value;
		}

		// If form title does not exist, object is not a form. Return null.
		if ( ! isset( $form['form_title'] ) ) {
			return null;
		}

		// Push fields and notifications to form.
		$form['fields']        = self::get_form_fields( $form_id );
		$form['notifications'] = self::get_form_notifications( $form_id );

		// Add form ID.
		$form['id'] = $form_id;

		// Return form.
		return $form;

	}

	/**
	 * Get fields for a Ninja Form.
	 *
	 * @access private
	 * @static
	 * @param int $form_id (default: null)
	 * @return array $fields
	 */
	private static function get_form_fields( $form_id = null ) {

		global $wpdb;

		// Create the return array.
		$fields = array();

		// If no form ID is provided, return.
		if ( rgblank( $form_id ) ) {
			return $fields;
		}

		// Get table name.
		$form_fields_table = self::$form_fields_table;

		// Get form fields
		$_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$form_fields_table} WHERE `form_id` = '%d' ORDER BY `order`, `id` DESC;", $form_id ), ARRAY_A );

		// Prepare form fields and add them to the return array.
		if ( ! empty( $_fields ) ) {

			foreach ( $_fields as $field ) {

				// Convert field data to array.
				$field_data = maybe_unserialize( $field['data'] );

				// Merge field data into field object.
				$field = array_merge( $field, $field_data );

				// Remove field data from field object.
				unset( $field['data'] );

				// Push to fields array.
				$fields[] = $field;

			}

		}

		// Return fields.
		return $fields;

	}

	/**
	 * Get notifications for a Ninja Form.
	 *
	 * @access private
	 * @static
	 * @param int $form_id (default: null)
	 * @return array $notifications
	 */
	private static function get_form_notifications( $form_id = null ) {

		global $wpdb;

		// Create the return array.
		$notifications = array();

		// If no form ID is provided, return.
		if ( rgblank( $form_id ) ) {
			return $notifications;
		}

		// Get needed table names.
		$meta_table          = self::$objects_meta_table;
		$relationships_table = self::$relationships_table;

		// Get notification IDs.
		$notification_ids = $wpdb->get_col( $wpdb->prepare( "SELECT child_id FROM {$wpdb->prefix}{$relationships_table} WHERE `parent_id` = '%d' AND `child_type` = '%s' AND `parent_type` = '%s';", $form_id, 'notification', 'form' ) );

		// Get notifications.
		foreach ( $notification_ids as $notification_id ) {

			$notification = array();

			// Get notification meta.
			$notification_meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->prefix}{$meta_table} WHERE `object_id` = '%d';", $notification_id ) );

			// Prepare notification meta.
			foreach ( $notification_meta as $meta ) {
				$notification[ $meta->meta_key ] = $meta->meta_value;
			}

			// Push to notifications array.
			$notifications[] = $notification;

		}

		// Return notifications.
		return $notifications;

	}

	/**
	 * Get submissions for a Ninja Form.
	 *
	 * @access public
	 * @static
	 * @param int $form_id (default: null)
	 * @return array $submissions
	 */
	public static function get_submissions( $form_id = null ) {

		// Create the return array.
		$submissions = array();

		// If no form ID is provided, return.
		if ( rgblank( $form_id ) ) {
			return $submissions;
		}

		// Get submission posts.
		$_submissions = new WP_Query( array(
			'meta_key'   => '_form_id',
			'meta_value' => absint( $form_id ),
			'nopaging'   => true,
			'post_type'  => 'nf_sub',
		) );

		// Get submission objects and push to submissions array.
		if ( ! empty( $_submissions->posts ) ) {

			foreach ( $_submissions->posts as $_submission ) {

				// Prepare submission object.
				$submission = array(
					'date_created' => $_submission->post_date,
					'created_by'   => $_submission->post_author,
				);

				// Get the submission data.
				$_submission_meta = get_post_meta( $_submission->ID );

				// Push needed data to submission object.
				foreach ( $_submission_meta as $entry_id => $value ) {

					if ( strpos( $entry_id, '_field_' ) !== 0 ) {
						continue;
					}

					$entry_id                = str_replace( '_field_', '', $entry_id );
					$submission[ $entry_id ] = $value[0];

				}

				$submissions[] = $submission;

			}

		}

		// Return the submissions.
		return $submissions;

	}

}
