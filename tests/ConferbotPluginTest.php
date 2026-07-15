<?php

use PHPUnit\Framework\TestCase;

class ConferbotPluginTest extends TestCase
{
    const ID = '691c970890527a0468f9b2c9';

    protected function setUp(): void
    {
        $GLOBALS['__options'] = array();
    }

    // ---- bot id --------------------------------------------------------------

    public function testExtractHandlesBareIdSnippetUrlAndGarbage()
    {
        $this->assertSame(self::ID, Conferbot_Bot_Id::extract(self::ID));
        $this->assertSame(self::ID, Conferbot_Bot_Id::extract("  " . self::ID . " \n"));
        $this->assertSame(self::ID, Conferbot_Bot_Id::extract('window.ConferbotWidget("' . self::ID . '", "live_chat")'));
        $this->assertSame(self::ID, Conferbot_Bot_Id::extract('https://app.conferbot.com/a/ws/1/u/2/chatbot/' . self::ID . '/embed'));
        $this->assertSame('', Conferbot_Bot_Id::extract('hello'));
        $this->assertSame('', Conferbot_Bot_Id::extract(''));
        $this->assertSame('', Conferbot_Bot_Id::extract(null));
    }

    public function testIsValidIsStrict()
    {
        $this->assertTrue(Conferbot_Bot_Id::is_valid(self::ID));
        $this->assertTrue(Conferbot_Bot_Id::is_valid(strtoupper(self::ID)));
        $this->assertFalse(Conferbot_Bot_Id::is_valid(substr(self::ID, 0, 23)));
        $this->assertFalse(Conferbot_Bot_Id::is_valid(self::ID . '0'));
        $this->assertFalse(Conferbot_Bot_Id::is_valid(' ' . self::ID));
        $this->assertFalse(Conferbot_Bot_Id::is_valid(''));
    }

    public function testDemoBotIdIsValidAndCanonical()
    {
        $this->assertSame('691c970890527a0468f9b2c9', Conferbot_Bot_Id::DEMO_BOT_ID);
        $this->assertTrue(Conferbot_Bot_Id::is_valid(Conferbot_Bot_Id::DEMO_BOT_ID));
    }

    // ---- settings ----------------------------------------------------------------

    public function testDefaultsAreDisabledWithAllPagesVisible()
    {
        $s = Conferbot_Settings::get();
        $this->assertFalse($s['enabled']);
        $this->assertSame('', $s['bot_id']);
        $this->assertSame('live_chat', $s['embed_type']);
        $this->assertSame(Conferbot_Settings::DEFAULT_SCRIPT_URL, $s['script_url']);
        foreach (Conferbot_Settings::$page_types as $page) {
            $this->assertTrue($s['show_on_' . $page]);
        }
    }

    public function testSanitizeExtractsBotIdFromPastedSnippet()
    {
        $clean = Conferbot_Settings::sanitize(array(
            'enabled' => '1',
            'bot_id' => 'js.onload: window.ConferbotWidget("' . self::ID . '")',
        ));
        $this->assertSame(self::ID, $clean['bot_id']);
        $this->assertTrue($clean['enabled']);
    }

    public function testSanitizeRefusesEnableWithoutValidBotId()
    {
        $clean = Conferbot_Settings::sanitize(array('enabled' => '1', 'bot_id' => 'nope'));
        $this->assertFalse($clean['enabled']);
        $this->assertSame('', $clean['bot_id']);
    }

    public function testSanitizeFallsBackOnBadEmbedTypeAndScriptUrl()
    {
        $clean = Conferbot_Settings::sanitize(array(
            'bot_id' => self::ID,
            'embed_type' => 'fullpage_chat',
            'script_url' => 'javascript:alert(1)',
        ));
        $this->assertSame('live_chat', $clean['embed_type']);
        $this->assertSame(Conferbot_Settings::DEFAULT_SCRIPT_URL, $clean['script_url']);
    }

    public function testSaveRoundTripsThroughOptions()
    {
        // Checkbox form contract: absent key means unchecked means off.
        Conferbot_Settings::save(array('enabled' => '1', 'bot_id' => self::ID, 'show_on_home' => '1'));
        $s = Conferbot_Settings::get();
        $this->assertTrue($s['enabled']);
        $this->assertSame(self::ID, $s['bot_id']);
        $this->assertTrue($s['show_on_home']);
        $this->assertFalse($s['show_on_checkout']);
    }

    public function testGetSurvivesCorruptStoredOption()
    {
        update_option(Conferbot_Settings::OPTION_KEY, 'corrupt-string');
        $s = Conferbot_Settings::get();
        $this->assertFalse($s['enabled']);
    }

    // ---- visibility ---------------------------------------------------------------

    public function pageTypeProvider()
    {
        return array(
            'woo cart wins over page' => array(array('woo_cart' => true, 'page' => true), 'cart'),
            'woo checkout wins over page' => array(array('woo_checkout' => true, 'page' => true), 'checkout'),
            'woo account wins over page' => array(array('woo_account' => true, 'page' => true), 'account'),
            'woo shop' => array(array('woo_shop' => true), 'shop'),
            'front page' => array(array('front_page' => true), 'home'),
            'single post' => array(array('single_post' => true), 'posts'),
            'regular page' => array(array('page' => true), 'pages'),
            'nothing set' => array(array(), 'other'),
        );
    }

    /** @dataProvider pageTypeProvider */
    public function testCurrentPageType($flags, $expected)
    {
        $this->assertSame($expected, Conferbot_Visibility::current_page_type($flags));
    }

    public function testVisibilityRespectsSettingsAndDefaultsToVisible()
    {
        $this->assertFalse(Conferbot_Visibility::is_visible('checkout', array('show_on_checkout' => false)));
        $this->assertTrue(Conferbot_Visibility::is_visible('home', array('show_on_home' => true)));
        $this->assertTrue(Conferbot_Visibility::is_visible('future_type', array()));
    }

    // ---- context --------------------------------------------------------------------

    public function testGuestContextHasSiteDataAndNoUser()
    {
        $o = Conferbot_Context::build(array('name' => 'My Blog', 'url' => 'https://b.example/', 'language' => 'en_US'), '1.0.0');
        $this->assertArrayNotHasKey('user', $o);
        $this->assertSame('wordpress', $o['customData']['platform']);
        $this->assertSame('My Blog', $o['customData']['siteName']);
        $this->assertArrayNotHasKey('storeCurrency', $o['customData']);
    }

    public function testLoggedInUserAndWooCartAppearInContext()
    {
        $o = Conferbot_Context::build(
            array('name' => 'Shop', 'url' => 'https://s.example/', 'language' => 'en_US', 'currency' => 'USD'),
            '1.0.0',
            array('id' => 7, 'name' => 'Jane Doe', 'email' => 'jane@example.com'),
            array('total' => '25.50', 'item_count' => '2')
        );
        $this->assertSame(array('id' => 'wp-7', 'name' => 'Jane Doe', 'email' => 'jane@example.com'), $o['user']);
        $this->assertSame('USD', $o['customData']['storeCurrency']);
        $this->assertSame(25.5, $o['customData']['cartTotal']);
        $this->assertSame(2, $o['customData']['cartItemCount']);
    }

    public function testUserWithoutIdIsGuest()
    {
        $o = Conferbot_Context::build(array(), '1.0.0', array('name' => 'X', 'email' => 'x@y.z'));
        $this->assertArrayNotHasKey('user', $o);
    }

    public function testJsonIsScriptContextSafe()
    {
        $o = Conferbot_Context::build(array('name' => '</script><script>alert(1)'), '1.0.0', array(
            'id' => 1, 'name' => '"; window.evil = 1; "', 'email' => "o'brien@example.com",
        ));
        $json = Conferbot_Context::to_json($o);
        $this->assertStringNotContainsString('</script>', $json);
        $this->assertStringNotContainsString('<', $json);
        $this->assertStringNotContainsString("'", $json);
        $decoded = json_decode($json, true);
        $this->assertStringContainsString('alert(1)', $decoded['customData']['siteName']);
    }

    // ---- loader -----------------------------------------------------------------------

    public function testLoaderRendersScriptWithBotIdAndOptions()
    {
        $settings = array_merge(Conferbot_Settings::defaults(), array('bot_id' => self::ID, 'embed_type' => 'live_chat', 'enabled' => true));
        $html = Conferbot_Loader::render($settings, Conferbot_Context::build(array('name' => 'Site'), '1.0.0'));
        $this->assertStringContainsString("window.ConferbotWidget('" . self::ID . "', 'live_chat'", $html);
        $this->assertStringContainsString(Conferbot_Settings::DEFAULT_SCRIPT_URL, $html);
        $this->assertStringContainsString("'conferbot-js'", $html);
        $this->assertStringContainsString('"platform":"wordpress"', $html);
    }
}
