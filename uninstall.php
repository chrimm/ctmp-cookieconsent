<?php

/* If uninstall is not called from WordPress, exit */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

$setting_names = array_keys( CTMP_Cookie_Consent::ctmpcc_default_configuration() );

foreach( $setting_names as $setting_name) {
    unregister_setting( CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.$setting_name );
}

/* For site options in Multisite */
delete_site_option( $option_name );
