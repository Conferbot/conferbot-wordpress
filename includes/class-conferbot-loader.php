<?php
/**
 * Renders the widget loader <script> for wp_head and gathers the live
 * WordPress/WooCommerce context that feeds Conferbot_Context::build().
 */

if (!defined('ABSPATH') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class Conferbot_Loader
{
    /**
     * Pure renderer: settings + prebuilt options -> HTML string.
     */
    public static function render(array $settings, array $options, array $events = array())
    {
        $script_url = esc_url($settings['script_url']);
        $bot_id = esc_js($settings['bot_id']);
        $embed_type = esc_js($settings['embed_type']);
        $json = Conferbot_Context::to_json($options);
        $tracks = self::track_calls($events);

        return <<<HTML
<!-- Conferbot Live Chat -->
<script type="text/javascript">
  (function (d, s, id) {
    var js, el = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.async = true;
    js.src = '{$script_url}';
    js.id = id;
    js.charset = 'UTF-8';
    if (el && el.parentNode) { el.parentNode.insertBefore(js, el); } else { d.head.appendChild(js); }
    js.onload = function () {
      window.ConferbotWidget('{$bot_id}', '{$embed_type}', {$json});{$tracks}
    };
  })(document, 'script', 'conferbot-js');
</script>

HTML;
    }

    /**
     * What the visitor did, for Conferbot's "chats that led to a sale":
     * the order on the thank-you page (once per order), and every AJAX
     * add-to-cart in WooCommerce, which also keeps the cart total current.
     */
    public static function track_calls(array $events)
    {
        $out = '';
        foreach ($events as $event) {
            if (empty($event['name'])) {
                continue;
            }
            $name = self::js((string) $event['name']);
            $props = self::js((object) (isset($event['props']) && is_array($event['props']) ? $event['props'] : array()));
            if (!empty($event['once'])) {
                $key = self::js('cb-track-' . (string) $event['once']);
                $out .= "\n      try { if (!sessionStorage.getItem({$key})) { sessionStorage.setItem({$key}, '1'); window.ConferbotWidget.track({$name}, {$props}); } } catch (e) { window.ConferbotWidget.track({$name}, {$props}); }";
            } else {
                $out .= "\n      window.ConferbotWidget.track({$name}, {$props});";
            }
        }
        if (!empty($events) || function_exists('WC')) {
            $out .= "\n      if (window.jQuery) { window.jQuery(document.body).on('added_to_cart', function () { window.ConferbotWidget.track('add_to_cart'); }); }";
        }
        return $out;
    }

    /** A value as script-safe JSON (strings and objects alike). */
    private static function js($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * The order a WooCommerce thank-you page shows, as a purchase event.
     */
    public static function gather_events()
    {
        $events = array();
        if (function_exists('is_order_received_page') && is_order_received_page() && function_exists('wc_get_order')) {
            $order = wc_get_order(absint(get_query_var('order-received')));
            if ($order) {
                $events[] = array(
                    'name' => 'purchase',
                    'props' => array(
                        'value' => (float) $order->get_total(),
                        'currency' => (string) $order->get_currency(),
                        'order_id' => (string) $order->get_order_number(),
                    ),
                    'once' => 'order-' . $order->get_id(),
                );
            }
        }
        return $events;
    }

    /**
     * Collects site/user/cart context from live WordPress + WooCommerce.
     * Not covered by unit tests (integration surface); kept tiny on purpose.
     */
    public static function gather_context()
    {
        $site = array(
            // Stored HTML-escaped; decoded here, the JSON_HEX flags keep the script safe.
            'name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
            'url' => home_url('/'),
            'language' => get_locale(),
            'currency' => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : '',
        );

        $user = null;
        if (is_user_logged_in()) {
            $wp_user = wp_get_current_user();
            $user = array(
                'id' => $wp_user->ID,
                'name' => $wp_user->display_name,
                'email' => $wp_user->user_email,
            );
        }

        $cart = null;
        if (function_exists('WC') && WC()->cart) {
            $cart = array(
                'total' => (float) WC()->cart->get_cart_contents_total(),
                'item_count' => (int) WC()->cart->get_cart_contents_count(),
            );
        }

        $settings = Conferbot_Settings::get();
        return Conferbot_Context::build($site, CONFERBOT_VERSION, $user, $cart, $settings['identity_secret']);
    }
}
