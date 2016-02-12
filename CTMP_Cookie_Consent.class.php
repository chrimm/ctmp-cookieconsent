<?php

/* If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'CTMPCC_OPTION_GROUP', 'ctmp-cookieconsent' );
define( 'CTMPCC_OPTION_PREFIX', CTMPCC_OPTION_GROUP.'_' );
define( 'CTMPCC_OPTION_FIELD_CALLBACK_PREFIX', 'ctmpcc_settings_page_field_callback_');

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
	 * Static default configuration as defined at
	 * https://silktide.com/tools/cookie-consent/docs/installation/
	 *
	 * @return Array of default configuration keys and values
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	public static function ctmpcc_default_configuration() {
		return array(
			'dismiss'		=> __( 'Got it!', 'ctmp-cookieconsent' ),
		    'domain'		=> $_SERVER['SERVER_NAME'],
		    'expiryDays'	=> 365,
		    'message'		=> __( 'This website uses cookies to ensure you get the best experience on our website', 'ctmp-cookieconsent' ),
		    'learnMore'		=> __( 'More info', 'ctmp-cookieconsent' ),
		    'link'			=> null,
		    'target'		=> '_self',
	    	'theme'			=> 'light-top'
		);
	}

	/**
	 * This is our Constructor
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	private function __construct() {

		load_plugin_textdomain( 'ctmp-cookieconsent', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );



		if( is_admin() ) {
			add_action( 'admin_init', 				array( &$this, 'ctmpcc_settings_init' 	)		);
			add_action( 'admin_menu', 				array( &$this, 'ctmpcc_settings_menu' 	)		);
		} else {
			add_action( 'wp_enqueue_scripts',		array( &$this, 'ctmpcc_enqueue_scripts'	),	10	);
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
		$configuration = self::ctmpcc_get_configuration();

		$configuration['link'] = get_permalink( $configuration['link'] );

		wp_enqueue_script( 'cookieconsent', 				COOKIE_CONSENT_PATH.'/cookieconsent.min.js', 	array(), 					COOKIE_CONSENT_VER,	$in_footer);
		wp_enqueue_script( 'cookieconsent_configuration', 	plugins_url( 'js/configuration.js', __FILE__ ), array( 'cookieconsent' ), 	CTMPCC_VER, 		$in_footer);
		wp_localize_script( 'cookieconsent_configuration', 'cookieconsent_configuration', $configuration); //Pass Object 'cookieconsent_configuration' to configuration.js
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
	    	__( 'Cookie Consent', 'ctmp-cookieconsent' ),	/* Page Title */
	    	__( 'Cookie Consent', 'ctmp-cookieconsent' ),	/* Menu Title */
	    	'manage_options',								/* Capability */
	    	CTMPCC_OPTION_GROUP,							/* Menu Slug  */
	    	array( &$this,'ctmpcc_settings_page' )			/* Function   */
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
	        <p><?php _e( 'You can edit the display of the cookie notification to your liking', 'ctmp-cookieconsent' ); ?></p>

	        <form method="post" action="options.php">
	            <?php

	            /* Output the settings sections */
	            do_settings_sections( CTMPCC_OPTION_GROUP );

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
		?>
			<hr />
		<?php
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
	 * Sanitizes the input of the 'dismiss' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_dismiss_sanitize($input) {
		return strip_tags($input);
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
	 * Sanitizes the input of the 'domain' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_domain_sanitize($input) {
		return strip_tags($input);
	}

	/**
	 * Outputs the 'expiryDays' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_expiryDays() {
		echo '<input type="number" name="' . CTMPCC_OPTION_PREFIX . 'expiryDays" id="' . CTMPCC_OPTION_PREFIX . 'expiryDays" value="' . get_option( CTMPCC_OPTION_PREFIX.'expiryDays' ) . '" >';
	}

	/**
	 * Sanitizes the input of the 'expiryDays' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_expiryDays_sanitize($input) {
		/* Check whether the input value is a valid positive integer
		 * if not, use the default value from the default values array.
		 */
		return (is_numeric($input) && ($input > 0)&& ($input < PHP_INT_MAX)) ? $input : self::ctmpcc_default_configuration()['expiryDays'];
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
	 * Sanitizes the input of the 'message' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_message_sanitize($input) {
		return strip_tags($input);
	}

	/**
	 * Outputs the 'learnmore' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_learnMore() {
		echo '<input type="text" name="' . CTMPCC_OPTION_PREFIX . 'learnMore" id="' . CTMPCC_OPTION_PREFIX . 'learnMore" value="' . get_option( CTMPCC_OPTION_PREFIX.'learnMore' ) . '" >';
	}

	/**
	 * Sanitizes the input of the 'learnMore' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_learnMore_sanitize($input) {
		return strip_tags($input);
	}

	/**
	 * Outputs the 'link' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_link() {
		wp_dropdown_pages( array(
			'name'				=> CTMPCC_OPTION_PREFIX . 'link',
			'show_option_none'	=> __( 'Do not add any link', 'ctmp-cookieconsent' ),
			'option_none_value'	=> null,
			'selected'			=> get_option( CTMPCC_OPTION_PREFIX.'link' )
		) );
	}

	/**
	 * Sanitizes the input of the 'link' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_link_sanitize($input) {
		return strip_tags($input);
	}

	/**
	 * Outputs the 'target' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_target() {
		$current_value = get_option( CTMPCC_OPTION_PREFIX.'target' );

		?>
			<select name="<?php echo CTMPCC_OPTION_PREFIX . 'target'; ?>" ="<?php echo CTMPCC_OPTION_PREFIX . 'target'; ?>">
				<option value="_self"<?php  ( '_self'  == $current_value ) ? ' selected="selected"' : ''; ?>><?php _e( 'Same Page (_self)', 'ctmp-cookieconsent' ); ?></option>
				<option value="_blank"<?php ( '_blank' == $current_value ) ? ' selected="selected"' : ''; ?>><?php _e( 'New Page (_blank)', 'ctmp-cookieconsent' ); ?></option>
			</select>
		<?php
	}

	/**
	 * Sanitizes the input of the 'target' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_target_sanitize($input) {
		return (($input != '_self') && ($input != '_blank')) ? self::ctmpcc_default_configuration()['target'] : $input;
	}

	/**
	 * Returns an array of all available themes and their localized descriptions
	 *
	 * @return An array of all available themes
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	static function ctmpcc_get_available_themes() {
		return array(
			'dark-top'			=> __( 'Dark banner at the top', 				'ctmp-cookieconsent' ),
		 	'dark-bottom'		=> __( 'Dark banner at the bottom', 			'ctmp-cookieconsent' ),
			'dark-floating'		=> __( 'Dark box floating in the lower right', 	'ctmp-cookieconsent' ),
			'light-top'			=> __( 'Light banner at the top', 				'ctmp-cookieconsent' ),
			'light-bottom'		=> __( 'Light banner at the bottom', 			'ctmp-cookieconsent' ),
			'light-floating'	=> __( 'Light box floating in the lower right', 'ctmp-cookieconsent' )
			//'custom'			=> __( 'Use custom stylesheet', 'ctmp-cookieconsent' ) -- not yet implemented...
		);
	}

	/**
	 * Outputs the 'theme' setting field
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_theme() {
		$available_themes = self::ctmpcc_get_available_themes();

		$current_value = get_option( CTMPCC_OPTION_PREFIX.'theme' );

		echo '<select name="'. CTMPCC_OPTION_PREFIX . 'theme">';
		foreach($available_themes as $theme => $theme_description) {
			echo '<option value="' . $theme . '"'. (( $theme  == $current_value ) ? ' selected="selected"' : '') .'>'.$theme_description.'</option>';
		}
		echo '</select>';
	}

	/**
	 * Sanitizes the input of the 'theme' field
	 *
	 * @return The sanitized value
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_page_field_callback_theme_sanitize($input) {
		/* Check whether the input value is in the list of available themes,
		 * if not, use default value from default value array.
		 */
		return ( in_array( $input, array_keys( self::ctmpcc_get_available_themes() ) ) ) ? $input : self::ctmpcc_default_configuration()['theme'];
	}

	/**
	 * Initializes the settings fields
	 *
	 * @return void
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	function ctmpcc_settings_init() {
		foreach( array_keys(self::ctmpcc_default_configuration()) as $setting_key ) {
			register_setting( CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.$setting_key, array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.$setting_key.'_sanitize' ) );
		}

		add_settings_section( CTMPCC_OPTION_PREFIX.'section_display', 	__( 'Display Settings', 'ctmp-cookieconsent' ), 								array( &$this, 'ctmpcc_settings_page_section_callback' ),  			CTMPCC_OPTION_GROUP);

		add_settings_field( CTMPCC_OPTION_PREFIX.'message', 			__( 'Cookie Message', 'ctmp-cookieconsent' ), 									array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'message' ), 	CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'dismiss', 			__( 'Dismiss Button Caption', 'ctmp-cookieconsent' ), 							array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'dismiss' ), 	CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'learnMore', 			__( 'Caption of the Learn More Link', 'ctmp-cookieconsent' ), 					array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'learnMore' ), 	CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'link', 				__( 'Target Page of the Learn More Link', 'ctmp-cookieconsent' ), 				array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'link' ),		CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'target', 				__( 'Open Learn More Link in the Same or New Page?', 'ctmp-cookieconsent' ), 	array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'target' ), 		CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_display');
		add_settings_field( CTMPCC_OPTION_PREFIX.'theme', 				__( 'How to Display the Cookie Notification?', 'ctmp-cookieconsent' ), 			array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'theme' ), 		CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_display');

		add_settings_section( CTMPCC_OPTION_PREFIX.'section_advanced', 	__( 'Advanced Settings', 'ctmp-cookieconsent' ), 								array( &$this, 'ctmpcc_settings_page_section_callback' ),  			CTMPCC_OPTION_GROUP);

		add_settings_field( CTMPCC_OPTION_PREFIX.'domain', 				__( '(Sub-)Domain for Opt-out Scope', 'ctmp-cookieconsent' ), 					array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'domain' ), 		CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_advanced');
		add_settings_field( CTMPCC_OPTION_PREFIX.'expiryDays', 			__( 'Opt-out Expiry Days', 'ctmp-cookieconsent' ), 								array( &$this, CTMPCC_OPTION_FIELD_CALLBACK_PREFIX.'expiryDays' ), 	CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.'section_advanced');
	}

	/**
	 * Get the setting values
	 *
	 * @return Array of configuration keys and values
	 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
	 * @since 0.1.0
	 */
	 public function ctmpcc_get_configuration() {
		 /* Set current configuration to default */
		 $configuration = self::ctmpcc_default_configuration();

		 /* Load settings from DB, leave default values if not found */
		 foreach(array_keys($configuration) as $setting_key) {
			$configuration[$setting_key] = get_option( CTMPCC_OPTION_PREFIX.$setting_key, $configuration[$setting_key] ); //option_key, default_value
		 }

		 return $configuration;
	 }
}
