<?php

interface GF_Migrate_NF_API {

	public function get_forms();

	public function get_form( $form_id );

	public function get_form_notifications( $form_id = null );

	public function get_submissions( $form_id );

}

class GF_Migrate_NF_API_2 implements GF_Migrate_NF_API {

	/**
	 * The instance of this class.  Used to instantiate.
	 *
	 * @since  0.2
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
	 * Get all Ninja Forms.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @return array
	 */
	public function get_forms() {

		// Initialize forms array.
		$forms = array();

		// Get forms.
		$nf_forms = Ninja_Forms()->forms()->get_all();

		// Loop through forms.
		foreach ( $nf_forms as $form_id ) {

			// Add to forms array.
			$forms[ $form_id ] = Ninja_Forms()->form( $form_id );

		}

		return $forms;

	}

	/**
	 * Get a Ninja Form.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param int $form_id Form ID to retrieve.
	 *
	 * @return object
	 */
	public function get_form( $form_id = null ) {

		// Get form.
		$nf_form = Ninja_Forms()->form( $form_id );

		// Get form notifications.
		$nf_form->notifications = $this->get_form_notifications( $form_id );

		return $nf_form;

	}

	/**
	 * Get notifications for a Ninja Form.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param int $form_id Form ID to retrieve notifications for.
	 *
	 * @return array
	 */
	public function get_form_notifications( $form_id = null ) {

		return nf_get_notifications_by_form_id( $form_id );

	}

	/**
	 * Get submissions for a Ninja Form.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param int $form_id Form ID to retrieve submissions for.
	 *
	 * @return array
	 */
	public function get_submissions( $form_id ) {

		// Initialize submissions array.
		$submissions = array();

		// Prepare submissions arguments.
		$args = array( 'form_id' => $form_id );

		// Loop through submissions.
		foreach ( Ninja_Forms()->subs()->get( $args ) as $nf_submission ) {

			// Prepare submission object.
			$submission = array(
				'date_created' => $nf_submission->date_submitted,
				'created_by'   => $nf_submission->user_id,
			);

			// Add fields to submission.
			$submission = array_merge( $submission, $nf_submission->get_all_fields() );

			// Add submission to array.
			$submissions[] = $submission;

		}

		return $submissions;

	}

}

class GF_Migrate_NF_API_3 implements GF_Migrate_NF_API {

	/**
	 * The instance of this class.  Used to instantiate.
	 *
	 * @since  0.2
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
	 * Get all Ninja Forms.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @return array
	 */
	public function get_forms() {

		// Initialize forms array.
		$forms = array();

		// Get forms.
		$nf_forms = Ninja_Forms()->form()->get_forms();

		// If no forms were found, return.
		if ( empty( $nf_forms ) ) {
			return $forms;
		}

		// Loop through forms.
		foreach ( $nf_forms as $form ) {

			// Add to forms array.
			$forms[ $form->get_id() ] = $form;

		}

		return $forms;

	}

	/**
	 * Get a Ninja Form.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param int $form_id Form ID to retrieve.
	 *
	 * @return object
	 */
	public function get_form( $form_id = null ) {
		
		// Get form.
		$nf_form = Ninja_Forms()->form( $form_id );
		
		return $nf_form;
		
	}

	/**
	 * Get notifications for a Ninja Form.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param int $form_id Form ID to retrieve notifications for.
	 *
	 * @return array
	 */
	public function get_form_notifications( $form_id = null ) {}

	/**
	 * Get submissions for a Ninja Form.
	 *
	 * @since  0.1
	 * @access public
	 *
	 * @param int $form_id Form ID to retrieve submissions for.
	 *
	 * @return array
	 */
	public function get_submissions( $form_id ) {}

}

/**
 * Returns an instance of the Ninja Forms Migration API library
 * based on active version of Ninja Forms.
 *
 * @see    GF_Migrate_NF_API_2::get_instance()
 * @see    GF_Migrate_NF_API_3::get_instance()
 *
 * @return object
 */
function gf_migrate_ninjaforms_api() {

	// Get active Ninja Forms version.
	if ( '1' == get_option( 'ninja_forms_load_deprecated' ) ) {
		$nf_version = get_option( 'nf_version_upgrade_from' );
	} else {
		$nf_version = get_option( 'ninja_forms_version' );
	}

	// Return API library based on active version
	if ( version_compare( $nf_version, '3.0', '>' ) ) {
		return GF_Migrate_NF_API_3::get_instance();
	} else {
		return GF_Migrate_NF_API_2::get_instance();
	}

}
