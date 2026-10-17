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

// The script/style API: calls are recorded so tests can check what was queued.
$GLOBALS['__enqueued'] = array();

function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $args = array())
{
    $GLOBALS['__enqueued'][] = array('script', $handle, $src, $deps, $ver, $args);
}

function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all')
{
    $GLOBALS['__enqueued'][] = array('style', $handle, $src, $deps, $ver, $media);
}

function wp_add_inline_script($handle, $data, $position = 'after')
{
    $GLOBALS['__enqueued'][] = array('inline', $handle, $data, $position);
    return true;
}

define('CONFERBOT_VERSION', '0.0.0-test');
define('CONFERBOT_PLUGIN_URL', 'https://example.test/wp-content/plugins/conferbot/');

require_once __DIR__ . '/../includes/class-conferbot-bot-id.php';
require_once __DIR__ . '/../includes/class-conferbot-settings.php';
require_once __DIR__ . '/../includes/class-conferbot-visibility.php';
require_once __DIR__ . '/../includes/class-conferbot-context.php';
require_once __DIR__ . '/../includes/class-conferbot-loader.php';
require_once __DIR__ . '/../admin/class-conferbot-admin.php';
