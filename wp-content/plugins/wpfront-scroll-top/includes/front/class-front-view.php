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
 * Front view class.
 *
 * @package wpfront-scroll-top
 */
class Front_View {

	/**
	 * Settings entity instance.
	 *
	 * @var Settings_Entity
	 */
	private $settings;

	/**
	 * WPFront Scroll Top instance.
	 *
	 * @var WPFront_Scroll_Top
	 */
	private $st;

	/**
	 * WP wrapper instance.
	 *
	 * @var WP_Wrapper
	 */
	private $wp;

	/**
	 * Constructor.
	 *
	 * @param WPFront_Scroll_Top $st WPFront Scroll Top instance.
	 * @param WP_Wrapper         $wp WordPress wrapper instance.
	 */
	public function __construct( WPFront_Scroll_Top $st, WP_Wrapper $wp ) {
		$this->st = $st;
		$this->wp = $wp;
	}

	/**
	 * Writes the HTML for the front view.
	 *
	 * @param Settings_Entity $settings Settings object.
	 *
	 * @return string HTML output.
	 */
	public function get_html( $settings ) {
		$this->settings = $settings;

		ob_start();
		$this->write_html();
		$html = ob_get_clean();
		$html = empty( $html ) ? '' : $html;
		return $html;
	}

	/**
	 * Writes the HTML for the front view.
	 *
	 * @return void
	 */
	private function write_html() {
		$tag = 'url' === $this->settings->button_action ? 'a' : 'button';
		printf(
			'<%1$s id="wpfront-scroll-top-container" aria-label="%2$s" title="%3$s" %4$s>',
			esc_html( $tag ),
			esc_attr( $this->settings->accessibility_aria_label ),
			esc_attr( $this->settings->accessibility_title ),
			'a' === $tag ? sprintf( 'href="%s"', esc_url( $this->settings->button_action_page_url ) ) : ''
		);
		$this->write_inner_html();
		echo '</' . esc_html( $tag ) . '>';
	}

	/**
	 * Writes the inner HTML for the front view.
	 *
	 * @return void
	 */
	private function write_inner_html() {
		$this->write_screen_reader_html();

		switch ( $this->settings->button_style ) {
			case 'text':
				$this->write_text_button_html();
				break;
			case 'font-awesome':
				$this->write_font_awesome_button_html();
				break;
			default:
				$this->write_image_button_html();
				break;
		}
	}

	/**
	 * Writes the image HTML for the front view.
	 *
	 * @return void
	 */
	private function write_image_button_html() {
		if ( 'custom' === $this->settings->image ) {
			$img = $this->settings->custom_url;
		} else {
			$img = $this->st->get_plugin_url( 'includes/assets/icons/' ) . $this->settings->image;
		}

		printf(
			'<img src="%s" alt="%s" title="%s">',
			esc_url( $img ),
			esc_attr( $this->settings->image_alt ),
			esc_attr( $this->settings->image_title )
		);
	}

	/**
	 * Writes the text HTML for the front view.
	 *
	 * @return void
	 */
	private function write_text_button_html() {
		printf(
			'<span class="text-holder">%s</span>',
			esc_html( $this->settings->text_button_text )
		);
	}

	/**
	 * Writes the Font Awesome button HTML for the front view.
	 *
	 * @return void
	 */
	private function write_font_awesome_button_html() {
		printf(
			'<i class="%s" aria-hidden="true"></i>',
			esc_attr( $this->settings->fa_button_class )
		);
	}

	/**
	 * Writes the screen reader text for the front view.
	 *
	 * @return void
	 */
	private function write_screen_reader_html() {
		if ( empty( $this->settings->accessibility_screen_reader_text ) ) {
			return;
		}

		echo '<span class="sr-only screen-reader-text">' . esc_html( $this->settings->accessibility_screen_reader_text ) . '</span>';
	}

	/**
	 * Returns the CSS for the front view.
	 *
	 * @param Settings_Entity $settings Settings object.
	 *
	 * @return string CSS output.
	 */
	public function get_css( $settings ) {
		$this->settings = $settings;

		ob_start();
		$this->write_css();
		$css = ob_get_clean();
		$css = empty( $css ) ? '' : $css;
		return $css;
	}

	/**
	 * Writes the CSS for the front view.
	 *
	 * @return void
	 */
	private function write_css() {
		include dirname( __DIR__ ) . '/assets/wpfront-scroll-top.css';

		$this->location_css();
		$this->write_hide_small_window_css();

		$this->write_image_button_css();
		$this->write_text_button_css();
		$this->write_font_awesome_button_css();

		$this->write_extra_css();
	}

	/**
	 * Outputs the CSS for the location.
	 *
	 * @return void
	 */
	protected function location_css() {
		$margin_x = strval( $this->settings->margin_x );
		$margin_y = strval( $this->settings->margin_y );

		echo '#wpfront-scroll-top-container{';
		switch ( $this->settings->location ) {
			case 1:
				echo 'right:' . esc_attr( $margin_x ) . 'px;';
				echo 'bottom:' . esc_attr( $margin_y ) . 'px;';
				break;
			case 2:
				echo 'left:' . esc_attr( $margin_x ) . 'px;';
				echo 'bottom:' . esc_attr( $margin_y ) . 'px;';
				break;
			case 3:
				echo 'right:' . esc_attr( $margin_x ) . 'px;';
				echo 'top:' . esc_attr( $margin_y ) . 'px;';
				break;
			case 4:
				echo 'left:' . esc_attr( $margin_x ) . 'px;';
				echo 'top:' . esc_attr( $margin_y ) . 'px;';
				break;
		}
		echo '}';
	}

	/**
	 * Outputs the CSS for the image button.
	 *
	 * @return void
	 */
	private function write_image_button_css() {
		$button_width  = strval( $this->settings->button_width );
		$button_height = strval( $this->settings->button_height );
		?>
		#wpfront-scroll-top-container img {
			width: <?php echo 0 === $this->settings->button_width ? 'auto' : esc_attr( $button_width ) . 'px'; ?>;
			height: <?php echo 0 === $this->settings->button_height ? 'auto' : esc_attr( $button_height ) . 'px'; ?>;
		}
		<?php
	}

	/**
	 * Outputs the CSS for the text button.
	 *
	 * @return void
	 */
	private function write_text_button_css() {
		$button_width  = strval( $this->settings->button_width );
		$button_height = strval( $this->settings->button_height );
		?>
		#wpfront-scroll-top-container .text-holder {
			color: <?php echo esc_attr( $this->settings->text_button_text_color ); ?>;
			background-color: <?php echo esc_attr( $this->settings->text_button_background_color ); ?>;
			width: <?php echo 0 === $this->settings->button_width ? 'auto' : esc_attr( $button_width ) . 'px'; ?>;
			height: <?php echo 0 === $this->settings->button_height ? 'auto' : esc_attr( $button_height ) . 'px'; ?>;
			<?php echo 0 === $this->settings->button_height ? '' : 'line-height:' . esc_attr( $button_height ) . 'px'; ?>;

			<?php
			$this->wp->echo_css( $this->settings->text_button_css );
			?>
		}

		#wpfront-scroll-top-container .text-holder:hover {
			background-color: <?php echo esc_attr( $this->settings->text_button_hover_color ); ?>;
		}
		<?php
	}

	/**
	 * Outputs the CSS for the font awesome button.
	 *
	 * @return void
	 */
	protected function write_font_awesome_button_css() {
		?>
		#wpfront-scroll-top-container i {
			color: <?php echo esc_attr( $this->settings->fa_button_text_color ); ?>;
		}

		<?php
		$this->wp->echo_css( $this->settings->fa_button_css );
	}

	/**
	 * Outputs the CSS to hide the button in small windows.
	 *
	 * @return void
	 */
	protected function write_hide_small_window_css() {
		if ( $this->settings->hide_small_window ) {
			?>
			@media screen and (max-width: <?php echo esc_attr( strval( $this->settings->small_window_width ) ) . 'px'; ?>) {
				#wpfront-scroll-top-container {
					visibility: hidden;
				}
			}
			<?php
		}
	}

	/**
	 * Outputs the extra CSS.
	 *
	 * @return void
	 */
	protected function write_extra_css() {
		if ( ! empty( $this->settings->css_extra_css ) ) {
			$this->wp->echo_css( $this->settings->css_extra_css );
		}
	}
}
