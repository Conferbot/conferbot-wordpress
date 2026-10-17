# WordPress.org plugin directory: submission kit

Everything needed to list **Conferbot - AI Chatbot, Live Chat & Customer Support** 1.1.1 in the WordPress.org plugin directory. Checked against the official docs on 2026-10-17.

## 1. Where to submit

| What | Where |
| --- | --- |
| Account | A free **WordPress.org account** (https://login.wordpress.org/register) with **two-factor authentication on**. 2FA has been required for plugin owners and committers since 1 Oct 2024. The username becomes the `Contributors:` value in `readme.txt`, which says `conferbot` today. Register that exact username, or change the header to the username you use. |
| Submission form | https://wordpress.org/plugins/developers/add/ (upload the zip, confirm the guidelines) |
| Rules | https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/ |
| Review checklist the team uses | https://make.wordpress.org/plugins/handbook/performing-reviews/review-checklist/ |
| After approval | SVN repository `https://plugins.svn.wordpress.org/<slug>/` |
| Fees | None. The directory is free. |

## 2. Current requirements (late 2026) and where we stand

| Requirement | Status |
| --- | --- |
| GPL-compatible licence | OK. `LICENSE` is GPL v2, and the headers say `GPLv2 or later`. |
| Plugin Check passes with no errors (it runs automatically on upload, and errors block the submission) | Run the [Plugin Check](https://wordpress.org/plugins/plugin-check/) plugin on a test site first, on the 1.1.1 zip. |
| No hard-coded `<script>`/`<style>`: use `wp_enqueue_*()` | OK since 1.1.1 (#9). The loader is `wp_enqueue_script` (same URL, in the head) plus `wp_add_inline_script(..., 'before')` for the boot call, with `async` and `charset` added by a `script_loader_tag` filter. The settings page's CSS and JS are files under `assets/`, enqueued on its own page only. A unit test fails if a `<script>`/`<style>` tag or `style=""` attribute comes back into the PHP. |
| Third-party services documented in readme: what is sent, when, with links to the service's terms and privacy policy | Done in this kit: `readme.txt` now has `== External services ==`. |
| No contact with external servers without consent (guideline 7) | OK. Nothing loads until the admin enters a bot ID and turns the chat on. |
| Service JS may load remotely; any other JS must be bundled (guideline 8) | OK. Only the Conferbot widget (the service itself) loads from `cdn.conferbot.com`. |
| Readme headers: short description of 150 characters or fewer, no more than 5 tags, `Tested up to` set to a released major, `Stable tag` matching the version | OK: 140 characters, 5 tags, `Tested up to: 7.1` (7.1.3 is the current release), `Stable tag: 1.1.1`, `Requires PHP: 7.2`, `Requires at least: 5.8`. |
| No trademark as the first word of the name or slug (guideline 17) | OK. The name starts with "Conferbot", and "WordPress"/"WooCommerce" appear only descriptively. |
| Slug | The directory builds the slug from `Plugin Name:`, which would give `conferbot-ai-chatbot-live-chat-customer-support`. The text domain is `conferbot`, and Plugin Check flags a mismatch. **Ask for the slug `conferbot`** (free as of today) when you submit, in the form's notes or in a reply to the review email before approval. A slug can never be changed after approval. |

Review time: the official target is about 5 business days (the handbook says up to 14). In 2026 the queue rose to about 1,050 plugins in April, then dropped again. Add `plugins@wordpress.org` to your safe senders.

## 3. Blockers

1. The `conferbot` WordPress.org username (or a change to `Contributors:`) and 2FA, both done by the owner.
2. None on fees, tax or legal: the plugin is free, and the directory takes no payment.

## 4. Listing copy (ready to paste)

On WordPress.org the listing *is* `readme.txt`: name, short description, description, FAQ, screenshots and changelog all come from it. Nothing is typed into a form apart from the zip upload. For reference:

- **Name** (from the `Plugin Name:` header and the readme title): `Conferbot - AI Chatbot, Live Chat & Customer Support`
- **Short description** (140 of 150 characters): `Turn visitors into customers with an AI chatbot and live chat widget. 24/7 support, lead capture, human handover - set up in under a minute.`
- **Long description, key benefits, FAQ:** already in `readme.txt` under `== Description ==` and `== Frequently Asked Questions ==`.
- **Tags** (5 maximum): `chatbot, live chat, ai chatbot, customer support, chat widget`
- **Pricing text** (already in the description): "The plugin is free. A Conferbot account is needed to run your own bot; the free plan is enough to start (600 conversations a month, no card). A public demo bot lets you try the widget before signing up."
- **Support:** the WordPress.org support forum for the plugin (created automatically), plus `support@conferbot.com`, the address already used in the plugin and on conferbot.com/contact.
- **Privacy policy:** https://www.conferbot.com/privacy-policy (HTTP 200)
- **Terms:** https://www.conferbot.com/terms-of-service (HTTP 200)
- **Plugin URI** in `conferbot.php`: https://www.conferbot.com/chatbot/wordpress (HTTP 200, the canonical WordPress page) since 1.1.1. The old `https://conferbot.com/integrations/wordpress` returns 404.

### Data and GDPR statement

This statement matches the code (`includes/class-conferbot-context.php`, `includes/class-conferbot-loader.php`) and is the same text as the readme's External services section:

- The plugin stores only its own settings in one option row, `conferbot_settings`: the bot ID, the display options, and the identity verification secret if you set one. Deleting the plugin removes that row (`uninstall.php`).
- When the chat is on and visible on a page, the visitor's browser loads `https://cdn.conferbot.com/dist/v1/widget.min.js`. The page passes the widget:
  - the Chatbot ID and plugin version
  - the site name, URL and locale, plus the WooCommerce currency
  - for **logged-in users only**: their WordPress user ID (`wp-<id>`), display name and email, and if a secret is set, an HMAC-SHA256 of the ID. The secret itself never reaches the browser.
  - with WooCommerce: the cart total, the item count and add-to-cart events, and on the order-received page the order's total, currency and number (once per order).
- Whatever the visitor types or uploads in the chat goes to Conferbot as the conversation.
- Nothing is sent while the chat is off, has no valid ID, or is hidden on that page type.
- The plugin sets no cookies of its own. The widget is Conferbot's, and its storage is covered by the Conferbot privacy policy.
- Site owners who show the chat to logged-in users should mention Conferbot as a processor in their own privacy policy.

## 5. Reviewer test instructions

Paste this into the submission notes or the review reply:

> 1. Install the zip and activate "Conferbot - AI Chatbot, Live Chat & Customer Support".
> 2. Go to Settings > Conferbot.
> 3. Click "Try our demo bot", or paste the public demo Chatbot ID `691c970890527a0468f9b2c9` (a live "Startup Consultation Chatbot"). No Conferbot account is needed.
> 4. Turn "Show the chat bubble on my site" on and click Save.
> 5. Open the site's front end. A chat bubble appears bottom right. Click it and reply to the bot.
> 6. Optional, with WooCommerce: log in as a customer. The bot greets the customer by name, and the cart total is passed as context. Turning the switch off removes the widget, and deleting the plugin removes the `conferbot_settings` option.

## 6. Assets (in `docs/marketplace/assets/`)

These go in the **top-level `assets/` folder of the SVN repository**, not in `trunk/` or `tags/`:

| File | Size | Content |
| --- | --- | --- |
| `icon-128x128.png`, `icon-256x256.png` | 128x128, 256x256 | Conferbot logo (from the landing site) on white |
| `banner-772x250.png`, `banner-1544x500.png` | 772x250, 1544x500 | Wordmark, one line of text, and the real widget from the install recording |
| `screenshot-1.png` ... `screenshot-3.png` | 1280x720 | Real frames from the v1.1.0 install recording (WordPress 7.1.3). The captions are the numbered lines under `== Screenshots ==` in `readme.txt`. |

SVN layout and commands, once the approval email arrives:

```bash
svn co https://plugins.svn.wordpress.org/conferbot conferbot-svn && cd conferbot-svn
unzip -q /path/to/conferbot.zip -d /tmp/cb && cp -R /tmp/cb/conferbot/. trunk/   # release zip contents
cp /path/to/conferbot-wordpress/docs/marketplace/assets/*.png assets/
svn add --force trunk assets
svn propset svn:mime-type image/png assets/*.png
svn cp trunk tags/1.1.1                                                            # must match Stable tag
svn ci -m "Conferbot 1.1.1" --username <your wp.org username>                      # SVN password from your profile
```

```
conferbot-svn/
├── assets/          icon-128x128.png  icon-256x256.png  banner-772x250.png  banner-1544x500.png  screenshot-1..3.png
├── tags/1.1.1/      copy of trunk at the release
└── trunk/           conferbot.php  readme.txt  admin/  includes/  assets/ (logo.png, css/, js/)  uninstall.php  LICENSE
```

## 7. Owner checklist

- [x] Fix #9 (enqueue) and the Plugin URI, then release 1.1.1 with matching `Version:`, `CONFERBOT_VERSION` and `Stable tag:`.
- [ ] Create the WordPress.org account (username `conferbot`, or update `Contributors:`) and turn on 2FA.
- [ ] Run the Plugin Check plugin on the release zip and get zero errors.
- [ ] Upload the zip at https://wordpress.org/plugins/developers/add/, ask for the slug `conferbot`, and accept the guidelines checkbox.
- [ ] Answer the reviewer's email (keep the reviewer test instructions above handy).
- [ ] After approval: set your SVN password in your profile, commit `trunk/`, `tags/<version>/` and `assets/` as shown above.
- [ ] Check that the listing renders: icon, banner, the 3 screenshots with captions, and the External services section.

Owner time: about 30 minutes for the account and upload, plus about 20 minutes for SVN after approval. Review: about 1 to 14 business days. No fees, tax or bank details needed.
