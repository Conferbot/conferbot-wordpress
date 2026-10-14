=== Conferbot - AI Chatbot, Live Chat & Customer Support ===
Contributors: conferbot
Tags: chatbot, live chat, ai chatbot, customer support, chat widget
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn visitors into customers with an AI chatbot and live chat widget. 24/7 support, lead capture, human handover - set up in under a minute.

== Description ==

Conferbot adds an AI-powered chat assistant to your WordPress site or WooCommerce store. Automate answers to repetitive questions, capture leads while you sleep, and hand conversations to human agents when it matters.

* **AI chatbot that sells** - flows or an AI agent trained on your content answer visitor and product questions instantly, 24/7
* **Real live chat** - handover from the bot to your team, with queue position, read receipts, file sharing and a post-chat rating; when nobody is free, visitors leave a message and you reply by email
* **Lead capture** - collect emails and qualify visitors automatically
* **WooCommerce aware** - the bot sees the logged-in customer's name, email and cart value, and sales are credited to the chats that led to them
* **Identity verification** - optionally sign logged-in customers so Conferbot can trust who they are
* **1-minute setup, zero code** - paste one ID, flip a switch, done
* **Try before signing up** - a built-in demo bot works without a Conferbot account
* **Fully brandable** - design the widget in the Conferbot dashboard: themes and dark mode, colors, logo, launcher, mascot, home screen, greetings and languages
* **Per-page control** - choose exactly where the chat appears (home, posts, pages, shop, cart, checkout, account)

A free Conferbot account is available at [conferbot.com](https://conferbot.com). The plugin also ships with a public demo bot so you can see the widget live before creating an account.

== Installation ==

1. Install and activate the plugin.
2. Go to Settings > Conferbot.
3. Paste your Chatbot ID - or the whole embed code from the Conferbot dashboard (open your bot, then Share > Embed); the plugin finds the ID in it. Or click "Try our demo bot".
4. Turn the chat on and save. The chat bubble appears on your site.

== Frequently Asked Questions ==

= Do I need a Conferbot account? =
To use your own bot, yes - the free plan is enough to start. To just try the widget, no: click "Try our demo bot" in the settings.

= Does it slow down my site? =
No. The widget script loads asynchronously from a CDN and does not block rendering.

= Does it work with WooCommerce? =
Yes. Logged-in customer identity and cart contents are passed to the bot as conversation context, and you can control visibility on shop, cart, checkout and account pages.

= Where do I customize the widget's look? =
In the Conferbot dashboard under Customize. Colors, logo, position, welcome messages, languages and proactive prompts are all delivered to the widget at runtime - no theme edits.

= How do I verify logged-in customers? =
In Conferbot, open workspace settings > Security > Identity verification and copy the secret. In WordPress, paste it under Settings > Conferbot > Advanced settings. Each logged-in customer is then signed on your server; the secret never reaches the browser.

= Are sales credited to chats? =
Yes, with WooCommerce. The thank-you page reports each order once (value, currency, order number), and add-to-cart clicks are reported too. A sale within a week of a chat is credited to that chat in Conferbot.

= What data does the plugin store? =
Only its own settings (bot ID, display options and, if you add one, the identity verification secret). Conversations happen between the visitor's browser and Conferbot's servers; see the Conferbot privacy policy.

== Screenshots ==

1. Two-step settings page: paste the bot ID, flip the switch.
2. The chat bubble live on a storefront.
3. A conversation with the AI assistant.

== Changelog ==

= 1.1.0 =
* Identity verification: logged-in customers are signed with your workspace secret.
* WooCommerce sales tracking: orders and add-to-cart are reported to Conferbot.

= 1.0.0 =
* Initial release: widget embed with visitor/WooCommerce context, two-step settings page, demo bot, per-page visibility.
