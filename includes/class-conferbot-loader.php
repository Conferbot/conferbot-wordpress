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
    public static function render(array $settings, array $options)
    {
        $script_url = esc_url($settings['script_url']);
        $bot_id = esc_js($settings['bot_id']);
        $embed_type = esc_js($settings['embed_type']);
        $json = Conferbot_Context::to_json($options);

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
      window.ConferbotWidget('{$bot_id}', '{$embed_type}', {$json});
    };
  })(document, 'script', 'conferbot-js');
</script>

HTML;
    }

    /**
     * Collects site/user/cart context from live WordPress + WooCommerce.
     * Not covered by unit tests (integration surface); kept tiny on purpose.
     */
    public static function gather_context()
    {
        $site = array(
            'name' => get_bloginfo('name'),
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

        return Conferbot_Context::build($site, CONFERBOT_VERSION, $user, $cart);
    }
}
