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
 * WP Wrapper
 *
 * This class is a wrapper for WordPress functions to facilitate testing.
 *
 * @package wpfront-scroll-top
 */
class WP_Wrapper {
	/**
	 * Loads a plugin's translated strings.
	 *
	 * @param string $domain          Unique identifier for retrieving translated strings.
	 * @param false  $deprecated      Optional. Deprecated. Use the $plugin_rel_path parameter instead.
	 *                                       Default false.
	 * @param string $plugin_rel_path Optional. Relative path to WP_PLUGIN_DIR where the .mo file resides.
	 *                                      Default false.
	 * @return bool True when textdomain is successfully loaded, false otherwise.
	 */
	public function load_plugin_textdomain( $domain, $deprecated, $plugin_rel_path ) {
		return load_plugin_textdomain( $domain, false, $plugin_rel_path );
	}

	/**
	 * Gets the basename of a plugin.
	 *
	 * @param string $file The filename of plugin.
	 * @return string The name of a plugin.
	 */
	public function plugin_basename( $file ) {
		return plugin_basename( $file );
	}

	/**
	 * Adds a callback to a WordPress action hook.
	 *
	 * @param string   $hook_name     The name of the action to add the callback to.
	 * @param callable $callback      The callback to be run when the action is called.
	 * @param int      $priority      Optional. Used to specify the order in which the functions
	 *                               associated with a particular action are executed.
	 * @param int      $accepted_args Optional. The number of arguments the function accepts.
	 * @return bool True on success, false on failure.
	 */
	public function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		return add_action( $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Wrapper for WordPress add_filter function.
	 *
	 * @param string   $hook_name     The filter hook name.
	 * @param callable $callback      The callback function.
	 * @param int      $priority      The priority.
	 * @param int      $accepted_args Number of arguments the callback accepts.
	 * @return true
	 */
	public function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ): bool {
		return add_filter( $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a submenu page to the Settings main menu.
	 *
	 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string   $menu_title The text to be used for the menu.
	 * @param string   $capability The capability required for this menu to be displayed to the user.
	 * @param string   $menu_slug  The slug name to refer to this menu by.
	 * @param callable $callback   The function to be called to output the content for this page.
	 * @return string|false The resulting page's hook_suffix, or false if the user does not have the capability required.
	 */
	public function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback ) {
		return add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback );
	}

	/**
	 * Wrapper for menu_page_url function.
	 *
	 * @param string $menu_slug The slug name to refer to this menu by.
	 * @param bool   $display Whether to echo or return the URL.
	 * @return string The menu page URL.
	 */
	public function menu_page_url( $menu_slug, $display = true ): string {
		return menu_page_url( $menu_slug, $display );
	}

	/**
	 * Wrapper for set_transient function.
	 *
	 * @param string $transient Transient name.
	 * @param mixed  $value Transient value.
	 * @param int    $expiration Transient expiration.
	 * @return bool
	 */
	public function set_transient( $transient, $value, $expiration = 0 ): bool {
		return set_transient( $transient, $value, $expiration );
	}

	/**
	 * Gets the value of a transient.
	 *
	 * @param string $transient Transient name.
	 * @return mixed Value of transient or false if it doesn't exist.
	 */
	public function get_transient( $transient ) {
		return get_transient( $transient );
	}

	/**
	 * Wrapper for delete_transient function.
	 *
	 * @param string $transient Transient name.
	 * @return bool
	 */
	public function delete_transient( $transient ): bool {
		return ! empty( delete_transient( $transient ) );
	}

	/**
	 * Wrapper for wp_safe_redirect function.
	 *
	 * @param string $location URL to redirect to.
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function wp_safe_redirect( $location ): void {
		wp_safe_redirect( $location );
		exit;
	}

	/**
	 * Wrapper for WordPress do_action function.
	 *
	 * @param string $hook_name The name of the action to be executed.
	 * @param mixed  ...$args Optional arguments to pass to the callbacks.
	 * @return void
	 */
	public function do_action( string $hook_name, ...$args ): void {
		do_action( $hook_name, ...$args ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * Wrapper for WordPress is_network_admin function.
	 *
	 * @return bool True if network admin, false otherwise.
	 */
	public function is_network_admin(): bool {
		return is_network_admin();
	}

	/**
	 * Adds a meta box to an edit form.
	 *
	 * @param string                        $id            Meta box ID.
	 * @param string                        $title         Title of the meta box.
	 * @param callable                      $callback      Function that fills the box with the desired content.
	 * @param string                        $screen        The screen on which to show the box.
	 * @param string                        $context       The context within the screen where the box should display.
	 * @param 'core'|'default'|'high'|'low' $priority      The priority within the context where the box should show.
	 * @param array<string,mixed>           $callback_args Optional. Data that should be set as the $args property of the box array.
	 * @return void
	 */
	public function add_meta_box( string $id, string $title, $callback, string $screen, string $context = 'advanced', string $priority = 'default', $callback_args = null ): void {
		add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );
	}

	/**
	 * Do meta boxes for a page.
	 *
	 * @param string     $screen_id Screen identifier.
	 * @param string     $context   Meta box context.
	 * @param null|mixed $data_object    Optional. Object being shown on the screen.
	 * @return void
	 */
	public function do_meta_boxes( string $screen_id, string $context, $data_object = null ): void {
		do_meta_boxes( $screen_id, $context, $data_object );
	}

	/**
	 * Wrapper for wp_enqueue_style function.
	 *
	 * @param string           $handle Name of the stylesheet.
	 * @param string           $src    URL to the stylesheet.
	 * @param array<string>    $deps   Array of style handles this stylesheet depends on.
	 * @param string|bool|null $ver    Version number.
	 * @return void
	 */
	public function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), $ver = false ): void {
		wp_enqueue_style( $handle, $src, $deps, $ver );
	}

	/**
	 * Enqueues a script.
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string           $src       Full URL of the script, or path of the script relative to the WordPress root directory.
	 *                                    Default empty.
	 * @param string[]         $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is added to the URL
	 *                                    as a query string for cache busting purposes. If version is set to false, a version
	 *                                    number is automatically added equal to current installed WordPress version.
	 *                                    If set to null, no version is added.
	 * @param array|bool       $args     {
	 *     Optional. An array of additional script loading strategies. Default empty array.
	 *     Otherwise, it may be a boolean in which case it determines whether the script is printed in the footer. Default false.
	 *
	 *     @type string    $strategy     Optional. If provided, may be either 'defer' or 'async'.
	 *     @type bool      $in_footer    Optional. Whether to print the script in the footer. Default 'false'.
	 * }
	 * @return void
	 * @phpstan-param bool|array{
	 *   strategy?: string,
	 *   in_footer?: bool,
	 * } $args
	 */
	public function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = \false, $args = array() ) {
		wp_enqueue_script( $handle, $src, $deps, $ver, $args );
	}

	/**
	 * Wrapper for wp_localize_script function.
	 *
	 * @param string               $handle      Name of the script to attach data to.
	 * @param string               $object_name Name of the JavaScript object containing the data.
	 * @param array<string, mixed> $l10n        Array of data to localize.
	 * @return bool True if the script was localized, false otherwise.
	 */
	public function wp_localize_script( string $handle, string $object_name, array $l10n ): bool {
		return wp_localize_script( $handle, $object_name, $l10n );
	}

	/**
	 * Wrapper for wp_enqueue_media function.
	 *
	 * @return void
	 */
	public function wp_enqueue_media(): void {
		wp_enqueue_media();
	}

	/**
	 * Wrapper for plugins_url function.
	 *
	 * @param string $path   Optional. Path relative to the plugin's folder.
	 * @param string $plugin Optional. The plugin file path to be relative to.
	 * @return string URL to the plugins folder or to a specific file within that folder.
	 */
	public function plugins_url( $path = '', $plugin = '' ) {
		return plugins_url( $path, $plugin );
	}

	/**
	 * Wrapper for get_option function.
	 *
	 * @param string $option        Name of the option to retrieve.
	 * @param mixed  $default_value Optional. Default value to return if the option does not exist.
	 * @return mixed Value set for the option.
	 */
	public function get_option( string $option, $default_value = false ) {
		return get_option( $option, $default_value );
	}

	/**
	 * Wrapper for update_option function.
	 *
	 * @param string $option   Name of the option to update.
	 * @param mixed  $value    The new value for the option.
	 * @param bool   $autoload Optional. Whether to load the option when WordPress starts up.
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function update_option( string $option, $value, $autoload = false ): bool {
		return update_option( $option, $value, $autoload );
	}

	/**
	 * Wrapper for get_posts function.
	 *
	 * @param array<string,mixed> $args Array of arguments to retrieve posts.
	 * @return array<int,int|\WP_Post> Array of post objects or post IDs.
	 *
	 * @phpstan-param array{
	 *   numberposts?: int,
	 *   category?: int|string,
	 *   include?: int[],
	 *   exclude?: int[],
	 *   suppress_filters?: bool,
	 *   attachment_id?: int,
	 *   author?: int|string,
	 *   author_name?: string,
	 *   author__in?: int[],
	 *   author__not_in?: int[],
	 *   cache_results?: bool,
	 *   cat?: int|string,
	 *   category__and?: int[],
	 *   category__in?: int[],
	 *   category__not_in?: int[],
	 *   category_name?: string,
	 *   comment_count?: array|int,
	 *   comment_status?: string,
	 *   comments_per_page?: int,
	 *   date_query?: array,
	 *   day?: int,
	 *   exact?: bool,
	 *   fields?: string,
	 *   hour?: int,
	 *   ignore_sticky_posts?: int|bool,
	 *   m?: int,
	 *   meta_key?: string|string[],
	 *   meta_value?: string|string[],
	 *   meta_compare?: string,
	 *   meta_compare_key?: string,
	 *   meta_type?: string,
	 *   meta_type_key?: string,
	 *   meta_query?: array,
	 *   menu_order?: int,
	 *   minute?: int,
	 *   monthnum?: int,
	 *   name?: string,
	 *   nopaging?: bool,
	 *   no_found_rows?: bool,
	 *   offset?: int,
	 *   order?: string,
	 *   orderby?: string|array,
	 *   p?: int,
	 *   page?: int,
	 *   paged?: int,
	 *   page_id?: int,
	 *   pagename?: string,
	 *   perm?: string,
	 *   ping_status?: string,
	 *   post__in?: int[],
	 *   post__not_in?: int[],
	 *   post_mime_type?: string,
	 *   post_name__in?: string[],
	 *   post_parent?: int,
	 *   post_parent__in?: int[],
	 *   post_parent__not_in?: int[],
	 *   post_type?: string|string[],
	 *   post_status?: string|string[],
	 *   posts_per_page?: int,
	 *   posts_per_archive_page?: int,
	 *   s?: string,
	 *   search_columns?: string[],
	 *   second?: int,
	 *   sentence?: bool,
	 *   suppress_filters?: bool,
	 *   tag?: string,
	 *   tag__and?: int[],
	 *   tag__in?: int[],
	 *   tag__not_in?: int[],
	 *   tag_id?: int,
	 *   tag_slug__and?: string[],
	 *   tag_slug__in?: string[],
	 *   tax_query?: array,
	 *   title?: string,
	 *   update_post_meta_cache?: bool,
	 *   update_post_term_cache?: bool,
	 *   update_menu_item_cache?: bool,
	 *   lazy_load_term_meta?: bool,
	 *   w?: int,
	 *   year?: int,
	 * } $args
	 * @phpstan-ignore-next-line
	 */
	public function get_posts( $args ) {
		return get_posts( $args );
	}

	/**
	 * Wrapper for get_post_type_object function.
	 *
	 * @param string $post_type Name of post type to get object for.
	 * @return \WP_Post_Type|null Post type object or null if not found.
	 */
	public function get_post_type_object( string $post_type ) {
		return get_post_type_object( $post_type );
	}

	/**
	 * Wrapper for wp_filesystem function.
	 *
	 * @return \WP_Filesystem_Direct|null
	 */
	public function wp_filesystem() {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php'; // @phpstan-ignore-line
		}

		WP_Filesystem();

		/**
		 * WordPress filesystem object.
		 *
		 * @var \WP_Filesystem_Direct|null
		 */
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			return null;
		}

		return $wp_filesystem;
	}

	/**
	 * Wrapper for wp_create_nonce function.
	 *
	 * @param string $action Action name.
	 * @return string The nonce value.
	 */
	public function wp_create_nonce( $action ) {
		return wp_create_nonce( $action );
	}

	/**
	 * Checks if SCRIPT_DEBUG is defined and true.
	 *
	 * @return bool True if SCRIPT_DEBUG is defined and true, false otherwise.
	 */
	public function is_script_debug(): bool {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	/**
	 * Wrapper for current_user_can function.
	 *
	 * @param string $capability Capability name.
	 * @return bool Whether the current user has the given capability.
	 */
	public function current_user_can( string $capability ): bool {
		return current_user_can( $capability );
	}

	/**
	 * Wrapper for wp_send_json_error function.
	 *
	 * @param mixed $data Optional. Data to encode as JSON, then print and die.
	 * @return void
	 * @phpstan-return never
	 */
	public function wp_send_json_error( $data = null ): void {
		wp_send_json_error( $data );
	}

	/**
	 * Wrapper for wp_verify_nonce function.
	 *
	 * @param string $nonce  Nonce value that was used for verification.
	 * @param string $action Action name.
	 * @return int|false 1 if the nonce is valid, false otherwise.
	 */
	public function wp_verify_nonce( string $nonce, string $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Wrapper for wp_send_json_success function.
	 *
	 * @param mixed $data Optional. Data to encode as JSON, then print and die.
	 * @return void
	 * @phpstan-return never
	 */
	public function wp_send_json_success( $data = null ): void {
		wp_send_json_success( $data );
	}

	/**
	 * Wrapper for admin_url function.
	 *
	 * @param string $path   Optional. Path relative to the admin URL.
	 * @param string $scheme The scheme to use. Default is 'admin', which obeys force_ssl_admin().
	 * @return string Admin URL link with optional path appended.
	 */
	public function admin_url( $path = '', $scheme = 'admin' ): string {
		return admin_url( $path, $scheme );
	}

	/**
	 * Wrapper for sanitize_hex_color function.
	 *
	 * @param string $color Color to sanitize.
	 * @return string Sanitized hex color string, or empty string if invalid.
	 */
	public function sanitize_hex_color( $color ): string {
		$result = sanitize_hex_color( $color );
		return $result ? $result : '';
	}

	/**
	 * Wrapper for esc_url_raw function.
	 *
	 * @param string $url URL to escape.
	 * @return string Escaped URL.
	 */
	public function esc_url_raw( $url ): string {
		return esc_url_raw( $url );
	}

	/**
	 * Wrapper for absint function.
	 *
	 * @param array<mixed>|bool|float|int|resource|string|null $value Value to convert to absolute integer.
	 * @return int Absolute integer value.
	 */
	public function absint( $value ): int {
		return absint( $value );
	}

	/**
	 * Wrapper for time function.
	 *
	 * @return int Current Unix timestamp.
	 */
	public function time(): int {
		return time();
	}

	/**
	 * Wrapper for wp_mkdir_p function.
	 *
	 * @param string $target Full path to attempt to create.
	 * @return bool True if the path was created, false otherwise.
	 */
	public function wp_mkdir_p( string $target ): bool {
		return wp_mkdir_p( $target );
	}

	/**
	 * Wrapper for wp_strip_all_tags function.
	 *
	 * @param string $text        String containing HTML tags.
	 * @param bool   $remove_breaks Optional. Whether to remove left over line breaks and white space chars.
	 * @return string The processed string.
	 */
	public function wp_strip_all_tags( string $text, bool $remove_breaks = false ): string {
		return wp_strip_all_tags( $text, $remove_breaks );
	}

	/**
	 * Wrapper for safecss_filter_attr function.
	 *
	 * @param string $css CSS string to filter.
	 * @return string Filtered CSS string.
	 */
	public function safecss_filter_attr( string $css ): string {
		return safecss_filter_attr( $css );
	}

	/**
	 * Wrapper for sanitize_textarea_field function.
	 *
	 * @param string $str String to sanitize.
	 * @return string Sanitized string.
	 */
	public function sanitize_textarea_field( string $str ): string {
		return sanitize_textarea_field( $str );
	}

	/**
	 * Wrapper for file_exists function.
	 *
	 * @param string $filename Path to the file or directory.
	 * @return bool True if the file or directory exists, false otherwise.
	 */
	public function file_exists( string $filename ): bool {
		return file_exists( $filename );
	}

	/**
	 * Wrapper for filemtime function.
	 *
	 * @param string $filename Path to the file.
	 * @return int|false The time the file was last modified, or false on failure.
	 */
	public function filemtime( string $filename ) {
		return filemtime( $filename );
	}

	/**
	 * Sanitized CSS.
	 *
	 * @param string $css CSS string to sanitize.
	 * @return string Sanitized CSS string.
	 */
	public function sanitize_css( string $css ): string {
		if ( empty( $css ) ) {
			return '';
		}

		$css = wp_strip_all_tags( $css, true );
		$css = preg_replace( '/\s*([{}|:;,])\s+/', '$1', $css );

		if ( empty( $css ) ) {
			return '';
		}

		$css = preg_replace( '/(?i)(@|expression|javascript:|vbscript:|data:)/', '', $css );

		if ( empty( $css ) ) {
			return '';
		}

		$css = preg_replace( array( '/({)/', '/(}|;)/', '/(,|:)/' ), array( "\n$1\n", "$1\n", '$1 ' ), $css );

		if ( empty( $css ) ) {
			return '';
		}

		return $css;
	}

	/**
	 * Outputs sanitized CSS.
	 *
	 * @param string $css CSS string to output.
	 * @return void
	 */
	public function echo_css( string $css ): void {
		$css = $this->sanitize_css( $css );
		$css = wp_strip_all_tags( $css, true );
		echo $css; //@phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
