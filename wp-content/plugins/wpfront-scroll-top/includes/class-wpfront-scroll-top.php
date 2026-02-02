<?php
/**
 * WPFront Scroll Top
 *
 * @package     wpfront-scroll-top
 * @author      Syam Mohan
 * @copyright   2013 WPFront
 * @license     GPL-2.0-or-later
 */

namespace WPFront\Scroll_Top;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @package wpfront-scroll-top
 *
 * @test \WPFront_Scroll_Top_Test
 */
class WPFront_Scroll_Top {

	const VERSION = '3.0.1.09211';

	/**
	 * Dependency injection container.
	 *
	 * @var DI
	 */
	protected $di;

	/**
	 * WP wrapper.
	 *
	 * @var WP_Wrapper
	 */
	protected $wp;

	/**
	 * The plugin instance.
	 *
	 * @param DI         $di Dependency injection container.
	 * @param WP_Wrapper $wp WP wrapper.
	 */
	public function __construct( DI $di, WP_Wrapper $wp ) {
		$this->di = $di;
		$this->wp = $wp;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->load_textdomain();
		$this->load_dependencies();
		$this->do_plugin_activated();
	}

	/**
	 * Load plugin text domain for translations.
	 */
	public function load_textdomain(): bool {
		$basename = $this->get_plugin_basename();
		return $this->wp->load_plugin_textdomain(
			'wpfront-scroll-top',
			false,
			dirname( $basename ) . '/languages/'
		);
	}

	/**
	 * Load plugin dependencies.
	 *
	 * @return void
	 */
	public function load_dependencies(): void {
		$this->di->get( Settings_View_Model::class )->init();
		$this->di->get( Front_View_Model::class )->init();
	}

	/**
	 * Fire the action for plugin activation.
	 *
	 * @return void
	 */
	public function do_plugin_activated() {
		$transient_name = $this->get_plugin_basename() . '_activated';
		if ( $this->wp->get_transient( $transient_name ) ) {
			$this->wp->delete_transient( $transient_name );

			$this->wp->do_action( 'wpfront_scroll_top_' . $transient_name );
		}
	}

	/**
	 * Returns the plugin's basename.
	 *
	 * @return string The plugin basename.
	 */
	public function get_plugin_basename(): string {
		$plugin_basename = $this->wp->plugin_basename( __FILE__ );
		if ( empty( $plugin_basename ) ) {
			return '';
		}

		return dirname( $plugin_basename, 2 ) . '/wpfront-scroll-top.php';
	}

	/**
	 * Returns the plugin URL.
	 *
	 * @param string $path Optional. Path to append to the URL. Default empty.
	 * @param string $file Optional. File name to append to the URL. Default empty.
	 * @return string The plugin URL.
	 */
	public function get_plugin_url( $path = '', $file = '' ): string {
		return $this->wp->plugins_url( $path, '' === $file ? __DIR__ : $file );
	}

	/**
	 * Returns the plugin version.
	 *
	 * @return string The plugin version.
	 */
	public function get_version(): string {
		return trim( self::VERSION );
	}

	/**
	 * Returns the location of the custom CSS file.
	 *
	 * @return string|null The custom CSS file location or null if an error occurs.
	 */
	public function get_custom_css_file_location() {
		$dir = wp_upload_dir();

		if ( $dir['error'] ) {
			return null;
		}

		return $dir['basedir'] . '/wpfront-scroll-top/style.css';
	}

	/**
	 * Returns the URL of the custom CSS file.
	 *
	 * @return string|null The custom CSS file URL or null if an error occurs.
	 */
	public function get_custom_css_file_url() {
		$dir = wp_upload_dir();

		if ( $dir['error'] ) {
			return null;
		}

		return $dir['baseurl'] . '/wpfront-scroll-top/style.css';
	}
}

require_once __DIR__ . '/helpers/class-di.php';
require_once __DIR__ . '/helpers/class-wp-wrapper.php';
require_once __DIR__ . '/settings/class-settings-view-model.php';
require_once __DIR__ . '/settings/class-settings-view.php';
require_once __DIR__ . '/entities/class-settings-entity.php';
require_once __DIR__ . '/front/class-front-view-model.php';
require_once __DIR__ . '/front/class-front-view.php';
