<?php

namespace PeerRaiser\Helper;

/**
 * PeerRaiser file helper.
 */
class File {

	/**
	 * Handles uploading a file to a WordPress post
	 *
	 * @param  int   $post_id              Post ID to upload the photo to
	 * @param  array $attachment_post_data Attachement post-data array
	 *
	 * @return int The ID of the attachment
	 */
	function attach_image_to_post( $post_id, $attachment_post_data = array() ) {
		// Make sure the right files were submitted
		if (
			empty( $_FILES )
			|| ! isset( $_FILES['submitted_post_thumbnail'] )
			|| isset( $_FILES['submitted_post_thumbnail']['error'] ) && 0 !== $_FILES['submitted_post_thumbnail']['error']
		) {
			return;
		}
		// Filter out empty array values
		$files = array_filter( $_FILES['submitted_post_thumbnail'] );
		// Make sure files were submitted at all
		if ( empty( $files ) ) {
			return;
		}
		// Make sure to include the WordPress media uploader API if it's not (front-end)
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}
		// Upload the file and send back the attachment post ID
		return media_handle_upload( 'submitted_post_thumbnail', $post_id, $attachment_post_data );
	}
}