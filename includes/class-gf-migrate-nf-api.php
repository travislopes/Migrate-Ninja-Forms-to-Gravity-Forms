<?php
	
class GF_Migrate_NF_API {

	public $objects_table       = 'nf_objects';
	public $objects_meta_table  = 'nf_objectmeta';
	public $relationships_table = 'nf_relationships';	
	public $form_fields_table   = 'ninja_forms_fields';

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