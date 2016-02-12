<?php

/* If uninstall is not called from WordPress, exit */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

require_once dirname(__FILE__).'/CTMP_Cookie_Consent.class.php';

$setting_names = array_keys( CTMP_Cookie_Consent::ctmpcc_default_configuration() );

foreach( $setting_names as $setting_name ) {
    /* Unregister and delete each setting */
    unregister_setting( CTMPCC_OPTION_GROUP, CTMPCC_OPTION_PREFIX.$setting_name );
    delete_option( CTMPCC_OPTION_PREFIX.$setting_name );
}
