<?php
/**
 * Plugin Name: Conferbot - AI Chatbot, Live Chat & Customer Support
 * Plugin URI: https://conferbot.com/integrations/wordpress
 * Description: Turn visitors into customers with an AI chatbot and live chat widget. Automate 24/7 support, capture leads and hand over to human agents - set up in under a minute, WooCommerce aware.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.2
 * Author: Conferbot
 * Author URI: https://conferbot.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: conferbot
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CONFERBOT_VERSION', '1.0.0');
define('CONFERBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CONFERBOT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CONFERBOT_PLUGIN_DIR . 'includes/class-conferbot-bot-id.php';
require_once CONFERBOT_PLUGIN_DIR . 'includes/class-conferbot-settings.php';
require_once CONFERBOT_PLUGIN_DIR . 'includes/class-conferbot-visibility.php';
require_once CONFERBOT_PLUGIN_DIR . 'includes/class-conferbot-context.php';
require_once CONFERBOT_PLUGIN_DIR . 'includes/class-conferbot-loader.php';
require_once CONFERBOT_PLUGIN_DIR . 'admin/class-conferbot-admin.php';

// ---- Admin ------------------------------------------------------------------

add_action('admin_menu', function () {
    add_options_page(
        __('Conferbot Live Chat', 'conferbot'),
        __('Conferbot', 'conferbot'),
        'manage_options',
        'conferbot',
        array('Conferbot_Admin', 'render_page')
    );
});

add_action('admin_post_conferbot_save', array('Conferbot_Admin', 'handle_save'));

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    array_unshift($links, '<a href="' . esc_url(admin_url('options-general.php?page=conferbot')) . '">'
        . esc_html__('Settings', 'conferbot') . '</a>');
    return $links;
});

// ---- Front office --------------------------------------------------------------

add_action('wp_head', function () {
    $settings = Conferbot_Settings::get();

    if (empty($settings['enabled']) || !Conferbot_Bot_Id::is_valid($settings['bot_id'])) {
        return;
    }

    $page_type = Conferbot_Visibility::current_page_type(array(
        'front_page' => is_front_page(),
        'single_post' => is_single(),
        'page' => is_page(),
        'woo_shop' => function_exists('is_shop') && (is_shop() || (function_exists('is_product') && is_product()) || (function_exists('is_product_category') && is_product_category())),
        'woo_cart' => function_exists('is_cart') && is_cart(),
        'woo_checkout' => function_exists('is_checkout') && is_checkout(),
        'woo_account' => function_exists('is_account_page') && is_account_page(),
    ));

    if (!Conferbot_Visibility::is_visible($page_type, $settings)) {
        return;
    }

    echo Conferbot_Loader::render($settings, Conferbot_Loader::gather_context()); // phpcs:ignore WordPress.Security.EscapeOutput -- script tag built from validated/JSON_HEX-encoded values
});
