# Conferbot WordPress Plugin

Embeds the [Conferbot](https://conferbot.com) AI chat widget on a WordPress site or WooCommerce store. Same product as the [PrestaShop module](https://github.com/Conferbot/conferbot-prestashop) and [Shopify app](https://github.com/Conferbot/conferbot-shopify), WordPress-shaped.

## See it in action

Upload the zip, activate it, paste the Chatbot ID under Settings > Conferbot, save, and the chat is live (recorded on WordPress 7.1.3):

[![Conferbot on WordPress: upload the plugin, activate, paste the bot ID, save, open the site and chat](docs/media/install.gif)](https://github.com/Conferbot/conferbot-wordpress/releases/download/v1.1.0/install.mp4)

## Install

### Requirements

- WordPress 5.8 or later (tested up to 7.1), PHP 7.2 or later.
- WooCommerce is optional. With it, the plugin also passes the cart and reports orders.
- A Conferbot account with a bot ([app.conferbot.com](https://app.conferbot.com)), or use the built-in demo bot to try it first.
- The visitor's browser loads `https://cdn.conferbot.com/dist/v1/widget.min.js` and talks to `wdt.conferbot.com`. If you run a Content Security Policy, allow both.

### From WordPress.org

The plugin is not in the WordPress.org directory yet. Once it is: **Plugins > Add New Plugin**, search for **Conferbot**, then **Install Now** and **Activate**.

### From a zip (manual)

1. Get the zip: download `conferbot.zip` from the [latest release](https://github.com/Conferbot/conferbot-wordpress/releases/latest), or build it from a checkout with `git archive --format=zip --prefix=conferbot/ -o conferbot.zip HEAD`. The zip must contain a single `conferbot/` folder with `conferbot.php` inside it.
2. In WordPress go to **Plugins > Add New Plugin > Upload Plugin**, choose `conferbot.zip`, and click **Install Now**.
3. Click **Activate Plugin**.

Over FTP or SSH instead: unzip into `wp-content/plugins/` so you have `wp-content/plugins/conferbot/conferbot.php`, then activate it under **Plugins**. With WP-CLI: `wp plugin install conferbot.zip --activate`.

### Find your Chatbot ID

The Chatbot ID is a 24-character code such as `64a1b2c3d4e5f6a7b8c9d0e1`.

1. Sign in at [app.conferbot.com](https://app.conferbot.com) and open your bot.
2. Click **Share**, then **Embed into your website**.
3. The ID is the first value in the code: `window.ConferbotWidget("64a1b2c3d4e5f6a7b8c9d0e1", "live_chat")`. It is also in the browser's address bar: `.../chatbot/64a1b2c3d4e5f6a7b8c9d0e1/embed`.

You can copy the whole embed code instead; the plugin finds the ID in it.

### Turn it on

1. Go to **Settings > Conferbot** (or click **Settings** under Conferbot on the Plugins page).
2. **Step 1:** paste the Chatbot ID, the embed code or the dashboard URL. To try it without an account, click **Try our demo bot**.
3. **Step 2:** turn on **Show the chat bubble on my site**.
4. Click **Save - start chatting with my visitors**. The green banner confirms it is live.

## Settings

| Setting | Where | What it does |
|---|---|---|
| Chatbot ID | Step 1 | Which bot runs on the site. Anything pasted is reduced to the 24-character ID; nothing is saved if no ID is found. |
| Show the chat bubble on my site | Step 2 | The on/off switch. Off means no Conferbot code at all on the site. It cannot be switched on without an ID. |
| How the chat appears | Advanced | **Floating chat bubble** (`live_chat`, the default) or **Popup window** (`popup_chat`). |
| Show the chat on these pages | Advanced | Home page, blog posts, pages, WooCommerce shop and products, cart, checkout, my account, and all other pages. All are on by default. |
| Identity verification secret | Advanced | Optional. From Conferbot workspace settings > Security > Identity verification. Signs logged-in customers so Conferbot trusts their name and email (see below). Write-only: leave it blank to keep the saved one, or tick **Remove the saved secret**. |
| Widget script URL | Advanced | Leave as `https://cdn.conferbot.com/dist/v1/widget.min.js` unless Conferbot support says otherwise. |

The look of the chat (colours, launcher, greeting, languages, flows, AI agent, live chat hours) is set per bot in the Conferbot dashboard, not in WordPress.

## Check that it works

1. Open your site in a private window, so you see it as a visitor does.
2. The chat bubble appears in the bottom-right corner. Click it, start the chat and send a message.
3. Your message appears in the Conferbot dashboard under the bot's conversations (and in the live chat inbox if your team is online).
4. To check the code is on the page: view the page source and search for `conferbot-js`. It is in the `<head>` once, followed by `window.ConferbotWidget('<your ID>', ...)`.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| No bubble, and `conferbot-js` is not in the page source | The switch is off, no valid ID is saved, or the page type is unticked under **Show the chat on these pages**. Also clear any page cache (WP Rocket, W3 Total Cache, LiteSpeed, Cloudflare APO) after saving. |
| `conferbot-js` is in the source but no bubble | A script blocker, a Content Security Policy without `cdn.conferbot.com` and `wdt.conferbot.com`, or a theme that strips `wp_head`. Check the browser console. Optimisation plugins that delay or combine JavaScript can also hold it back; exclude `cdn.conferbot.com`. |
| "We could not find a Chatbot ID in what you pasted" | What you pasted has no 24-character code. Copy it again from Share > Embed into your website. |
| The bubble shows a different bot | Another copy of the embed code is still in the theme or a header-scripts plugin. Remove it: the plugin skips loading if a `conferbot-js` script is already on the page. |
| Logged-in customers show as unverified | Paste the identity verification secret under Advanced settings, and make sure it matches the one in your Conferbot workspace. |

Still stuck? Write to support@conferbot.com.

## Uninstall

- **Turn it off for now:** switch off step 2 and save. The settings are kept.
- **Deactivate:** Plugins > Conferbot > **Deactivate**. Nothing is loaded on the site; the settings are kept.
- **Delete:** deactivate it, then click **Delete**. `uninstall.php` removes the `conferbot_settings` option, including the identity secret. Your bot and its conversations stay in your Conferbot account.

## How it works

The plugin hooks `wp_head` and injects the standard Conferbot loader. The widget script loads without blocking the page, and `ConferbotWidget` is called once it has loaded:

```html
<script>
  (function (d, s, id) {
    var js = d.createElement(s); js.async = true; js.id = id;
    js.src = "https://cdn.conferbot.com/dist/v1/widget.min.js";
    d.head.appendChild(js);
    js.onload = function () {
      window.ConferbotWidget("<CHATBOT_ID>", "live_chat", {
        user:       { id: "wp-7", name, email, hash },   // logged-in WP user, if any
        customData: { platform: "wordpress", siteName, siteUrl, siteLanguage,
                      storeCurrency?, cartTotal?, cartItemCount? }   // Woo, when active
      });
      // On the WooCommerce thank-you page, once per order:
      window.ConferbotWidget.track("purchase", { value, currency, order_id });
    };
  })(document, "script", "conferbot-js");
</script>
```

The only credential is the **Chatbot ID**. Paste the bare ID, the full embed code from the dashboard (your bot > Share > Embed), or a dashboard URL, and the ID is extracted automatically. A one-click **demo bot** works without an account.

Everything visitors see is set per bot in the Conferbot dashboard and delivered at runtime, with nothing to configure in WordPress: flows and AI agents, live chat with handover to your team (queue position, leave a message when nobody is free, read receipts, file sharing, post-chat rating), the home screen, themes and dark mode, colours, launcher and mascot, greetings and languages.

### Identity verification (optional)

With a secret from Conferbot (workspace settings > Security > Identity verification) pasted under Advanced settings, each logged-in customer is sent with `hash = HMAC-SHA256(secret, user.id)`, computed on your server. Conferbot then trusts their name and email, and can link their chats to their contact. The secret is stored in your WordPress options and never sent to the browser. Without it, the customer's details are still passed but marked unverified, and if your workspace *requires* verification they are dropped.

### Sales tracking

On the WooCommerce order-received page the plugin reports the order (`purchase`, with value, currency and order number) once per order, and every AJAX add-to-cart as `add_to_cart`. Conferbot credits a sale to a chat that happened within the week before it.

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

26 tests, no WordPress install needed:

```bash
docker run --rm -v "$PWD":/mod -v /path/to/phpunit-10.phar:/phpunit.phar:ro \
  -w /mod php:8.2-cli php /phpunit.phar
```

Covered: bot ID extraction (bare/snippet/URL/garbage), settings sanitization (invalid embed types, `javascript:` script URLs, enable-without-ID refusal, the write-only identity secret), WooCommerce-aware page-type resolution with priority rules, per-page visibility, guest/user/cart context building, the identity hash, purchase tracking once per order, and script-injection safety of the inlined JSON.

Verified end to end in a dockerized WordPress 7.1 with WooCommerce: activation, loader render with correct context, logged-in identity (`wp-1`), per-page gating on/off, and the real CDN widget mounting and chatting. Re-checked on WordPress 7.1.3 (October 2026): upload and activate, save with nonce, refusal for non-admins, the on/off switch, script-safe site names, and the widget opening and chatting (the GIF above).

## License

GPLv2 or later (WordPress.org requirement).
