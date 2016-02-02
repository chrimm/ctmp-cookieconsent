<?php
/**
 * Adds the Cookie Consent library to WordPress to provide Cookie notificatons 
 *
 * @package   CTMP_Cookie_Consent
 * @author    Christoffer T. Timm <kontakt@christoffertimm.de>
 * @license   GPL-2.0+
 * @link      https://github.com/chrim/ctmp-cookieconsent
 * @copyright 2016 Christoffer T. Timm
 *
 * @wordpress-plugin
 * Plugin Name:       CTMP Cookie Consent
 * Plugin URI:        https://github.com/chrimm/ctmp-cookieconsent
 * Description:       Moves the Floating Social Bar plugin output from just inside the entry content to just before it.
 * Version:           0.1.0
 * Author:            Christoffer T. Timm
 * Author URI:        https://github.com/chrimm/ctmp-cookieconsent
 * Text Domain:       ctmpcc
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/chrimm/ctmp-cookieconsent
 * GitHub Branch:     master
 *
 *  This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License, version 2, as
 *	published by the Free Software Foundation.
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* If this file is called directly, abort. */
if ( ! defined( 'WPINC' ) ) {
	exit();
}

if( ! defined( 'CTMPCC_VER' ) ) {
	define( 'CTMPCC_VER', '0.1.0' );
}

if( ! defined( 'COOKIE_CONSENT_VER') ) {
	define( 'COOKIE_CONSENT_VER', '1.0.9' );
}

if( ! defined( 'COOKIE_CONSENT_PATH') ) {
	define( 'COOKIE_CONSENT_PATH', '//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/'.COOKIE_CONSENT_VER );
}

class CTMP_Cookie_Consent {

	/**
	 * Static property to hold our singleton instance
	 */
	static $instance = false;

	/**
	 * Configuration Object to be passed to configuration.js
	 */
	private $configuration;

	/**
	 * The default configuration as defined at
	 * https://silktide.com/tools/cookie-consent/docs/installation/
	 */
	private static $default_configuration = array(
		"dismiss":      __( 'Got it!', 'ctmpcc' ),
	    "domain":       $_SERVER['SERVER_NAME'],
	    "expiryDays":   365,
	    "message":      __( 'This website uses cookies to ensure you get the best experience on our website', 'ctmpcc' ),
	    "learnMore":    __( 'More info', 'ctmpcc' ),
	    "link":         null,
	    "target":       '_self',
	    "theme":        'light-top'
	);

	/**
	 * This is our Constructor
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	private function __construct() {

		load_plugin_textdomain( 'ctmp-cookieconsent', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		register_activation_hook( __FILE__,			array( &$this, 'ctmpcc_set_default_configuration' 	) 		);

		add_action( 'plugins_loaded', 				array( &$this, 'ctmpcc_textdomain'					) 		);

		if( is_admin() ) {
			add_action( 'admin_enqueue_scripts',	array( &$this, 'ctmpcc_enqueue_admin_scripts'		)		);
		} else {
			add_action( 'wp_enqueue_scripts',		array( &$this, 'ctmpccenqueue_scripts'				),	10	);
		}

		load_configuration();
	}

	/**
	 * If an instance exists, this returns it. If not, it creates one and retuns it.
	 *
	 * @return CTMP_Cookie_Consent
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	public static function ctmpcc_get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Enqueue scripts needed for Cookie Consent integration
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	public function ctmpcc_enqueue_scripts() {
		$in_footer = true;

		wp_enqueue_script( 'cookie_consent', 				COOKIE_CONSENT_PATH.'/cookieconsent.min.js', 	array(), 					COOKIE_CONSENT_VER,	$in_footer);
		wp_register_script( 'cookieconsent_configuration', 	plugins_url( 'js/configuration.js', __FILE__ ), array( 'cookie_consent' ), 	CTMP_CC_VER, 		$in_footer);
		wp_localize_script( 'cookieconsent_configuration', 'cookieconsent_configuration', $this->$configuration ); //Pass Object 'cookieconsent_configuration' to configuration.js
		wp_enqueue_script( 'cookieconsent_configuration' );
	}

	/**
 	 * Creates a new WP Option and writes default configuration to DB
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
 	 */
	public function ctmpcc_set_default_configuration() {
		/* Write default settings to DB */
		add_option( 'ctmp_cookieconsent_configuration', $this->default_settings );
	}

	/**
	 * Loads the current configuration from DB
	 *
 	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	public function ctmpcc_load_configuration() {
		/* Get registered option */
	    $this->configuration = get_option( 'ctmp_cookieconsent_configuration' );
	}

}

/* Instantiate our Class */
$CTMP_Cookie_Consent = CTMP_Cookie_Consent::ctmpcc_get_instance();
