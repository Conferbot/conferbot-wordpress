<?php
/**
 * Maps WordPress/WooCommerce conditional-tag results to the plugin's page
 * types and decides widget visibility from the settings. Pure functions:
 * the caller evaluates the conditional tags and passes booleans in.
 */

if (!defined('ABSPATH') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class Conferbot_Visibility
{
    /**
     * @param array $flags front_page, single_post, page, woo_shop, woo_cart,
     *                     woo_checkout, woo_account (booleans)
     * @return string one of Conferbot_Settings::$page_types
     */
    public static function current_page_type(array $flags)
    {
        // Woo checks first: cart/checkout/account are technically pages too.
        if (!empty($flags['woo_cart'])) {
            return 'cart';
        }
        if (!empty($flags['woo_checkout'])) {
            return 'checkout';
        }
        if (!empty($flags['woo_account'])) {
            return 'account';
        }
        if (!empty($flags['woo_shop'])) {
            return 'shop';
        }
        if (!empty($flags['front_page'])) {
            return 'home';
        }
        if (!empty($flags['single_post'])) {
            return 'posts';
        }
        if (!empty($flags['page'])) {
            return 'pages';
        }
        return 'other';
    }

    public static function is_visible($page_type, array $settings)
    {
        $key = 'show_on_' . $page_type;
        // Missing key (future page type) defaults to visible.
        return !array_key_exists($key, $settings) || !empty($settings[$key]);
    }
}
