<?php
/**
 * Plugin Name: WPFront Scroll Top
 * Plugin URI: http://wpfront.com/scroll-top-plugin/
 * Description: Adds a lightweight and smooth "Scroll to Top" button to your WordPress site, improving navigation and user experience with customizable options.
 * Version: 3.0.1
 * Requires at least: 5.3
 * Requires PHP: 7.2
 * Author: WPFront Team
 * Author URI: http://wpfront.com
 * License: GPL v3
 *
 * @package wpfront-scroll-top
 */

/*
	WPFront Scroll Top
	Copyright (C) 2013, wpfront.com
	Website: wpfront.com
	Contact: syam@wpfront.com

	WPFront Scroll Top Plugin is distributed under the GNU General Public License, Version 3,
	June 2007. Copyright (C) 2007 Free Software Foundation, Inc., 51 Franklin
	St, Fifth Floor, Boston, MA 02110, USA

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/


defined( 'ABSPATH' ) || exit;


add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'WPFront_Scroll_Top' )
			|| class_exists( 'WPFront\Scroll_Top\WPFront_Scroll_Top' )
			|| class_exists( 'WPFront_Scroll_Top\WPFront_Scroll_Top' ) ) {
			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpfront-scroll-top.php';

		( new WPFront\Scroll_Top\DI() )->get( WPFront\Scroll_Top\WPFront_Scroll_Top::class )->init();
	}
);

register_activation_hook(
	__FILE__,
	function () {
		set_transient( plugin_basename( __FILE__ ) . '_activated', true, 15 );
	}
);
