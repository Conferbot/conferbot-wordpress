# Conferbot WordPress Plugin

Embeds the [Conferbot](https://conferbot.com) AI chat widget on a WordPress site or WooCommerce store. Same product as the [PrestaShop module](https://github.com/Conferbot/conferbot-prestashop) and [Shopify app](https://github.com/Conferbot/conferbot-shopify), WordPress-shaped.

## See it in action

Paste the Chatbot ID in Settings > Conferbot, save, and the chat is live:

![Conferbot WordPress demo: paste the bot ID, save, chat appears on the site](docs/demo.gif)

[HD video (MP4)](docs/demo.mp4)

## How it works

The plugin hooks `wp_head` and injects the standard Conferbot loader:

```html
<script src="https://cdn.conferbot.com/dist/v1/widget.min.js" async></script>
<script>
  window.ConferbotWidget("<CHATBOT_ID>", "live_chat", {
    user:       { id, name, email },          // logged-in WP user, if any
    customData: { platform: "wordpress", siteName, siteUrl, siteLanguage,
                  storeCurrency?, cartTotal?, cartItemCount? }   // Woo, when active
  });
</script>
```

The only credential is the **Chatbot ID** (paste the bare ID, the full embed snippet, or a dashboard URL - the ID is extracted automatically), with a one-click **demo bot** that works without an account. Appearance and behavior are configured in the Conferbot dashboard and delivered at runtime.

## Structure

```
conferbot.php                             # plugin bootstrap: hooks only
includes/
├── class-conferbot-bot-id.php            # ID extraction/validation + demo bot
├── class-conferbot-settings.php          # option shape, defaults, sanitize, persistence
├── class-conferbot-visibility.php        # conditional tags -> page type -> visible?
├── class-conferbot-context.php           # ConferbotWidget() options + script-safe JSON
└── class-conferbot-loader.php            # renders the loader; gathers live WP/Woo context
admin/class-conferbot-admin.php           # two-step settings page (nonce + capability checked)
uninstall.php                             # removes plugin options on delete
readme.txt                                # WordPress.org listing copy
tests/                                    # PHPUnit suite (WP functions stubbed)
```

## Tests

23 tests / 66 assertions, green on PHP 7.4 and 8.2, no WordPress install needed:

```bash
docker run --rm -v "$PWD":/mod -v /path/to/phpunit-9.phar:/phpunit.phar:ro \
  -w /mod php:8.2-cli php /phpunit.phar
```

Covered: bot ID extraction (bare/snippet/URL/garbage), settings sanitization (invalid embed types, `javascript:` script URLs, enable-without-ID refusal), WooCommerce-aware page-type resolution with priority rules, per-page visibility, guest/user/cart context building, and script-injection safety of the inlined JSON.

Verified end to end in a dockerized WordPress 6.5: activation, loader render with correct context, logged-in identity (`wp-1`), per-page gating on/off, and the real CDN widget mounting and chatting (see the video above).

## License

GPLv2 or later (WordPress.org requirement).
