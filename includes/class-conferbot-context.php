<?php
/**
 * Builds the third argument for window.ConferbotWidget(botId, embedType, options):
 * options.user for the logged-in WordPress user, options.customData for site
 * and WooCommerce cart context. Pure data-in/data-out.
 */

if (!defined('ABSPATH') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class Conferbot_Context
{
    /**
     * @param array $site keys: name, url, language, currency
     * @param string $version plugin version
     * @param array|null $user keys: id, name, email (null = guest)
     * @param array|null $cart keys: total, item_count (null = no Woo/empty)
     * @return array
     */
    public static function build(array $site, $version, $user = null, $cart = null)
    {
        $options = array('customData' => array(
            'platform' => 'wordpress',
            'pluginVersion' => (string) $version,
            'siteName' => isset($site['name']) ? (string) $site['name'] : '',
            'siteUrl' => isset($site['url']) ? (string) $site['url'] : '',
            'siteLanguage' => isset($site['language']) ? (string) $site['language'] : '',
        ));
        if (!empty($site['currency'])) {
            $options['customData']['storeCurrency'] = (string) $site['currency'];
        }

        if (is_array($user) && !empty($user['id'])) {
            $options['user'] = array(
                'id' => 'wp-' . (int) $user['id'],
                'name' => isset($user['name']) ? trim((string) $user['name']) : '',
                'email' => isset($user['email']) ? (string) $user['email'] : '',
            );
        }

        if (is_array($cart)) {
            if (isset($cart['total'])) {
                $options['customData']['cartTotal'] = (float) $cart['total'];
            }
            if (isset($cart['item_count'])) {
                $options['customData']['cartItemCount'] = (int) $cart['item_count'];
            }
        }

        return $options;
    }

    /** JSON safe to inline inside a <script> block (user-controlled names/emails). */
    public static function to_json(array $options)
    {
        return json_encode($options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
}
