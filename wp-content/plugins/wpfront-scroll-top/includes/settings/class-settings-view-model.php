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
 * Settings view model class.
 *
 * @package wpfront-scroll-top
 */
class Settings_View_Model {

	/**
	 * WP wrapper instance.
	 *
	 * @var WP_Wrapper
	 */
	private $wp;

	/**
	 * Settings view instance.
	 *
	 * @var Settings_View
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
	 * Front view model instance.
	 *
	 * @var Front_View_Model
	 */
	private $front_view_model;

	/**
	 * Constructor.
	 *
	 * @param WPFront_Scroll_Top $st WPFront Scroll Top instance.
	 * @param WP_Wrapper         $wp WordPress wrapper instance.
	 * @param Settings_View      $view Settings view instance.
	 * @param Settings_Entity    $entity Settings entity instance.
	 * @param Front_View_Model   $front_view_model Front view model instance.
	 */
	public function __construct( WPFront_Scroll_Top $st, WP_Wrapper $wp, Settings_View $view, Settings_Entity $entity, Front_View_Model $front_view_model ) {
		$this->st               = $st;
		$this->wp               = $wp;
		$this->view             = $view;
		$this->entity           = $entity;
		$this->front_view_model = $front_view_model;
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
		$this->wp->add_action( 'admin_init', array( $this, 'admin_init' ) );
		$this->wp->add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		$this->wp->add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		$this->wp->add_action( 'wpfront_scroll_top_' . $this->st->get_plugin_basename() . '_activated', array( $this, 'add_redirect_hook' ) );
	}

	/**
	 * Initialize the admin hooks.
	 *
	 * @return void
	 */
	public function admin_init(): void {
		$this->wp->add_action( 'wp_ajax_wpfront_scroll_top_submit_data', array( $this, 'submit_data' ) );
	}

	/**
	 * Register the redirect hook for plugin activation.
	 *
	 * @return void
	 */
	public function add_redirect_hook(): void {
		$this->wp->add_action( 'admin_init', array( $this, 'redirect_to_settings_after_activation' ) );
	}

	/**
	 * Redirect to settings page after plugin activation.
	 *
	 * @return void
	 */
	public function redirect_to_settings_after_activation(): void {
		if ( $this->wp->is_network_admin() || isset( $_GET['activate-multi'] ) || isset( $_GET['activate-selected'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( ! $this->wp->current_user_can( $this->get_capability() ) ) {
			return;
		}

		$url = $this->wp->menu_page_url( $this->get_menu_slug(), false );
		if ( empty( $url ) ) {
			return;
		}

		$this->wp->wp_safe_redirect( $url );
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function admin_menu(): void {
		$page_hook_suffix = $this->wp->add_options_page(
			__( 'WPFront Scroll Top Settings', 'wpfront-scroll-top' ),
			__( 'Scroll Top', 'wpfront-scroll-top' ),
			$this->get_capability(),
			$this->get_menu_slug(),
			array( $this, 'settings_render' )
		);

		$this->wp->add_action( 'admin_print_styles-' . $page_hook_suffix, array( $this, 'enqueue_styles' ) );
		$this->wp->add_action( 'admin_print_scripts-' . $page_hook_suffix, array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue styles for the settings page.
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		$this->wp->wp_enqueue_style( 'dashicons' );
		$this->wp->wp_enqueue_style( 'element-plus', $this->st->get_plugin_url( '/includes/assets/element-plus.min.css' ), array(), '2.2.6' );
		$this->wp->wp_enqueue_style( 'wpfront-scroll-top-settings', $this->st->get_plugin_url( '/includes/assets/settings.css' ), array( 'element-plus' ), $this->st->get_version() );
	}

	/**
	 * Enqueue scripts for the settings page.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$debug = $this->wp->is_script_debug();

		$this->wp->wp_enqueue_media();
		$this->wp->wp_enqueue_script( 'postbox' );
		$this->wp->wp_enqueue_script( 'vuejs', $this->st->get_plugin_url( '/includes/assets/vue.global.prod.js' ), array(), '3.2.37', true );
		$this->wp->wp_enqueue_script( 'element-plus', $this->st->get_plugin_url( '/includes/assets/element-plus.min.js' ), array(), '2.2.6', true );

		$version     = $this->st->get_version();
		$settings_js = $this->st->get_plugin_url( '/includes/assets/wpfront-scroll-top-settings.min.js' );

		if ( $debug ) {
			$version     = (string) $this->wp->time();
			$settings_js = $this->st->get_plugin_url( '/assets/settings.js', __FILE__ );
		}

		$this->wp->wp_enqueue_script( 'wpfront-scroll-top-settings', $settings_js, array( 'jquery', 'postbox', 'vuejs', 'element-plus' ), $version, true );

		$data = array(
			'labels'                => $this->get_labels_data(),
			'help'                  => $this->get_help_data(),
			'templates'             => $this->get_view_templates(),
			'button_style_options'  => $this->get_button_style_options(),
			'button_action_options' => $this->get_button_action_options(),
			'location_options'      => $this->get_location_options(),
			'filter_options'        => $this->get_filter_options(),
			'filter_posts_list'     => $this->get_filter_objects(),
			'icons_list'            => $this->get_icons(),
			'data'                  => $this->get_client_data(),
			'ajax_url'              => $this->wp->admin_url( 'admin-ajax.php' ),
		);

		$this->wp->wp_localize_script(
			'wpfront-scroll-top-settings',
			'wpfront_scroll_top_settings',
			$data
		);
	}

	/**
	 * Add settings link to plugin action links.
	 *
	 * @param array<string,string> $links Plugin action links.
	 * @param string               $file Plugin file path.
	 * @return array<string,string> Modified plugin action links.
	 */
	public function plugin_action_links( $links, $file ): array {
		if ( $this->st->get_plugin_basename() === $file && $this->wp->current_user_can( $this->get_capability() ) ) {
			$settings_link = array(
				'settings' => sprintf(
					'<a href="%s" id="wpfront-scroll-top-settings">%s</a>',
					$this->wp->menu_page_url( $this->get_menu_slug(), false ),
					__( 'Settings', 'wpfront-scroll-top' )
				),
			);
			$links         = array_merge( $settings_link, $links );
		}

		return $links;
	}

	/**
	 * Get the capability required to access the plugin settings.
	 *
	 * @return string
	 */
	protected function get_capability(): string {
		return 'manage_options';
	}

	/**
	 * Get the menu slug for the settings page.
	 *
	 * @return string
	 */
	protected function get_menu_slug(): string {
		return 'wpfront-scroll-top';
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function settings_render(): void {
		$this->view->render();
	}

	/**
	 * Retunrs the client data for the settings page.
	 *
	 * @return array<string,mixed> Client data.
	 */
	public function get_client_data() {
		$settings_data = $this->entity->get();

		$css_file = $this->st->get_custom_css_file_location();
		if ( empty( $css_file ) ) {
			$css_file = '';
		}

		return array(
			'enabled'                          => $settings_data->enabled,
			'javascript_async'                 => $settings_data->javascript_async,
			'scroll_offset'                    => $settings_data->scroll_offset,
			'button_width'                     => $settings_data->button_width,
			'button_height'                    => $settings_data->button_height,
			'button_opacity'                   => $settings_data->button_opacity,
			'button_fade_duration'             => $settings_data->button_fade_duration,
			'scroll_duration'                  => $settings_data->scroll_duration,
			'auto_hide'                        => $settings_data->auto_hide,
			'auto_hide_after'                  => $settings_data->auto_hide_after,
			'hide_small_window'                => $settings_data->hide_small_window,
			'small_window_width'               => $settings_data->small_window_width,
			'hide_wpadmin'                     => $settings_data->hide_wpadmin,
			'hide_iframe'                      => $settings_data->hide_iframe,
			'button_style'                     => $settings_data->button_style,
			'button_action'                    => $settings_data->button_action,
			'button_action_element_selector'   => $settings_data->button_action_element_selector,
			'button_action_container_selector' => $settings_data->button_action_container_selector,
			'button_action_element_offset'     => $settings_data->button_action_element_offset,
			'button_action_page_url'           => $settings_data->button_action_page_url,
			'location'                         => $settings_data->location,
			'margin_x'                         => $settings_data->margin_x,
			'margin_y'                         => $settings_data->margin_y,
			'display_pages'                    => $settings_data->display_pages,
			'include_pages'                    => $settings_data->include_pages,
			'exclude_pages'                    => $settings_data->exclude_pages,
			'image'                            => $settings_data->image,
			'image_alt'                        => $settings_data->image_alt,
			'image_title'                      => $settings_data->image_title,
			'custom_url'                       => $settings_data->custom_url,
			'text_button_text'                 => $settings_data->text_button_text,
			'text_button_text_color'           => $settings_data->text_button_text_color,
			'text_button_background_color'     => $settings_data->text_button_background_color,
			'text_button_hover_color'          => $settings_data->text_button_hover_color,
			'text_button_css'                  => $settings_data->text_button_css,
			'fa_button_class'                  => $settings_data->fa_button_class,
			'fa_button_url'                    => $settings_data->fa_button_url,
			'fa_button_exclude_url'            => $settings_data->fa_button_exclude_url,
			'fa_button_text_color'             => $settings_data->fa_button_text_color,
			'fa_button_css'                    => $settings_data->fa_button_css,
			'accessibility_aria_label'         => $settings_data->accessibility_aria_label,
			'accessibility_title'              => $settings_data->accessibility_title,
			'accessibility_screen_reader_text' => $settings_data->accessibility_screen_reader_text,
			'css_enqueue_file'                 => $settings_data->css_enqueue_file,
			'css_extra_css'                    => $settings_data->css_extra_css,
			'css_file_location'                => $css_file,
			'__nonce'                          => $this->wp->wp_create_nonce( 'wpfront_scroll_top_submit_data' ),
		);
	}

	/**
	 * Handle the AJAX request to submit data.
	 *
	 * @param string $stream Stream URL.
	 * @return void
	 */
	public function submit_data( $stream ): void {
		if ( ! $this->wp->current_user_can( $this->get_capability() ) ) {
			$this->wp->wp_send_json_error( __( 'You do not have permission to access this page.', 'wpfront-scroll-top' ) );
		}

		$json_data = $this->get_stream_data( $stream );

		if ( empty( $json_data ) ) {
			$this->wp->wp_send_json_error( __( 'No data received from input stream.', 'wpfront-scroll-top' ) );
		}

		$data = json_decode( $json_data, true );

		if ( ! is_array( $data ) ) {
			$this->wp->wp_send_json_error( __( 'Invalid data received.', 'wpfront-scroll-top' ) );
		}

		$result = ! empty( $data['__nonce'] ) && $this->wp->wp_verify_nonce( is_scalar( $data['__nonce'] ) ? (string) $data['__nonce'] : '', 'wpfront_scroll_top_submit_data' );

		if ( ! $result ) {
			$this->wp->wp_send_json_error( __( 'Invalid nonce.', 'wpfront-scroll-top' ) );
		}

		unset( $data['css_file_location'] );
		unset( $data['__nonce'] );

		$data = $this->sanitize_value( $data );

		$entity = $this->entity->get();
		$entity->set_data( $data );

		if ( $entity->css_enqueue_file ) {
			$css = $this->front_view_model->get_button_css( $entity );
			if ( empty( $css ) ) {
				$this->wp->wp_send_json_error( __( 'Invalid CSS data.', 'wpfront-scroll-top' ) );
			}

			$location = $this->st->get_custom_css_file_location();
			if ( empty( $location ) ) {
				$this->wp->wp_send_json_error( __( 'Unable to create directory in uploads.', 'wpfront-scroll-top' ) );
			}

			$result = $this->wp->wp_mkdir_p( dirname( $location ) );
			if ( empty( $result ) ) {
				$this->wp->wp_send_json_error( __( 'Unable to create directory for CSS file.', 'wpfront-scroll-top' ) );
			}

			$filesystem = $this->wp->wp_filesystem();
			if ( empty( $filesystem ) ) {
				$this->wp->wp_send_json_error( __( 'Unable to initialize filesystem.', 'wpfront-scroll-top' ) );
			}

			$result = $filesystem->put_contents( $location, $css );
			if ( empty( $result ) ) {
				/* translators: %s: file path */
				$this->wp->wp_send_json_error( sprintf( __( 'Unable to write to the CSS file "%s".', 'wpfront-scroll-top' ), $location ) );
			}
		}

		$entity->save();
		$this->clear_cache();

		$this->wp->wp_send_json_success( $this->get_client_data() );
	}

	/**
	 * Get the stream data.
	 *
	 * @param string $stream Stream URL.
	 * @return string Stream data.
	 */
	protected function get_stream_data( $stream ) {
		if ( empty( $stream ) ) {
			$stream = 'php://input';
		}

		$data = file_get_contents( $stream ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( empty( $data ) ) {
			return '';
		}

		return $data;
	}

	/**
	 * Get labels data for the settings page.
	 *
	 * @return array<string,string> Labels data.
	 */
	public function get_labels_data() {
		return array(
			'enabled'                           => __( 'Enabled', 'wpfront-scroll-top' ),
			'javascript_async'                  => __( 'Javascript Async', 'wpfront-scroll-top' ),
			'scroll_offset'                     => __( 'Scroll Offset', 'wpfront-scroll-top' ),
			'button_size'                       => __( 'Button Size', 'wpfront-scroll-top' ),
			'button_opacity'                    => __( 'Button Opacity', 'wpfront-scroll-top' ),
			'button_fade_duration'              => __( 'Button Fade Duration', 'wpfront-scroll-top' ),
			'scroll_duration'                   => __( 'Scroll Duration', 'wpfront-scroll-top' ),
			'auto_hide'                         => __( 'Auto Hide', 'wpfront-scroll-top' ),
			'auto_hide_after'                   => __( 'Auto Hide After', 'wpfront-scroll-top' ),
			'hide_small_window'                 => __( 'Hide on Small Window', 'wpfront-scroll-top' ),
			'small_window_width'                => __( 'Small Window Max Width', 'wpfront-scroll-top' ),
			'hide_wpadmin'                      => __( 'Hide on WP-ADMIN', 'wpfront-scroll-top' ),
			'hide_iframe'                       => __( 'Hide on iframes', 'wpfront-scroll-top' ),
			'button_style'                      => __( 'Button Style', 'wpfront-scroll-top' ),
			'button_action'                     => __( 'Button Action', 'wpfront-scroll-top' ),
			'location'                          => __( 'Location', 'wpfront-scroll-top' ),
			'margin_x'                          => __( 'Margin X', 'wpfront-scroll-top' ),
			'margin_y'                          => __( 'Margin Y', 'wpfront-scroll-top' ),
			'custom_url'                        => __( 'Custom URL', 'wpfront-scroll-top' ),
			'image_alt'                         => __( 'Image ALT', 'wpfront-scroll-top' ),
			'image_title'                       => __( 'Image Title', 'wpfront-scroll-top' ),
			'text_button_text'                  => __( 'Text', 'wpfront-scroll-top' ),
			'text_button_text_color'            => __( 'Text Color', 'wpfront-scroll-top' ),
			'text_button_background_color'      => __( 'Background Color', 'wpfront-scroll-top' ),
			'text_button_hover_color'           => __( 'Mouse Over Color', 'wpfront-scroll-top' ),
			'text_button_css'                   => __( 'Custom CSS', 'wpfront-scroll-top' ),
			'fa_button_class'                   => __( 'Icon Class', 'wpfront-scroll-top' ),
			'fa_button_url'                     => __( 'Font Awesome URL', 'wpfront-scroll-top' ),
			'fa_button_exclude_url'             => __( 'Do not include URL', 'wpfront-scroll-top' ),
			'fa_button_text_color'              => __( 'Icon Color', 'wpfront-scroll-top' ),
			'fa_button_css'                     => __( 'Custom CSS', 'wpfront-scroll-top' ),
			'button_action_element_selector'    => __( 'Element CSS Selector', 'wpfront-scroll-top' ),
			'button_action_container_selector'  => __( 'Scroll Container CSS Selector', 'wpfront-scroll-top' ),
			'button_action_element_offset'      => __( 'Offset', 'wpfront-scroll-top' ),
			'button_action_element_how_to_link' => __( 'How to find CSS selector?', 'wpfront-scroll-top' ),
			'button_action_page_url'            => __( 'Page URL', 'wpfront-scroll-top' ),
			'display_pages'                     => __( 'Display on Pages', 'wpfront-scroll-top' ),
			'media_library_button'              => __( 'Media Library', 'wpfront-scroll-top' ),
			'media_library_title'               => __( 'Choose Image', 'wpfront-scroll-top' ),
			'media_library_text'                => __( 'Select Image', 'wpfront-scroll-top' ),
			'accessibility_aria_label'          => __( 'ARIA Label', 'wpfront-scroll-top' ),
			'accessibility_title'               => __( 'Title', 'wpfront-scroll-top' ),
			'accessibility_screen_reader_text'  => __( 'Screen Reader Text', 'wpfront-scroll-top' ),
			'css_enqueue_file'                  => __( 'Enqueue Using File', 'wpfront-scroll-top' ),
			'css_file_location'                 => __( 'File Location', 'wpfront-scroll-top' ),
			'css_extra_css'                     => __( 'Extra CSS', 'wpfront-scroll-top' ),
		);
	}

	/**
	 * Get help data for the settings page.
	 *
	 * @return array<string,string> Help data.
	 */
	public function get_help_data() {
		return array(
			'enabled'                           => __( 'Enables the scroll top button.', 'wpfront-scroll-top' ),
			'javascript_async'                  => __( 'Increases site performance. Keep it enabled, if there are no conflicts.', 'wpfront-scroll-top' ),
			'scroll_offset'                     => __( 'Number of pixels to be scrolled before the button appears.', 'wpfront-scroll-top' ),
			'button_size'                       => __( 'Set 0px to auto fit, does not work for font awesome button.', 'wpfront-scroll-top' ),
			'button_opacity'                    => __( 'Set transparency of the button.', 'wpfront-scroll-top' ),
			'button_fade_duration'              => __( 'Button fade duration in milliseconds.', 'wpfront-scroll-top' ),
			'scroll_duration'                   => __( 'Window scroll duration in milliseconds.', 'wpfront-scroll-top' ),
			'auto_hide'                         => __( 'Enable to hide the button automatically.', 'wpfront-scroll-top' ),
			'auto_hide_after'                   => __( 'Button will be auto hidden after this duration in seconds, if enabled.', 'wpfront-scroll-top' ),
			'hide_small_window'                 => __( 'Button will be hidden on broswer window when the width matches.', 'wpfront-scroll-top' ),
			'small_window_width'                => __( 'Button will be hidden on browser window with lesser or equal width.', 'wpfront-scroll-top' ),
			'hide_wpadmin'                      => __( 'Button will be hidden on \'wp-admin\'.', 'wpfront-scroll-top' ),
			'hide_iframe'                       => __( 'Button will be hidden on iframes, usually inside popups.', 'wpfront-scroll-top' ),
			'button_style_options_image'        => __( 'Built in or custom icon as button.', 'wpfront-scroll-top' ),
			'button_style_options_text'         => __( 'Text as button.', 'wpfront-scroll-top' ),
			'button_style_options_font-awesome' => __( 'Font awesome icon as button.', 'wpfront-scroll-top' ),
			'button_action_options_top'         => __( 'Default action on WP-ADMIN pages.', 'wpfront-scroll-top' ),
			'button_action_options_element'     => __( 'Scroll to the element specified by the user.', 'wpfront-scroll-top' ),
			'button_action_options_url'         => __( 'Redirects to the URL.', 'wpfront-scroll-top' ),
			'button_action_page_url'            => __( 'URL of the page, you are trying to redirect to.', 'wpfront-scroll-top' ),
			'location'                          => __( 'Sets the location of the scroll top button. Default is bottom right position.', 'wpfront-scroll-top' ),
			'margin_x'                          => __( 'Negative values allowed.', 'wpfront-scroll-top' ),
			'margin_y'                          => __( 'Negative values allowed.', 'wpfront-scroll-top' ),
			'image_alt'                         => __( 'Alternative information for an image', 'wpfront-scroll-top' ),
			'image_title'                       => __( 'HTML title attribute(displays as a tooltip).', 'wpfront-scroll-top' ),
			'include_in_pages'                  => __( 'Use the textbox below to specify the post IDs as a comma separated list.', 'wpfront-scroll-top' ),
			'exclude_in_pages'                  => __( 'Use the textbox below to specify the post IDs as a comma separated list.', 'wpfront-scroll-top' ),
			'text_button_text'                  => __( 'Text to be displayed.', 'wpfront-scroll-top' ),
			'text_button_text_color'            => __( 'Hex color code.', 'wpfront-scroll-top' ),
			'text_button_background_color'      => __( 'Hex color code.', 'wpfront-scroll-top' ),
			'text_button_hover_color'           => __( 'Hex color code.', 'wpfront-scroll-top' ),
			'text_button_css'                   => __( 'ex:', 'wpfront-scroll-top' ) . ' font-size: 1.5em; padding: 10px;',
			'fa_button_class'                   => __( 'ex:', 'wpfront-scroll-top' ) . ' fa fa-arrow-circle-up fa-5x',
			'fa_button_url'                     => __( 'Leave blank to use BootstrapCDN URL by MaxCDN. Otherwise specify the URL you want to use.', 'wpfront-scroll-top' ),
			'fa_button_text_color'              => __( 'Hex color code.', 'wpfront-scroll-top' ),
			'fa_button_exclude_url'             => __( 'Enable this setting if your site already has Font Awesome. Usually your theme includes it.', 'wpfront-scroll-top' ),
			'fa_button_css'                     => __( 'ex:', 'wpfront-scroll-top' ) . ' #wpfront-scroll-top-container i:hover{ color: #000000; }',
			'button_action_element_selector'    => __( 'CSS selector of the element, you are trying to scroll to. Ex: #myDivID, .myDivClass', 'wpfront-scroll-top' ),
			'button_action_container_selector'  => __( 'CSS selector of the element, which has the scroll bar. "html, body" works in almost all cases.', 'wpfront-scroll-top' ),
			'button_action_element_offset'      => __( 'Negative value allowed. Use this filed to precisely set scroll position. Useful when you have overlapping elements.', 'wpfront-scroll-top' ),
			'accessibility_aria_label'          => __( 'ARIA label for the button.', 'wpfront-scroll-top' ),
			'accessibility_title'               => __( 'Title for the button.', 'wpfront-scroll-top' ),
			'accessibility_screen_reader_text'  => __( 'Text for screen readers.', 'wpfront-scroll-top' ),
			'css_enqueue_file'                  => __( 'Enqueue CSS using file instead of inline CSS.', 'wpfront-scroll-top' ),
			'css_file_location'                 => __( 'Location of the CSS file.', 'wpfront-scroll-top' ),
			'css_extra_css'                     => __( 'Additional CSS to be added.', 'wpfront-scroll-top' ),
		);
	}

	/**
	 * Get the button action options.
	 *
	 * @return array<string,string> Button action options.
	 */
	public function get_button_style_options() {
		return array(
			'image'        => __( 'Image Button', 'wpfront-scroll-top' ),
			'text'         => __( 'Text Button', 'wpfront-scroll-top' ),
			'font-awesome' => __( 'Font Awesome Button', 'wpfront-scroll-top' ),
		);
	}

	/**
	 * Get the button action options.
	 *
	 * @return array<string,string> Button action options.
	 */
	public function get_button_action_options() {
		return array(
			'top'     => __( 'Scroll to Top', 'wpfront-scroll-top' ),
			'element' => __( 'Scroll to Element', 'wpfront-scroll-top' ),
			'url'     => __( 'Link to Page', 'wpfront-scroll-top' ),
		);
	}

	/**
	 * Get the location options.
	 *
	 * @return array<int,string> Location options.
	 */
	public function get_location_options() {
		return array(
			1 => __( 'Bottom Right', 'wpfront-scroll-top' ),
			2 => __( 'Bottom Left', 'wpfront-scroll-top' ),
			3 => __( 'Top Right', 'wpfront-scroll-top' ),
			4 => __( 'Top Left', 'wpfront-scroll-top' ),
		);
	}


	/**
	 * Get the display filter options.
	 *
	 * @return array<int,string> Filter options.
	 */
	public function get_filter_options(): array {
		return array(
			1 => __( 'All pages', 'wpfront-scroll-top' ),
			2 => __( 'Include in pages', 'wpfront-scroll-top' ),
			3 => __( 'Exclude in pages', 'wpfront-scroll-top' ),
		);
	}

	/**
	 * Get the filter objects.
	 *
	 * @return array<int,array<string,string>> Filter objects.
	 */
	public function get_filter_objects() {
		$objects = array();

		$objects[] = array(
			'key'     => 'home',
			'display' => __( '[Home Page]', 'wpfront-scroll-top' ),
		);

		$posts = $this->wp->get_posts(
			array(
				'post_type'   => 'any',
				'post_status' => 'publish',
				'numberposts' => 100,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		foreach ( $posts as $post ) {
			if ( ! is_object( $post ) ) {
				continue;
			}

			$obj = $this->wp->get_post_type_object( $post->post_type );
			if ( empty( $obj ) ) {
				continue;
			}

			$label = empty( $obj->labels->singular_name ) ? $obj->label : $obj->labels->singular_name;
			$label = is_scalar( $label ) ? (string) $label : '';
			$label = "[$label]" . ' ' . $post->post_title;

			$objects[] = array(
				'key'     => (string) $post->ID,
				'display' => $label,
			);
		}

		return $objects;
	}

	/**
	 * Get the icons.
	 *
	 * @return array<string,string> Icons.
	 */
	public function get_icons() {
		$files = array();
		$dir   = dirname( __DIR__ ) . '/assets/icons';
		$icons = scandir( $dir );

		if ( ! is_array( $icons ) ) {
			return $files;
		}

		$url = $this->st->get_plugin_url( '/includes/assets/icons/' );
		foreach ( $icons as $icon ) {
			if ( pathinfo( $icon, PATHINFO_EXTENSION ) !== 'png' ) {
				continue;
			}

			$src            = $url . $icon;
			$files[ $icon ] = $src;
		}

		return $files;
	}

	/**
	 * Returns the templates for the settings view.
	 *
	 * @return array<string,string> Templates for the settings view.
	 */
	public function get_view_templates() {
		$templates = array();

		$wp_filesystem = $this->wp->wp_filesystem();

		if ( empty( $wp_filesystem ) ) {
			return $templates;
		}

		$templates['help-icon']              = $wp_filesystem->get_contents( __DIR__ . '/html/help-icon.html' );
		$templates['posts-filter-selection'] = $wp_filesystem->get_contents( __DIR__ . '/html/posts-filter-selection.html' );
		$templates['color-picker']           = $wp_filesystem->get_contents( __DIR__ . '/html/color-picker.html' );

		return $templates;  // @phpstan-ignore return.type
	}

	/**
	 * Sanitize the value.
	 *
	 * @param array<mixed,mixed> $values Value to sanitize.
	 * @return array<mixed,mixed> Sanitized value.
	 */
	public function sanitize_value( $values ) {
		array_walk(
			$values,
			function ( &$value, $key ) {
				if ( is_string( $value ) ) {
					if ( 'text_button_css' === $key || 'fa_button_css' === $key || 'css_extra_css' === $key ) {
						$value = $this->wp->sanitize_textarea_field( $value );
						$value = $this->wp->sanitize_css( $value );
					} else {
						$value = sanitize_text_field( $value );
					}
				}
			}
		);

		return $values;
	}

	/**
	 * Clear the cache for various caching plugins.
	 *
	 * @return void
	 */
	protected function clear_cache() {
		wp_cache_flush();

		if ( function_exists( 'rocket_clean_domain' ) ) {
			/**
			 * WP Rocket
			 *
			 * @disregard
			 */
			\rocket_clean_domain();
		}

		if ( class_exists( 'LiteSpeed\Purge' ) && method_exists( 'LiteSpeed\Purge', 'purge_all' ) ) { // @phpstan-ignore function.impossibleType
			/**
			 * LiteSpeed Cache
			 *
			 * @disregard
			 */
			\LiteSpeed\Purge::purge_all();
		}

		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			/**
			 * WP Super Cache
			 *
			 * @disregard
			*/
			\wp_cache_clear_cache();
		}

		if ( function_exists( 'w3tc_flush_all' ) ) {
			/**
			 * W3 Total Cache
			 *
			 * @disregard
			*/
			\w3tc_flush_all();
		}

		if ( class_exists( 'WpFastestCache' ) ) {
			/**
			 * WP Fastest Cache
			 *
			 * @disregard
			*/
			$wp_fastest_cache = new \WpFastestCache();
			if ( method_exists( $wp_fastest_cache, 'clearCache' ) ) {
				$wp_fastest_cache->clearCache();
			}
		}

		if ( class_exists( 'Cache_Enabler' ) && method_exists( 'Cache_Enabler', 'clear_total_cache' ) ) { // @phpstan-ignore function.impossibleType
			/**
			 * Cache Enabler
			 *
			 * @disregard
			*/
			\Cache_Enabler::clear_total_cache();
		}

		if ( class_exists( 'Hummingbird\Core\Utils' ) && method_exists( 'Hummingbird\Core\Utils', 'flush_cache' ) ) { // @phpstan-ignore function.impossibleType
			/**
			 * Hummingbird Cache
			 *
			 * @disregard
			*/
			\Hummingbird\Core\Utils::flush_cache( true );
		}

		if ( class_exists( 'SiteGround_Optimizer\Helper\Helper' ) && method_exists( 'SiteGround_Optimizer\Helper\Helper', 'purge_cache' ) ) { // @phpstan-ignore function.impossibleType
			/**
			 * SiteGround Optimizer
			 *
			 * @disregard
			*/
			\SiteGround_Optimizer\Helper\Helper::purge_cache();
		}

		if ( class_exists( 'Breeze_PurgeCache' ) && method_exists( 'Breeze_PurgeCache', 'breeze_purge_cache' ) ) { // @phpstan-ignore function.impossibleType
			/**
			 * Breeze Cache
			 *
			 * @disregard
			*/
			\Breeze_PurgeCache::breeze_purge_cache();
		}

		if ( class_exists( 'FlyingPress\FlyingPress' ) && method_exists( 'FlyingPress\FlyingPress', 'clear_cache' ) ) { // @phpstan-ignore function.impossibleType
			/**
			 * FlyingPress Cache
			 *
			 * @disregard
			*/
			\FlyingPress\FlyingPress::clear_cache();
		}
	}
}
