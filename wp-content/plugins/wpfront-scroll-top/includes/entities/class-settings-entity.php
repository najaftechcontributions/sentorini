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
 * Entity class of WPFront Scroll Top Plugin
 *
 * @package wpfront-scroll-top
 */
class Settings_Entity {


	/**
	 * Scroll Top Enabled.
	 *
	 * @var bool
	 */
	public $enabled = false;

	/**
	 * Scroll Top Javascript Async.
	 *
	 * @var bool
	 */
	public $javascript_async = false;

	/**
	 * Scroll Top Scroll Offset.
	 *
	 * @var int<0,max>
	 */
	public $scroll_offset = 100;

	/**
	 * Scroll Top Button Width.
	 *
	 * @var int<0,max>
	 */
	public $button_width = 0;

	/**
	 * Scroll Top Button Height.
	 *
	 * @var int<0,max>
	 */
	public $button_height = 0;

	/**
	 * Scroll Top Button Opacity.
	 *
	 * @var int<0,100>
	 */
	public $button_opacity = 80;

	/**
	 * Scroll Top Button Fade Duration.
	 *
	 * @var int<0,max>
	 */
	public $button_fade_duration = 0;

	/**
	 * Scroll Top Button Scroll Duration.
	 *
	 * @var int<0,max>
	 */
	public $scroll_duration = 400;

	/**
	 * Scroll Top Auto Hide.
	 *
	 * @var bool
	 */
	public $auto_hide = false;

	/**
	 * Scroll Top Auto Hide After.
	 *
	 * @var int<0,max>
	 */
	public $auto_hide_after = 2;

	/**
	 * Scroll Top Hide Small Window.
	 *
	 * @var bool
	 */
	public $hide_small_window = false;

	/**
	 * Scroll Top Window Width.
	 *
	 * @var int<0,max>
	 */
	public $small_window_width = 640;

	/**
	 * Scroll Top Hide WPAdmin.
	 *
	 * @var bool
	 */
	public $hide_wpadmin = false;

	/**
	 * Scroll Top Hide iframe.
	 *
	 * @var bool
	 */
	public $hide_iframe = false;

	/**
	 * Scroll Top Button Style.
	 *
	 * @var 'image'|'text'|'font-awesome'
	 */
	public $button_style = 'image';

	/**
	 * Scroll Top Button Action.
	 *
	 * @var 'top'|'element'|'url'
	 */
	public $button_action = 'top';

	/**
	 * Scroll Top Button Action Element Selector.
	 *
	 * @var string
	 */
	public $button_action_element_selector = '';

	/**
	 * Scroll Top Button Action Container Selector.
	 *
	 * @var string
	 */
	public $button_action_container_selector = 'html, body';

	/**
	 * Scroll Top Button Action Offset.
	 *
	 * @var int
	 */
	public $button_action_element_offset = 0;

	/**
	 * Scroll Top Button Action Page URL.
	 *
	 * @var string
	 */
	public $button_action_page_url = '';

	/**
	 * Scroll Top Button Location.
	 *
	 * @var int<1,4>
	 * 1 - Bottom Right
	 * 2 - Bottom Left
	 * 3 - Top Right
	 * 4 - Top Left
	 */
	public $location = 1;

	/**
	 * Scroll Top Margin X.
	 *
	 * @var int
	 */
	public $margin_x = 20;

	/**
	 * Scroll Top Margin Y.
	 *
	 * @var int
	 */
	public $margin_y = 20;

	/**
	 * Scroll Top Display Pages.
	 *
	 * @var int<1,3>
	 * 1 - All pages
	 * 2 - Include in pages
	 * 3 - Exclude in pages
	 */
	public $display_pages = 1;

	/**
	 * Scroll Top include Pages.
	 *
	 * @var string
	 */
	public $include_pages = '';

	/**
	 * Scroll Top  exclude Pages.
	 *
	 * @var string
	 */
	public $exclude_pages = '';

	/**
	 * Scroll Top Image.
	 *
	 * @var string
	 */
	public $image = '1.png';

	/**
	 * Scroll Top Image Alt.
	 *
	 * @var string
	 */
	public $image_alt = '';

	/**
	 * Scroll Top Image Title.
	 *
	 * @var string
	 */
	public $image_title = '';

	/**
	 * Scroll Top Custom URL.
	 *
	 * @var string
	 */
	public $custom_url = '';

	/**
	 * Scroll Top Text Button Text.
	 *
	 * @var string
	 */
	public $text_button_text = '';

	/**
	 * Scroll Top Text Button Text Color.
	 *
	 * @var non-empty-string
	 */
	public $text_button_text_color = '#FFFFFF';

	/**
	 * Scroll Top Text Button Background Color.
	 *
	 * @var non-empty-string
	 */
	public $text_button_background_color = '#000000';

	/**
	 * Scroll Top Text Button Mouse Over Color.
	 *
	 * @var non-empty-string
	 */
	public $text_button_hover_color = '#000000';

	/**
	 * Scroll Top Custom CSS for Text Button.
	 *
	 * @var string
	 */
	public $text_button_css = '';

	/**
	 * Scroll Top FA Button Class.
	 *
	 * @var string
	 */
	public $fa_button_class = '';

	/**
	 * Scroll Top FA Button Exclude URL.
	 *
	 * @var bool
	 */
	public $fa_button_exclude_url = false;

	/**
	 * Scroll Top FA Button Text Color.
	 *
	 * @var non-empty-string
	 */
	public $fa_button_text_color = '#000000';

	/**
	 * Scroll Top Custom CSS.
	 *
	 * @var string
	 */
	public $fa_button_css = '';

	/**
	 * Scroll Top Font Awesome URL.
	 *
	 * @var string
	 */
	public $fa_button_url = '';

	/**
	 * Accessibility ARIA Label.
	 *
	 * @var string
	 */
	public $accessibility_aria_label = '';

	/**
	 * Accessibility Title.
	 *
	 * @var string
	 */
	public $accessibility_title = '';

	/**
	 * Accessibility Screen Reader Text.
	 *
	 * @var string
	 */
	public $accessibility_screen_reader_text = '';

	/**
	 * Enqueue CSS using file.
	 *
	 * @var bool
	 */
	public $css_enqueue_file = false;

	/**
	 * Extra CSS for Scroll Top.
	 *
	 * @var string
	 */
	public $css_extra_css = '';

	/**
	 * Last updated
	 *
	 * @var int
	 */
	public $last_updated = 0;

	/**
	 * WordPress wrapper instance.
	 *
	 * @var WP_Wrapper
	 */
	private $wp;

	/**
	 * Constructor.
	 *
	 * @param WP_Wrapper $wp WordPress wrapper instance.
	 */
	public function __construct( WP_Wrapper $wp ) {
		$this->wp = $wp;
	}

	/**
	 * Returns Scroll Top Settings Data.
	 *
	 * @return self Returns this settings entity instance.
	 */
	public function get() {
		$data = $this->wp->get_option( 'wpfront-scroll-top-options' );

		if ( empty( $data ) ) {
			$data = array();
		}

		$data = (array) $data;

		// For backward compatibility.
		if ( isset( $data['marginX'] ) ) {
			$data['margin_x'] = $data['marginX'];
		}

		if ( isset( $data['marginY'] ) ) {
			$data['margin_y'] = $data['marginY'];
		}

		if ( isset( $data['fa_button_URL'] ) ) {
			$data['fa_button_url'] = $data['fa_button_URL'];
		}

		if ( isset( $data['fa_button_exclude_URL'] ) ) {
			$data['fa_button_exclude_url'] = $data['fa_button_exclude_URL'];
		}

		$this->set_data( $data );

		return $this;
	}

	/**
	 * Save Scroll Top Option Data.
	 *
	 * @return void
	 */
	public function save() {
		$data = array();

		$data['enabled']                          = $this->enabled;
		$data['javascript_async']                 = $this->javascript_async;
		$data['scroll_offset']                    = $this->scroll_offset;
		$data['button_width']                     = $this->button_width;
		$data['button_height']                    = $this->button_height;
		$data['button_opacity']                   = $this->button_opacity;
		$data['button_fade_duration']             = $this->button_fade_duration;
		$data['scroll_duration']                  = $this->scroll_duration;
		$data['auto_hide']                        = $this->auto_hide;
		$data['auto_hide_after']                  = $this->auto_hide_after;
		$data['hide_small_window']                = $this->hide_small_window;
		$data['small_window_width']               = $this->small_window_width;
		$data['hide_wpadmin']                     = $this->hide_wpadmin;
		$data['hide_iframe']                      = $this->hide_iframe;
		$data['button_style']                     = $this->button_style;
		$data['button_action']                    = $this->button_action;
		$data['button_action_element_selector']   = $this->button_action_element_selector;
		$data['button_action_container_selector'] = $this->button_action_container_selector;
		$data['button_action_element_offset']     = $this->button_action_element_offset;
		$data['button_action_page_url']           = $this->button_action_page_url;

		$data['location'] = $this->location;
		$data['margin_x'] = $this->margin_x;
		$data['margin_y'] = $this->margin_y;

		$data['display_pages'] = $this->display_pages;
		$data['include_pages'] = $this->include_pages;
		$data['exclude_pages'] = $this->exclude_pages;

		$data['image']       = $this->image;
		$data['image_alt']   = $this->image_alt;
		$data['image_title'] = $this->image_title;
		$data['custom_url']  = $this->custom_url;

		$data['text_button_text']             = $this->text_button_text;
		$data['text_button_text_color']       = $this->text_button_text_color;
		$data['text_button_background_color'] = $this->text_button_background_color;
		$data['text_button_hover_color']      = $this->text_button_hover_color;
		$data['text_button_css']              = $this->text_button_css;

		$data['fa_button_class']       = $this->fa_button_class;
		$data['fa_button_url']         = $this->fa_button_url;
		$data['fa_button_exclude_url'] = $this->fa_button_exclude_url;
		$data['fa_button_text_color']  = $this->fa_button_text_color;
		$data['fa_button_css']         = $this->fa_button_css;

		$data['accessibility_aria_label']         = $this->accessibility_aria_label;
		$data['accessibility_title']              = $this->accessibility_title;
		$data['accessibility_screen_reader_text'] = $this->accessibility_screen_reader_text;

		$data['css_enqueue_file'] = $this->css_enqueue_file;
		$data['css_extra_css']    = $this->css_extra_css;

		$data['last_updated'] = $this->wp->time();

		$this->wp->update_option( 'wpfront-scroll-top-options', $data );
	}

	/**
	 * Set Scroll Top Option Data.
	 *
	 * @param array<mixed,mixed> $data Scroll Top option data.
	 * @return void
	 */
	public function set_data( $data ) {

		if ( isset( $data['enabled'] ) ) {
			$this->enabled = $this->validate_bool( $data['enabled'] );
		}
		if ( isset( $data['javascript_async'] ) ) {
			$this->javascript_async = $this->validate_bool( $data['javascript_async'] );
		}
		if ( isset( $data['scroll_offset'] ) ) {
			$this->scroll_offset = $this->validate_absint( $data['scroll_offset'] );
		}
		if ( isset( $data['button_width'] ) ) {
			$this->button_width = $this->validate_absint( $data['button_width'] );
		}
		if ( isset( $data['button_height'] ) ) {
			$this->button_height = $this->validate_absint( $data['button_height'] );
		}
		if ( isset( $data['button_opacity'] ) ) {
			$this->button_opacity = $this->validate_range_0_100( $data['button_opacity'] );
		}
		if ( isset( $data['button_fade_duration'] ) ) {
			$this->button_fade_duration = $this->validate_absint( $data['button_fade_duration'] );
		}
		if ( isset( $data['scroll_duration'] ) ) {
			$this->scroll_duration = $this->validate_absint( $data['scroll_duration'] );
		}
		if ( isset( $data['auto_hide'] ) ) {
			$this->auto_hide = $this->validate_bool( $data['auto_hide'] );
		}
		if ( isset( $data['auto_hide_after'] ) ) {
			$this->auto_hide_after = $this->validate_absint( $data['auto_hide_after'] );
		}
		if ( isset( $data['hide_small_window'] ) ) {
			$this->hide_small_window = $this->validate_bool( $data['hide_small_window'] );
		}
		if ( isset( $data['small_window_width'] ) ) {
			$this->small_window_width = $this->validate_absint( $data['small_window_width'] );
		}
		if ( isset( $data['hide_wpadmin'] ) ) {
			$this->hide_wpadmin = $this->validate_bool( $data['hide_wpadmin'] );
		}
		if ( isset( $data['hide_iframe'] ) ) {
			$this->hide_iframe = $this->validate_bool( $data['hide_iframe'] );
		}
		if ( isset( $data['button_style'] ) ) {
			$this->button_style = $this->validate_button_style( $data['button_style'] );
		}
		if ( isset( $data['button_action'] ) ) {
			$this->button_action = $this->validate_button_action( $data['button_action'] );
		}
		if ( isset( $data['button_action_element_selector'] ) ) {
			$this->button_action_element_selector = $this->validate_string( $data['button_action_element_selector'] );
		}
		if ( isset( $data['button_action_container_selector'] ) ) {
			$this->button_action_container_selector = $this->validate_button_action_container_selector( $data['button_action_container_selector'] );
		}
		if ( isset( $data['button_action_element_offset'] ) ) {
			$this->button_action_element_offset = $this->validate_int( $data['button_action_element_offset'] );
		}
		if ( isset( $data['button_action_page_url'] ) ) {
			$this->button_action_page_url = $this->validate_url( $data['button_action_page_url'] );
		}
		if ( isset( $data['location'] ) ) {
			$this->location = $this->validate_range_1_4( $data['location'] );
		}
		if ( isset( $data['margin_x'] ) ) {
			$this->margin_x = $this->validate_int( $data['margin_x'] );
		}
		if ( isset( $data['margin_y'] ) ) {
			$this->margin_y = $this->validate_int( $data['margin_y'] );
		}
		if ( isset( $data['display_pages'] ) ) {
			$this->display_pages = $this->validate_display_pages( $data['display_pages'] );
		}
		if ( isset( $data['include_pages'] ) ) {
			$this->include_pages = $this->validate_string( $data['include_pages'] );
		}
		if ( isset( $data['exclude_pages'] ) ) {
			$this->exclude_pages = $this->validate_string( $data['exclude_pages'] );
		}
		if ( isset( $data['image'] ) ) {
			$this->image = $this->validate_string( $data['image'] );
		}
		if ( isset( $data['image_alt'] ) ) {
			$this->image_alt = $this->validate_string( $data['image_alt'] );
		}
		if ( isset( $data['image_title'] ) ) {
			$this->image_title = $this->validate_string( $data['image_title'] );
		}
		if ( isset( $data['custom_url'] ) ) {
			$this->custom_url = $this->validate_url( $data['custom_url'] );
		}
		if ( isset( $data['text_button_text'] ) ) {
			$this->text_button_text = $this->validate_string( $data['text_button_text'] );
		}
		if ( isset( $data['text_button_text_color'] ) ) {
			$this->text_button_text_color = $this->validate_color( $data['text_button_text_color'], '#FFFFFF' );
		}
		if ( isset( $data['text_button_background_color'] ) ) {
			$this->text_button_background_color = $this->validate_color( $data['text_button_background_color'], '#000000' );
		}
		if ( isset( $data['text_button_hover_color'] ) ) {
			$this->text_button_hover_color = $this->validate_color( $data['text_button_hover_color'], '#000000' );
		}
		if ( isset( $data['text_button_css'] ) ) {
			$this->text_button_css = $this->validate_string( $data['text_button_css'] );
		}
		if ( isset( $data['fa_button_class'] ) ) {
			$this->fa_button_class = $this->validate_string( $data['fa_button_class'] );
		}
		if ( isset( $data['fa_button_url'] ) ) {
			$this->fa_button_url = $this->validate_url( $data['fa_button_url'] );
		}
		if ( isset( $data['fa_button_exclude_url'] ) ) {
			$this->fa_button_exclude_url = $this->validate_bool( $data['fa_button_exclude_url'] );
		}
		if ( isset( $data['fa_button_text_color'] ) ) {
			$this->fa_button_text_color = $this->validate_color( $data['fa_button_text_color'], '#000000' );
		}
		if ( isset( $data['fa_button_css'] ) ) {
			$this->fa_button_css = $this->validate_string( $data['fa_button_css'] );
		}
		if ( isset( $data['accessibility_aria_label'] ) ) {
			$this->accessibility_aria_label = $this->validate_string( $data['accessibility_aria_label'] );
		}
		if ( isset( $data['accessibility_title'] ) ) {
			$this->accessibility_title = $this->validate_string( $data['accessibility_title'] );
		}
		if ( isset( $data['accessibility_screen_reader_text'] ) ) {
			$this->accessibility_screen_reader_text = $this->validate_string( $data['accessibility_screen_reader_text'] );
		}
		if ( isset( $data['css_enqueue_file'] ) ) {
			$this->css_enqueue_file = $this->validate_bool( $data['css_enqueue_file'] );
		}
		if ( isset( $data['css_extra_css'] ) ) {
			$this->css_extra_css = $this->validate_string( $data['css_extra_css'] );
		}
		if ( isset( $data['last_updated'] ) ) {
			$this->last_updated = $this->validate_int( $data['last_updated'] );
		}
	}

	/**
	 * Return boolean value.
	 *
	 * @param mixed $value value.
	 * @return bool $arg
	 */
	protected function validate_bool( $value ) {
		$value = is_string( $value ) ? trim( $value ) : $value;
		return ! empty( $value );
	}

	/**
	 * Return positive integer value.
	 *
	 * @param mixed $value value.
	 * @return int<0,max> $arg
	 */
	protected function validate_absint( $value ) {
		$result = $this->wp->absint( is_scalar( $value ) ? $value : 0 );

		// Ensure we always return a non-negative value.
		return $result >= 0 ? $result : 0;
	}

	/**
	 * Return integer value in range of 0 to 100.
	 *
	 * @param mixed $value value.
	 * @return int<0,100> $arg
	 */
	protected function validate_range_0_100( $value ) {
		$value = $this->validate_absint( $value );
		if ( $value > 100 ) {
			$value = 100;
		}

		return $value;
	}

	/**
	 * Return integer value in range of 1 to 4.
	 *
	 * @param mixed $value value.
	 * @return int<1,4> $arg
	 */
	protected function validate_range_1_4( $value ) {
		$value = $this->validate_absint( $value );
		if ( $value < 1 ) {
			$value = 1;
		}
		if ( $value > 4 ) {
			$value = 4;
		}
		return $value;
	}

	/**
	 * Returns Button Action.
	 *
	 * @param mixed $value   value.
	 * @return 'top'|'element'|'url' $arg
	 */
	protected function validate_button_action( $value ) {
		$value = $this->validate_string( $value );

		return in_array( $value, array( 'top', 'element', 'url' ), true ) ? $value : 'top';
	}

	/**
	 * Returns String.
	 *
	 * @param mixed $value   value.
	 * @return string $arg
	 */
	protected function validate_string( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		$value = trim( $value );

		return $value;
	}

	/**
	 * Returns URL.
	 *
	 * @param mixed $value   value.
	 * @return string $arg
	 */
	protected function validate_url( $value ) {
		$value = $this->validate_string( $value );

		return $this->wp->esc_url_raw( $value );
	}

	/**
	 * Returns Integer.
	 *
	 * @param mixed $value   value.
	 * @return int $arg
	 */
	protected function validate_int( $value ) {
		$value = is_scalar( $value ) ? (int) $value : 0;

		return $value;
	}

	/**
	 * Returns Button Action Container Selector.
	 *
	 * @param mixed $value   value.
	 * @return string $arg
	 */
	protected function validate_button_action_container_selector( $value ) {
		$value = $this->validate_string( $value );

		if ( empty( $value ) ) {
			return 'html, body';
		}

		return $value;
	}

	/**
	 * Returns Color.
	 *
	 * @param mixed            $value    value.
	 * @param non-empty-string $d default value.
	 * @return non-empty-string $arg
	 */
	protected function validate_color( $value, $d ) {
		$value = $this->validate_string( $value );
		$color = $this->wp->sanitize_hex_color( $value );

		if ( empty( $color ) ) {
			return $d;
		}

		return $color;
	}

	/**
	 * Returns Button Style.
	 *
	 * @param mixed $value    value.
	 * @return 'image'|'text'|'font-awesome' $arg
	 */
	protected function validate_button_style( $value ) {
		$style = $this->validate_string( $value );

		return in_array( $style, array( 'image', 'text', 'font-awesome' ), true ) ? $style : 'image';
	}

	/**
	 * Returns Display Pages.
	 *
	 * @param mixed $value    value.
	 * @return int<1,3> $arg
	 */
	protected function validate_display_pages( $value ) {
		$value = $this->validate_absint( $value );

		if ( $value < 1 ) {
			return 1;
		}

		if ( $value > 3 ) {
			return 3;
		}

		return $value;
	}
}
