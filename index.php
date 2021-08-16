<?php

/*
  Plugin Name: Word Filter Plugin
  Description: Replaces a list of words.
  Version: 1.0
  Author: Victoria
  Author URI: https://github.com/vacb
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WordFilterPlugin {
  function __construct() {
    add_action('admin_menu', array($this, 'pluginMenu'));
    add_action('admin_init', array($this, 'pluginSettings'));
    // If db contains a selected option for plugin_words_to_filter, apply filter logic
    if (get_option('plugin_words_to_filter')) {
      add_filter('the_content', array($this, 'filterLogic'));
    }
  }

  // REGISTER SETTINGS
  function pluginSettings() {
    add_settings_section('replacementTextSection', null, null, 'word-filter-options');
    register_setting('replacementFields', 'replacementText');
    add_settings_field('replacement-text', 'Filtered Text Appearance', array($this, 'replacementFieldHTML'), 'word-filter-options', 'replacementTextSection');
  }

  // HTML for options page
  function replacementFieldHTML() { ?>
    <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '****'))?>">
    <p class="description">Leave blank to simply remove the filtered words.</p>
  <?php }

  // FILTER LOGIC
  function filterLogic($content) {
    $badWords = explode(',', get_option('plugin_words_to_filter'));
    $badWordsTrimmed = array_map('trim', $badWords);
    return str_ireplace($badWordsTrimmed, esc_html(get_option('replacementText'), '****'), $content);
  }

  // ADD MENU ITEMS AND LINK TO STYLES
  function pluginMenu() {
    // Args: title, text in admin sidebar, permission to view, shortname/slug, function that outputs the html, icon, number to control how high in the menu it appears
    // Returns a hook name that can be used in the action adding CSS
    $mainPageHook = add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'wordfilter', array($this, 'wordFilterPage'), plugin_dir_url(__FILE__) . 'custom.svg', 100);

    // ALT WAY TO INCLUDE SVG FILE
    // Open svg file in text editor, copy to clipboard and run inside btoa(``) in console to conv to ascii
    // add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'wordfilter', array($this, 'wordFilterPage'), 'data:image/svg+xmp;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+Cg==', 100);

    // Args: menu to add to (slug), doc title, text in admin sidebar, permission/capability to view, shortname/slug, function to output html
    // First item duplicates top level item but with a different description - avoids having two idential links to the same place
    add_submenu_page('wordfilter', 'Words To Filter', 'Words List', 'manage_options', 'wordfilter', array($this, 'wordFilterPage'));
    add_submenu_page('wordfilter', 'Word Filter Options', 'Options', 'manage_options', 'word-filter-options', array($this, 'optionsSubPage'));
    add_action("load-{$mainPageHook}", array($this, 'mainPageAssets'));
  }

  // Include CSS for plugin admin page
  function mainPageAssets() {
    wp_enqueue_style('filterAdminCss', plugin_dir_url(__FILE__) . 'styles.css');
  }

  function handleForm() {
    if (wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') AND current_user_can('manage_options')) {
      update_option('plugin_words_to_filter', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>
      <div class="updated">
        <p>Your filtered words were saved.</p>
      </div>
    <?php } else { ?>
      <div class="error">
        <p>Sorry, you do not have permission to perform that action.</p>
      </div>
    <?php }
 }

  // Create plugin page - custom form handling
  function wordFilterPage() { ?>
    <div class="wrap">
      <h1>Word Filter</h1>
      <?php if ($_POST['justsubmitted'] == "true") $this->handleForm() ?>
      <form method="POST">
        <input type="hidden" name="justsubmitted" value="true">
        <?php wp_nonce_field('saveFilterWords', 'ourNonce'); ?>
        <label for="plugin_words_to_filter"><p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content.</p></label>
        <div class="word-filter__flex-container">
          <textarea name="plugin_words_to_filter" id="plugin_words_to_filter" placeholder="bad, mean, awful, horrible"><?php echo esc_textarea(get_option('plugin_words_to_filter')); ?></textarea>
        </div>
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
      </form>
    </div>
  <?php }

  // Create plugin sub-page - using WP formhandling instead of custom above
  function optionsSubPage() { ?>
    <div class="wrap">
      <h1>Word Filter Options</h1>
      <form action="options.php" method="POST">
        <?php
          // Not registered under settings api, so need to call the error function specifically.
          settings_errors();
          settings_fields('replacementFields');
          do_settings_sections('word-filter-options');
          submit_button();
        ?>
      </form>
    </div>
  <?php }
}

$wordFilterPlugin = new WordFilterPlugin();