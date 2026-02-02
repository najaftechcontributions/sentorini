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
 * Front view model class.
 *
 * @package wpfront-scroll-top
 */
class Front_View_Model {

	/**
	 * WP wrapper instance.
	 *
	 * @var WP_Wrapper
	 */
	private $wp;

	/**
	 * Front view instance.
	 *
	 * @var Front_View
	 */
	private $view;

	/**
	 * WPFront Scroll Top instance.
	 *
	 * @var WPFront_Scroll_Top
	 */
	private $st;

	/**
	 * Settings entity instance.
	 *
	 * @var Settings_Entity
	 */
	private $entity;

	/**
	 * Constructor.
	 *
	 * @param WPFront_Scroll_Top $st WPFront Scroll Top instance.
	 * @param WP_Wrapper         $wp WordPress wrapper instance.
	 * @param Front_View         $view Front view instance.
	 * @param Settings_Entity    $entity Settings entity instance.
	 */
	public function __construct( WPFront_Scroll_Top $st, WP_Wrapper $wp, Front_View $view, Settings_Entity $entity ) {
		$this->st     = $st;
		$this->wp     = $wp;
		$this->view   = $view;
		$this->entity = $entity;
	}

	/**
	 * Initialize the view model.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_hooks();
	}

	/**
	 * Register plugin hooks.
	 *
	 * @return void
	 */
	protected function register_hooks(): void {
		$this->wp->add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scroll_top_scripts' ) );
		$this->wp->add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scroll_top_scripts' ) );
	}

	/**
	 * Enqueue scripts for the scroll top functionality.
	 *
	 * @return void
	 */
	public function enqueue_scroll_top_scripts() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$settings = $this->entity->get();

		if ( 'font-awesome' === $settings->button_style ) {
			if ( ! $settings->fa_button_exclude_url || is_admin() ) {
				$url = 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css';
				$ver = '4.7.0';

				if ( ! empty( $settings->fa_button_url ) ) {
					$url = $settings->fa_button_url;
					$ver = false;
				}

				$this->wp->wp_enqueue_style( 'font-awesome', $url, array(), $ver );
			}
		}

		$src     = $this->st->get_plugin_url( 'includes/assets/wpfront-scroll-top.min.js' );
		$version = $this->st->get_version();

		if ( $this->wp->is_script_debug() ) {
			$src     = $this->st->get_plugin_url( 'assets/wpfront-scroll-top.js', __FILE__ );
			$version = 'v' . $this->wp->time();
		}

		$script_args = array(
			'in_footer' => true,
		);

		if ( $settings->javascript_async ) {
			$script_args['strategy'] = 'defer';
		}

		$this->wp->wp_enqueue_script(
			'wpfront-scroll-top',
			$src,
			array( 'jquery' ),
			$version,
			$script_args
		);

		$view = $this->view;

		if ( is_admin() ) {
			$settings->button_action = 'top';
		}

		if ( $settings->css_enqueue_file ) {
			$css_file = $this->st->get_custom_css_file_location();
			if ( $css_file && $this->wp->file_exists( $css_file ) ) { // TODO: try to create the file if it does not exist.
				$version = $this->wp->filemtime( $css_file );
				$css_url = $this->st->get_custom_css_file_url();

				if ( $version && $css_url ) {
					$this->wp->wp_enqueue_style(
						'wpfront-scroll-top',
						$css_url,
						array(),
						'v' . $version
					);
				} else {
					$settings->css_enqueue_file = false; // Fallback to inline CSS if any error occurs.
				}
			} else {
				$settings->css_enqueue_file = false; // Fallback to inline CSS if the file does not exist.
			}
		}

		$css  = $settings->css_enqueue_file ? null : $this->get_button_css( $settings );
		$html = $view->get_html( $settings );
		$data = array(
			'hide_iframe'                      => $settings->hide_iframe,
			'button_fade_duration'             => $settings->button_fade_duration,
			'auto_hide'                        => $settings->auto_hide,
			'auto_hide_after'                  => $settings->auto_hide_after,
			'scroll_offset'                    => $settings->scroll_offset,
			'button_opacity'                   => $settings->button_opacity / 100,
			'button_action'                    => $settings->button_action,
			'button_action_element_selector'   => $settings->button_action_element_selector,
			'button_action_container_selector' => $settings->button_action_container_selector,
			'button_action_element_offset'     => $settings->button_action_element_offset,
			'scroll_duration'                  => $settings->scroll_duration,
		);

		$data = array(
			'css'  => $css,
			'html' => $html,
			'data' => $data,
		);

		$this->wp->wp_localize_script( 'wpfront-scroll-top', 'wpfront_scroll_top_data', array( 'data' => $data ) );
	}

	/**
	 * Checks if the scroll top button is enabled.
	 *
	 * @return bool True if the scroll top button is enabled, false otherwise.
	 */
	protected function is_enabled(): bool {
		$settings = $this->entity->get();

		$enabled = $settings->enabled && $this->is_enabled_on_current_page();

		/**
		 * Whether the scroll top button is enabled.
		 *
		 * @var bool $enabled
		 */
		$enabled = apply_filters( 'wpfront_scroll_top_enabled', $enabled );

		return $enabled;
	}

	/**
	 * Checks if the scroll top button is enabled on the current page.
	 *
	 * @return bool True if the scroll top button is enabled on the current page, false otherwise.
	 */
	protected function is_enabled_on_current_page(): bool {
		$settings = $this->entity->get();

		if ( is_admin() ) {
			return ! $settings->hide_wpadmin;
		}

		if ( 1 === $settings->display_pages ) {
			return true;
		}

		$id = null;

		if ( is_home() ) {
			$id = 'home';
		}

		$post = get_queried_object();
		if ( $post instanceof \WP_Post ) {
			$id = (string) $post->ID;
		}

		$list = 2 === $settings->display_pages ? $settings->include_pages : $settings->exclude_pages;

		$list = array_map( 'trim', explode( ',', $list ) );

		$exists = in_array( $id, $list, true );

		return 2 === $settings->display_pages ? $exists : ! $exists;
	}

	/**
	 * Gets the CSS for the scroll top button.
	 *
	 * @param Settings_Entity $settings Settings entity instance.
	 *
	 * @return string CSS output.
	 */
	public function get_button_css( $settings ) {
		$css = $this->view->get_css( $settings );
		$css = preg_replace( '/\s*([{}|:;,])\s+/', '$1', $css );

		return $css ?? '';
	}
}
