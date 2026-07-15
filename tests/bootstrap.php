<?php
/**
 * Test bootstrap: loads plugin classes without WordPress. The handful of WP
 * functions the pure classes touch are stubbed here.
 */

define('CONFERBOT_TEST_SUITE', true);

$GLOBALS['__options'] = array();

function get_option($key, $default = false)
{
    return array_key_exists($key, $GLOBALS['__options']) ? $GLOBALS['__options'][$key] : $default;
}

function update_option($key, $value)
{
    $GLOBALS['__options'][$key] = $value;
    return true;
}

function esc_url($url)
{
    return filter_var($url, FILTER_SANITIZE_URL);
}

function esc_js($text)
{
    return str_replace(array('"', "'", '<', '>'), array('\"', "\'", '\x3c', '\x3e'), (string) $text);
}

require_once __DIR__ . '/../includes/class-conferbot-bot-id.php';
require_once __DIR__ . '/../includes/class-conferbot-settings.php';
require_once __DIR__ . '/../includes/class-conferbot-visibility.php';
require_once __DIR__ . '/../includes/class-conferbot-context.php';
require_once __DIR__ . '/../includes/class-conferbot-loader.php';
