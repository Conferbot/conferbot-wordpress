<?php
/**
 * Bot ID parsing and validation. A Conferbot bot ID is 24 hex characters;
 * extraction is forgiving (accepts a full embed snippet or URL), validation
 * is strict.
 */

if (!defined('ABSPATH') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class Conferbot_Bot_Id
{
    /** Public demo bot - works out of the box, no Conferbot account needed. */
    const DEMO_BOT_ID = '691c970890527a0468f9b2c9';

    public static function extract($text)
    {
        if (preg_match('/[a-f0-9]{24}/i', (string) $text, $m)) {
            return $m[0];
        }
        return '';
    }

    public static function is_valid($id)
    {
        return (bool) preg_match('/^[a-f0-9]{24}$/i', (string) $id);
    }
}
