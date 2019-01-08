<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'dikg_settings' );
delete_option( 'dikg_license_key' );
delete_option( 'dikg_license_status' );