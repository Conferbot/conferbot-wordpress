<?php
/**
 * Runs when the plugin is deleted from the admin: remove all plugin data.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('conferbot_settings');
