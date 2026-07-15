<?php
/**
 * Single home for the plugin's option shape, defaults and persistence.
 * Everything lives under one 'conferbot_settings' option row.
 */

if (!defined('ABSPATH') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class Conferbot_Settings
{
    const OPTION_KEY = 'conferbot_settings';
    const DEFAULT_SCRIPT_URL = 'https://cdn.conferbot.com/dist/v1/widget.min.js';

    public static $embed_types = array('live_chat', 'popup_chat');

    /** Page-type ids used by the visibility checkboxes. */
    public static $page_types = array('home', 'posts', 'pages', 'shop', 'cart', 'checkout', 'account', 'other');

    public static function defaults()
    {
        $defaults = array(
            'enabled' => false,
            'bot_id' => '',
            'embed_type' => 'live_chat',
            'script_url' => self::DEFAULT_SCRIPT_URL,
        );
        foreach (self::$page_types as $page) {
            $defaults['show_on_' . $page] = true;
        }
        return $defaults;
    }

    public static function get()
    {
        $stored = get_option(self::OPTION_KEY, array());
        return array_merge(self::defaults(), is_array($stored) ? $stored : array());
    }

    /**
     * Sanitizes raw form input into a safe settings array. Pure so it is
     * unit-testable; persistence happens in save().
     */
    public static function sanitize(array $input)
    {
        $bot_id = Conferbot_Bot_Id::extract(isset($input['bot_id']) ? $input['bot_id'] : '');

        $embed_type = isset($input['embed_type']) ? $input['embed_type'] : 'live_chat';
        if (!in_array($embed_type, self::$embed_types, true)) {
            $embed_type = 'live_chat';
        }

        $script_url = isset($input['script_url']) ? trim((string) $input['script_url']) : '';
        if ($script_url === '' || !preg_match('#^https?://#i', $script_url)) {
            $script_url = self::DEFAULT_SCRIPT_URL;
        }

        $clean = array(
            'enabled' => !empty($input['enabled']) && Conferbot_Bot_Id::is_valid($bot_id),
            'bot_id' => $bot_id,
            'embed_type' => $embed_type,
            'script_url' => $script_url,
        );
        foreach (self::$page_types as $page) {
            $clean['show_on_' . $page] = !empty($input['show_on_' . $page]);
        }
        return $clean;
    }

    public static function save(array $input)
    {
        $clean = self::sanitize($input);
        update_option(self::OPTION_KEY, $clean);
        return $clean;
    }
}
