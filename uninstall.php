<?php

// Exit if uninstall not called from WordPress.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

delete_option('dikg_settings');

