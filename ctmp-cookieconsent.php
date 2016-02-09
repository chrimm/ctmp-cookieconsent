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
 * Description:       Adds the Cookie Consent library to WordPress to provide Cookie notificatons.
 * Version:           0.1.0
 * Author:            Christoffer T. Timm
 * Author URI:        http://christoffertimm.de
 * Text Domain:       ctmpcc
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/chrimm/ctmp-cookieconsent
 * GitHub Branch:     master
 *
 * 	This program is free software; you can redistribute it and/or modify
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

define( 'CTMPCC_OPTION_GROUP', 'ctmp_cookieconsent' );
define( 'CTMPCC_OPTION_PREFIX', CTMPCC_OPTION_GROUP.'_' );

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
	 * This is our Constructor
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	private function __construct() {

		load_plugin_textdomain( 'ctmpcc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		register_activation_hook( __FILE__,			array( &$this, 'ctmpcc_install' 					) 		);

		if( is_admin() ) {
			add_action( 'admin_init', 				array( &$this, 'ctmpcc_settings_init' 				)		);
			add_action( 'admin_menu', 				array( &$this, 'ctmpcc_settings_menu' 				)		);
		} else {
			add_action( 'wp_enqueue_scripts',		array( &$this, 'ctmpcc_enqueue_scripts'				),	10	);
		}
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

		wp_enqueue_script( 'cookieconsent', 				COOKIE_CONSENT_PATH.'/cookieconsent.min.js', 	array(), 					COOKIE_CONSENT_VER,	$in_footer);
		wp_register_script( 'cookieconsent_configuration', 	plugins_url( 'js/configuration.js', __FILE__ ), array( 'cookieconsent' ), 	CTMPCC_VER, 		$in_footer);
		wp_localize_script( 'cookieconsent_configuration', 'cookieconsent_configuration', $this->configuration ); //Pass Object 'cookieconsent_configuration' to configuration.js
		wp_enqueue_script( 'cookieconsent_configuration' );
	}

	/**
	 * Add Settings page
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_menu() {

	    add_options_page(
	    	__( 'Cookie Consent', 'ctmpcc' ),			/* Page Title */
	    	__( 'Cookie Consent', 'ctmpcc' ),			/* Menu Title */
	    	'manage_options',							/* Capability */
	    	__FILE__,									/* Menu Slug  */
	    	array( &$this,'ctmpcc_settings_page' )		/* Function   */
	    );
	}

	/**
	 * Outputs the Settings page content
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page() {
	    ?>

	    <div class="wrap">
			<h2>Cookie Consent</h2>
	        <p><?php _e( 'You can edit the display of the cookie notification to your liking' ); ?></p>

	        <form method="post" action="options.php">
	            <?php

	            /* Output the settings sections */
	            do_settings_sections( __FILE__ );

	            /* Output the hidden fields, nonce, etc. */
	            settings_fields( CTMPCC_OPTION_GROUP );

	            /* Output submit button */
	            submit_button();

	            ?>
	        </form>
	    </div>

	    <?php
	}

	/**
	 * Outputs the settings section
	 *
	 * Well, indeed it does nothing.
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_section_callback() {
		/* Nothing to do here */
	}

	/**
	 * Outputs the 'dismiss' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_dismiss() {
		echo '<input type="text" name="' . CTMPCC_OPTION_PREFIX . 'dismiss" id="' . CTMPCC_OPTION_PREFIX . 'dismiss" value="' . get_option( CTMPCC_OPTION_PREFIX.'dismiss' ) . '" >';
	}

	/**
	 * Outputs the 'domain' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_domain() {
		echo '<input type="text" name="' . CTMPCC_OPTION_PREFIX . 'domain" id="' . CTMPCC_OPTION_PREFIX . 'domain" value="' . get_option( CTMPCC_OPTION_PREFIX.'domain' ) . '" >';
	}

	/**
	 * Outputs the 'expirydays' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_expirydays() {
		echo '<input type="number" name="' . CTMPCC_OPTION_PREFIX . 'expiryDays" id="' . CTMPCC_OPTION_PREFIX . 'expiryDays" value="' . get_option( CTMPCC_OPTION_PREFIX.'expiryDays' ) . '" >';
	}

	/**
	 * Outputs the 'message' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_message() {
		echo '<textarea name="' . CTMPCC_OPTION_PREFIX . 'message" id=="' . CTMPCC_OPTION_PREFIX . 'message">' . get_option( CTMPCC_OPTION_PREFIX.'message' ) . '</textarea>';
	}

	/**
	 * Outputs the 'learnmore' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_learnmore() {
		echo '<input type="text" name="' . CTMPCC_OPTION_PREFIX . 'learnMore" id="' . CTMPCC_OPTION_PREFIX . 'learnMore" value="' . get_option( CTMPCC_OPTION_PREFIX.'learnMore' ) . '" >';
	}

	/**
	 * Outputs the 'dismiss' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_link() {
		//echo '<input type="dropdown-pages" name="' . CTMPCC_OPTION_PREFIX . 'link" id="' . CTMPCC_OPTION_PREFIX . 'link" value="' . get_option( CTMPCC_OPTION_PREFIX.'link' ) . '" >';
		wp_dropdown_pages( array(
			'name'=>CTMPCC_OPTION_PREFIX . 'link',
			'show_option_none'=>__( 'Do not add any link', 'ctmpcc' ),
			'option_none_value'=>null
		) );
	}

	/**
	 * Outputs the 'dismiss' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_target() {
		$current_value = get_option( CTMPCC_OPTION_PREFIX.'target' );

		?>
			<select name="<?php echo CTMPCC_OPTION_PREFIX . 'target'; ?>" ="<?php echo CTMPCC_OPTION_PREFIX . 'target'; ?>">
				<option value="_self"<?php  ( '_self'  == $current_value ) ? ' selected="selected"' : ''; ?>><?php _e( 'Same Page (_self)', 'ctmpcc' ); ?></option>
				<option value="_blank"<?php ( '_blank' == $current_value ) ? ' selected="selected"' : ''; ?>><?php _e( 'New Page (_blank)', 'ctmpcc' ); ?></option>
			</select>
		<?php
	}

	/**
	 * Outputs the 'theme' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_theme() {
		$available_themes = array(
			'dark-top'			=> __( 'Dark banner at the top', 		'ctmpcc' ),
		 	'dark-bottom'		=> __( 'Dark banner at the bottom', 	'ctmpcc' ),
			'dark-floating'		=> __( 'Dark box floating in the lower right', 'ctmpcc' ),
			'light-top'			=> __( 'Light banner at the top', 		'ctmpcc' ),
			'light-bottom'		=> __( 'Light banner at the bottom', 	'ctmpcc' ),
			'light-floating'	=> __( 'Light box floating in the lower right', 'ctmpcc' )
			//'custom'			=> __( 'Use custom stylesheet', 'ctmpcc' ) -- not yet implemented...
		);

		$current_value = get_option( CTMPCC_OPTION_PREFIX.'theme' );

		echo '<select name="'. CTMPCC_OPTION_PREFIX . 'theme">';
		foreach($available_themes as $theme => $theme_description) {
			echo '<option value="' . $theme . '"'. (( $theme  == $current_value ) ? ' selected="selected"' : '') .'>'.$theme_description.'</option>';
		}
		echo '</select>';
	}

	/**
	 * Initializes the settings fields
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_init() {
		add_settings_section( CTMPCC_OPTION_PREFIX.'section_display', 	__( 'Display Settings', 'ctmpcc' ), 								array( &$this, 'ctmpcc_settings_page_section_callback'),  			__FILE__);

		add_settings_field( CTMPCC_OPTION_PREFIX.'message', 		__( 'Cookie Message', 'ctmpcc' ), 									array( &$this, 'ctmpcc_settings_page_field_callback_message'), 		__FILE__, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'dismiss', 		__( 'Dismiss Button Caption', 'ctmpcc' ), 							array( &$this, 'ctmpcc_settings_page_field_callback_dismiss'), 		__FILE__, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'learnMore', 	__( 'Caption of the Learn More Link', 'ctmpcc' ), 					array( &$this, 'ctmpcc_settings_page_field_callback_learnmore'), 	__FILE__, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'link', 			__( 'Target Page of the Learn More Link', 'ctmpcc' ), 				array( &$this, 'ctmpcc_settings_page_field_callback_link'),			__FILE__, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'target', 		__( 'Open Learn More Link in the Same or New Page?', 'ctmpcc' ), 	array( &$this, 'ctmpcc_settings_page_field_callback_target'), 		__FILE__, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'theme', 		__( 'How to Display the Cookie Notification?', 'ctmpcc' ), 			array( &$this, 'ctmpcc_settings_page_field_callback_theme'), 		__FILE__, CTMPCC_OPTION_PREFIX.'section_display');

		add_settings_section( CTMPCC_OPTION_PREFIX.'section_advanced', 	__( 'Advanced Settings', 'ctmpcc' ), 								array( &$this, 'ctmpcc_settings_page_section_callback'),  			__FILE__);

		add_settings_field( CTMPCC_OPTION_PREFIX.'domain', 		__( '(Sub-)Domain for Opt-out Scope', 'ctmpcc' ), 					array( &$this, 'ctmpcc_settings_page_field_callback_domain'), 		__FILE__, CTMPCC_OPTION_PREFIX.'section_advanced');
		add_settings_field( CTMPCC_OPTION_PREFIX.'expiryDays', 	__( 'Opt-out Expiry Date', 'ctmpcc' ), 								array( &$this, 'ctmpcc_settings_page_field_callback_expirydays'), 	__FILE__, CTMPCC_OPTION_PREFIX.'section_advanced');
	}

	/**
	 * Static default configuration as defined at
	 * https://silktide.com/tools/cookie-consent/docs/installation/
	 *
	 * @return Array of default configuration keys and values
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	public static function ctmpcc_default_configuration() {
		return array(
			'dismiss'		=> __( 'Got it!', 'ctmpcc' ),
		    'domain'		=> $_SERVER['SERVER_NAME'],
		    'expiryDays'	=> 365,
		    'message'		=> __( 'This website uses cookies to ensure you get the best experience on our website', 'ctmpcc' ),
		    'learnMore'		=> __( 'More info', 'ctmpcc' ),
		    'link'			=> null,
		    'target'		=> '_self',
	    	'theme'			=> 'light-top'
		);
	}

	/**
 	 * Creates a new WP Option and writes default configuration to DB
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
 	 */
	public function ctmpcc_install() {
		/* Set configuration to default */
		$this->configuration = self::ctmpcc_default_configuration();

		/* Write default settings to DB and register settings */
		foreach($this->configuration as $conf_key => $conf_val) {
			add_option( CTMPCC_OPTION_PREFIX.$conf_key, $conf_val );
			register_setting( CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.$conf_key );
		}
	}
}

/* Instantiate our Class */
$CTMP_Cookie_Consent = CTMP_Cookie_Consent::ctmpcc_get_instance();
