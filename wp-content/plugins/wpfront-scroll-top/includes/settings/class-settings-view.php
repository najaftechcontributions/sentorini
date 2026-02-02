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
 * Settings view class.
 *
 * @package wpfront-scroll-top
 */
class Settings_View {
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
	 * Renders the settings view.
	 *
	 * @return void
	 */
	public function render(): void {
		$this->add_hooks();
		$this->write_html();
	}

	/**
	 * Adds hooks for the settings view.
	 *
	 * @return void
	 */
	public function add_hooks() {
		$this->wp->add_meta_box( 'postbox-display-settings', __( 'Display', 'wpfront-scroll-top' ), array( $this, 'display_html' ), 'wpfront-scroll-top', 'normal', 'default', array( 'file' => 'display-settings.html' ) );
		$this->wp->add_meta_box( 'postbox-location-settings', __( 'Location', 'wpfront-scroll-top' ), array( $this, 'display_html' ), 'wpfront-scroll-top', 'normal', 'default', array( 'file' => 'location-settings.html' ) );
		$this->wp->add_meta_box( 'postbox-filter-settings', __( 'Filter', 'wpfront-scroll-top' ), array( $this, 'display_html' ), 'wpfront-scroll-top', 'normal', 'default', array( 'file' => 'filter-settings.html' ) );
		$this->wp->add_meta_box( 'postbox-button-settings', '{{ button_style_options[data.button_style] }}', array( $this, 'display_html' ), 'wpfront-scroll-top', 'normal', 'default', array( 'file' => array( 'image-button-settings.html', 'text-button-settings.html', 'font-awesome-button-settings.html' ) ) );
		$this->wp->add_meta_box( 'postbox-accessibility-settings', __( 'Accessibility', 'wpfront-scroll-top' ), array( $this, 'display_html' ), 'wpfront-scroll-top', 'normal', 'default', array( 'file' => 'accessibility-settings.html' ) );
		$this->wp->add_meta_box( 'postbox-css-settings', __( 'CSS', 'wpfront-scroll-top' ), array( $this, 'display_html' ), 'wpfront-scroll-top', 'normal', 'default', array( 'file' => 'css-settings.html' ) );
		$this->wp->add_meta_box( 'postbox-side-1', __( 'Actions', 'wpfront-scroll-top' ), array( $this, 'action_area' ), 'wpfront-scroll-top', 'side', 'default' );
		$this->wp->add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
	}

	/**
	 * Displays the settings html.
	 *
	 * @return void
	 */
	public function write_html() {
		?>
		<div class="wrap scroll-top">
			<h1><?php esc_html_e( 'WPFront Scroll Top Settings', 'wpfront-scroll-top' ); ?></h1>
			<div id="scroll-top-content" class="wrap" style="display: none;">
				<form id="scroll-top-content-form" onsubmit="return false" @submit.prevent="submit">
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content">
								<?php $this->wp->do_meta_boxes( 'wpfront-scroll-top', 'normal', null ); ?>
							</div>
							<div id="postbox-container-1" class="postbox-container-right">
								<?php $this->wp->do_meta_boxes( 'wpfront-scroll-top', 'side', null ); ?>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Displays the HTML for the settings view.
	 *
	 * @param \WP_Post            $post The post object.
	 * @param array<string,mixed> $args The arguments passed to the callback.
	 *
	 * @return void
	 */
	public function display_html( $post, $args ): void {
		$file = $args['args'];

		if ( is_array( $file ) ) {
			$file = $file['file'];
		}

		if ( is_array( $file ) ) {
			foreach ( $file as $f ) {
				$this->display_html( $post, array( 'args' => array( 'file' => $f ) ) );
			}

			return;
		}

		if ( ! is_string( $file ) ) {
			return;
		}

		include __DIR__ . '/html/' . $file;
	}

	/**
	 * Displays the action area for the settings view.
	 *
	 * @return void
	 */
	public function action_area(): void {
		?>
		<p class="submit">
			<input type="submit" class="button button-primary" :disabled="save_disabled" value="<?php esc_html_e( 'Save Changes', 'wpfront-scroll-top' ); ?>" />
		</p>
		<div  class="settings-updated-message" v-if="save_success">
			<?php esc_html_e( 'Settings updated successfully.', 'wpfront-scroll-top' ); ?>
		</div>
		<div  class="settings-error-message" v-if="error_message">
			<?php esc_html_e( 'Save failed.', 'wpfront-scroll-top' ); ?> {{ error_message }}
		</div>
		<?php
	}

	/**
	 * Modifies the admin footer text.
	 *
	 * @param string $text The current admin footer text.
	 * @return string Modified footer text
	 */
	public function admin_footer_text( $text ): string {
		$settings_link = 'scroll-top-plugin-settings/';

		$settings_link = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://wpfront.com/' . $settings_link, __( 'Settings description', 'wpfront-scroll-top' ) );
		$review_link   = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/support/plugin/wpfront-scroll-top/reviews/', __( 'Write a review', 'wpfront-scroll-top' ) );
		$donate_link   = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://wpfront.com/donate/', __( 'Buy me a Beer or Coffee', 'wpfront-scroll-top' ) );

		return sprintf( '%s | %s | %s | %s', $settings_link, $review_link, $donate_link, $text );
	}
}
