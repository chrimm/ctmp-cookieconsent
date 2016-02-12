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
 * Version:           0.9.4
 * Author:            Christoffer T. Timm
 * Author URI:        http://christoffertimm.de
 * Text Domain:       ctmp-cookieconsent
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
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if( ! defined( 'CTMPCC_VER' ) ) {
	define( 'CTMPCC_VER', '0.9.4' );
}

if( ! defined( 'COOKIE_CONSENT_VER') ) {
	define( 'COOKIE_CONSENT_VER', '1.0.9' );
}

if( ! defined( 'COOKIE_CONSENT_PATH') ) {
	define( 'COOKIE_CONSENT_PATH', '//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/'.COOKIE_CONSENT_VER );
}

/*
 * Bootstrap
 */

require_once dirname(__FILE__).'/CTMP_Cookie_Consent.class.php';

/*
 * Activation Hooks *must* be registered and defined in main file!
 */
register_activation_hook(   __FILE__, 'ctmpcc_install' 	 );
register_deactivation_hook( __FILE__, 'ctmpcc_deactivate');

/**
 * Creates a new WP Option and writes default configuration to DB
 *
 * @return void
 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
 * @since 0.1.0
 */
function ctmpcc_install() {
	/* Set configuration to default */
	$default_configuration = CTMP_Cookie_Consent::ctmpcc_default_configuration();

	/* Write default settings to DB and register settings */
	foreach($default_configuration as $conf_key => $conf_val) {
		add_option( CTMPCC_OPTION_PREFIX.$conf_key, $conf_val );
	}
}

/**
 * Unregisters all settings
 *
 * @return void
 * @author Christoffer T. Timm <kontakt@christoffertimm.de>
 * @since 0.9.1
 */
function ctmpcc_deactivate() {
	/* Fetch all setting keys */
	$setting_keys = array_keys( CTMP_Cookie_Consent::ctmpcc_default_configuration() );

	/* Unregister each settings */
	foreach( $setting_keys as $setting_key ) {
		unregister_setting( CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.setting_key );
	}
}

/* Instantiate our Class */
$CTMP_Cookie_Consent = CTMP_Cookie_Consent::ctmpcc_get_instance();
