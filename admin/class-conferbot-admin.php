<?php
/**
 * Settings page: two steps - paste the bot ID (auto-extracted from anything
 * pasted), flip the switch, save. Advanced options collapsed.
 */

if (!defined('ABSPATH') && !defined('CONFERBOT_TEST_SUITE')) {
    exit;
}

class Conferbot_Admin
{
    public static function handle_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to change these settings.', 'conferbot'));
        }
        check_admin_referer('conferbot_save');

        $pasted = isset($_POST['bot_id']) ? wp_unslash($_POST['bot_id']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in Conferbot_Settings::sanitize()
        $wants_enabled = !empty($_POST['enabled']);
        $extracted = Conferbot_Bot_Id::extract($pasted);

        $error = '';
        if ($extracted === '' && trim($pasted) !== '') {
            $error = 'no_id_found';
        } elseif ($extracted === '' && $wants_enabled) {
            $error = 'id_required';
        }

        if ($error === '') {
            Conferbot_Settings::save(array(
                'enabled' => $wants_enabled,
                'bot_id' => $pasted,
                'embed_type' => isset($_POST['embed_type']) ? sanitize_text_field(wp_unslash($_POST['embed_type'])) : 'live_chat',
                'script_url' => isset($_POST['script_url']) ? esc_url_raw(wp_unslash($_POST['script_url'])) : '',
                'identity_secret' => isset($_POST['identity_secret']) ? sanitize_text_field(wp_unslash($_POST['identity_secret'])) : '',
                'identity_secret_clear' => !empty($_POST['identity_secret_clear']),
                'show_on_home' => !empty($_POST['show_on_home']),
                'show_on_posts' => !empty($_POST['show_on_posts']),
                'show_on_pages' => !empty($_POST['show_on_pages']),
                'show_on_shop' => !empty($_POST['show_on_shop']),
                'show_on_cart' => !empty($_POST['show_on_cart']),
                'show_on_checkout' => !empty($_POST['show_on_checkout']),
                'show_on_account' => !empty($_POST['show_on_account']),
                'show_on_other' => !empty($_POST['show_on_other']),
            ));
        }

        wp_safe_redirect(add_query_arg(
            $error ? array('page' => 'conferbot', 'cb_error' => $error) : array('page' => 'conferbot', 'saved' => '1'),
            admin_url('options-general.php')
        ));
        exit;
    }

    public static function render_page()
    {
        $s = Conferbot_Settings::get();
        $saved = isset($_GET['saved']); // phpcs:ignore WordPress.Security.NonceVerification -- display-only flag
        $error = isset($_GET['cb_error']) ? sanitize_key(wp_unslash($_GET['cb_error'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification

        $page_labels = array(
            'home' => __('Home page', 'conferbot'),
            'posts' => __('Blog posts', 'conferbot'),
            'pages' => __('Pages', 'conferbot'),
            'shop' => __('Shop & products (WooCommerce)', 'conferbot'),
            'cart' => __('Cart (WooCommerce)', 'conferbot'),
            'checkout' => __('Checkout (WooCommerce)', 'conferbot'),
            'account' => __('My account (WooCommerce)', 'conferbot'),
            'other' => __('All other pages', 'conferbot'),
        );
        ?>
<style>
  .cb-panel { max-width: 720px; margin: 24px 0; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); padding: 32px 36px; font-size: 15px; }
  .cb-panel h1 { display: flex; align-items: center; gap: 10px; font-size: 24px; margin: 0 0 6px; padding: 0; }
  .cb-sub { color: #6b7280; margin-bottom: 24px; }
  .cb-status { border-radius: 10px; padding: 14px 18px; margin-bottom: 26px; }
  .cb-status.ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
  .cb-status.warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
  .cb-status.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
  .cb-step { margin-bottom: 26px; }
  .cb-step-label { font-weight: 700; font-size: 17px; margin-bottom: 6px; display: block; }
  .cb-step-help { color: #6b7280; margin-bottom: 10px; }
  .cb-input { width: 100%; font-size: 18px; padding: 14px 16px; border: 2px solid #d1d5db; border-radius: 10px; }
  .cb-input:focus { border-color: #2563eb; outline: none; box-shadow: none; }
  .cb-switch { position: relative; display: inline-block; width: 64px; height: 34px; vertical-align: middle; }
  .cb-switch input { opacity: 0; width: 0; height: 0; }
  .cb-slider { position: absolute; cursor: pointer; inset: 0; background: #d1d5db; border-radius: 34px; transition: .2s; }
  .cb-slider:before { content: ""; position: absolute; height: 26px; width: 26px; left: 4px; bottom: 4px; background: #fff; border-radius: 50%; transition: .2s; }
  .cb-switch input:checked + .cb-slider { background: #16a34a; }
  .cb-switch input:checked + .cb-slider:before { transform: translateX(30px); }
  .cb-switch-text { margin-left: 12px; vertical-align: middle; font-size: 16px; }
  .cb-save { display: block; width: 100%; margin-top: 8px; background: #2563eb !important; color: #fff !important; border: none !important; border-radius: 10px !important; padding: 16px !important; font-size: 19px !important; font-weight: 700; cursor: pointer; }
  .cb-demo { margin-top: 8px; color: #6b7280; font-size: 14px; }
  .cb-advanced { margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 18px; }
  .cb-advanced summary { cursor: pointer; color: #6b7280; font-weight: 600; font-size: 14px; }
  .cb-pages label { display: inline-flex; align-items: center; gap: 6px; margin: 8px 16px 0 0; }
  .cb-select { margin-top: 10px; }
  .cb-help { margin-top: 18px; text-align: center; color: #6b7280; }
</style>
<div class="wrap">
<div class="cb-panel">
  <h1><img src="<?php echo esc_url(CONFERBOT_PLUGIN_URL . 'assets/logo.png'); ?>" alt="Conferbot" style="width:44px;height:44px"> <?php esc_html_e('Conferbot Live Chat', 'conferbot'); ?></h1>
  <p class="cb-sub"><?php esc_html_e('Add a chat assistant to your site in under a minute. Only one thing to paste!', 'conferbot'); ?></p>

  <?php if ($error === 'no_id_found') : ?>
    <div class="cb-status err">🤔 <?php esc_html_e('We could not find a Chatbot ID in what you pasted. It looks like a 24-character code, e.g. 64a1b2c3d4e5f6a7b8c9d0e1.', 'conferbot'); ?></div>
  <?php elseif ($error === 'id_required') : ?>
    <div class="cb-status err">👇 <?php esc_html_e('Please paste your Chatbot ID first (step 1), then turn the chat on.', 'conferbot'); ?></div>
  <?php elseif ($saved && !empty($s['enabled'])) : ?>
    <div class="cb-status ok">✅ <?php esc_html_e('Saved! The chat is now live - open your site to say hello to it.', 'conferbot'); ?>
      <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank"><?php esc_html_e('See it on my site →', 'conferbot'); ?></a></div>
  <?php elseif ($saved) : ?>
    <div class="cb-status ok">✅ <?php esc_html_e('Saved. The chat is switched off for now - turn it on whenever you are ready.', 'conferbot'); ?></div>
  <?php elseif (empty($s['bot_id'])) : ?>
    <div class="cb-status warn">👇 <?php esc_html_e('Just one step: paste your Chatbot ID below and press the big blue button.', 'conferbot'); ?></div>
  <?php elseif (empty($s['enabled'])) : ?>
    <div class="cb-status warn">⏸️ <?php esc_html_e('The chat is currently switched OFF. Turn it on below when you are ready.', 'conferbot'); ?></div>
  <?php else : ?>
    <div class="cb-status ok">✅ <?php esc_html_e('All set! The chat widget is live on your site.', 'conferbot'); ?>
      <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank"><?php esc_html_e('See it on my site →', 'conferbot'); ?></a></div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="conferbot_save">
    <?php wp_nonce_field('conferbot_save'); ?>

    <div class="cb-step">
      <label class="cb-step-label" for="cb-id">1. <?php esc_html_e('Paste your Chatbot ID', 'conferbot'); ?></label>
      <div class="cb-step-help">
        <?php
        printf(
            /* translators: %s: link to the Conferbot dashboard */
            esc_html__('Find it in your %s: open your bot, click "Share", then "Copy Bot ID". Pasting the whole code snippet also works - we will find the ID for you.', 'conferbot'),
            '<a href="https://app.conferbot.com" target="_blank" rel="noopener">' . esc_html__('Conferbot dashboard', 'conferbot') . '</a>'
        );
        ?>
      </div>
      <input class="cb-input" type="text" id="cb-id" name="bot_id"
             value="<?php echo esc_attr($s['bot_id']); ?>"
             placeholder="<?php esc_attr_e('e.g. 64a1b2c3d4e5f6a7b8c9d0e1', 'conferbot'); ?>">
      <div class="cb-demo">
        <?php esc_html_e('No account yet?', 'conferbot'); ?>
        <a href="#" id="cb-use-demo" data-demo-id="<?php echo esc_attr(Conferbot_Bot_Id::DEMO_BOT_ID); ?>"><?php esc_html_e('Try our demo bot', 'conferbot'); ?></a>
        <?php esc_html_e('- it works right away, no signup needed.', 'conferbot'); ?>
      </div>
    </div>

    <div class="cb-step">
      <span class="cb-step-label">2. <?php esc_html_e('Turn the chat on', 'conferbot'); ?></span>
      <label class="cb-switch">
        <input type="checkbox" name="enabled" value="1" <?php checked(!empty($s['enabled'])); ?>>
        <span class="cb-slider"></span>
      </label>
      <span class="cb-switch-text"><?php esc_html_e('Show the chat bubble on my site', 'conferbot'); ?></span>
    </div>

    <button class="cb-save" type="submit"><?php esc_html_e('Save - start chatting with my visitors', 'conferbot'); ?> 🎉</button>

    <details class="cb-advanced">
      <summary><?php esc_html_e('Advanced settings (optional - the defaults are fine)', 'conferbot'); ?></summary>
      <p><strong><?php esc_html_e('How the chat appears', 'conferbot'); ?></strong></p>
      <select class="cb-select" name="embed_type">
        <option value="live_chat" <?php selected($s['embed_type'], 'live_chat'); ?>><?php esc_html_e('Floating chat bubble (recommended)', 'conferbot'); ?></option>
        <option value="popup_chat" <?php selected($s['embed_type'], 'popup_chat'); ?>><?php esc_html_e('Popup window', 'conferbot'); ?></option>
      </select>
      <p><strong><?php esc_html_e('Show the chat on these pages', 'conferbot'); ?></strong></p>
      <div class="cb-pages">
        <?php foreach ($page_labels as $key => $label) : ?>
          <label><input type="checkbox" name="show_on_<?php echo esc_attr($key); ?>" value="1" <?php checked(!empty($s['show_on_' . $key])); ?>> <?php echo esc_html($label); ?></label>
        <?php endforeach; ?>
      </div>
      <p><strong><?php esc_html_e('Identity verification secret', 'conferbot'); ?></strong> <span class="cb-step-help"><?php esc_html_e('(optional - from Conferbot workspace settings > Security > Identity verification; lets Conferbot trust your logged-in customers\' names and emails)', 'conferbot'); ?></span></p>
      <input class="cb-input" style="font-size:14px;padding:10px 12px" type="password" name="identity_secret" autocomplete="new-password"
        placeholder="<?php echo esc_attr(!empty($s['identity_secret']) ? __('Saved - paste a new one to replace it', 'conferbot') : __('Paste your secret', 'conferbot')); ?>" value="">
      <?php if (!empty($s['identity_secret'])) : ?>
        <label class="cb-step-help"><input type="checkbox" name="identity_secret_clear" value="1"> <?php esc_html_e('Remove the saved secret', 'conferbot'); ?></label>
      <?php endif; ?>
      <p><strong><?php esc_html_e('Widget script URL', 'conferbot'); ?></strong> <span class="cb-step-help"><?php esc_html_e('(leave as is unless Conferbot support says otherwise)', 'conferbot'); ?></span></p>
      <input class="cb-input" style="font-size:14px;padding:10px 12px" type="text" name="script_url" value="<?php echo esc_attr($s['script_url']); ?>">
    </details>
  </form>
  <p class="cb-help"><?php esc_html_e('Need help? Write to', 'conferbot'); ?> <a href="mailto:support@conferbot.com">support@conferbot.com</a></p>
</div>
</div>
<script>
  document.getElementById('cb-use-demo').addEventListener('click', function (e) {
    e.preventDefault();
    var input = document.getElementById('cb-id');
    input.value = this.getAttribute('data-demo-id');
    var toggle = document.querySelector('.cb-switch input');
    if (toggle) { toggle.checked = true; }
    input.focus();
  });
</script>
        <?php
    }
}
