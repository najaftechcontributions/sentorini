<?php
/**
 * Uninstall script for the plugin.
 *
 * @package wpfront-scroll-top
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Remove a directory and all its contents.
 *
 * @param string $dir Directory path to remove.
 * @return bool
 */
function wpfront_scroll_top_remove_directory( $dir ) {
	if ( ! file_exists( $dir ) || ! is_dir( $dir ) ) {
		return false;
	}

	$files = array_diff( scandir( $dir ), array( '.', '..' ) );
	foreach ( $files as $file ) {
		is_dir( "$dir/$file" ) ? wpfront_scroll_top_remove_directory( "$dir/$file" ) : @unlink( "$dir/$file" ); // @phpcs:ignore 
	}

	return @rmdir( $dir ); // @phpcs:ignore 
}

/**
 * Remove the plugin options and files.
 *
 * @return void
 */
function wpfront_scroll_top_remove() {
	delete_option( 'wpfront-scroll-top-options' );

	$upload_dir = wp_upload_dir();

	if ( $upload_dir['error'] ) {
		return;
	}

	$base_dir = $upload_dir['basedir'];

	wpfront_scroll_top_remove_directory( $base_dir . '/wpfront-scroll-top' );
}

/**
 * Uninstall function for the WPFront Scroll Top plugin.
 *
 * @return void
 */
function wpfront_scroll_top_uninstall() {
	if ( is_multisite() ) {
		/**
		 * WPDB
		 *
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // @phpcs:ignore 

		foreach ( $blog_ids as $blog_id ) {
			if ( ! is_scalar( $blog_id ) ) {
				continue;
			}

			$blog_id = (int) $blog_id;

			switch_to_blog( $blog_id );

			wpfront_scroll_top_remove();

			restore_current_blog();
		}
	} else {
		wpfront_scroll_top_remove();
	}
}

wpfront_scroll_top_uninstall();
