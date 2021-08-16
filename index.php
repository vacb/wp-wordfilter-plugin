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
  }

  function pluginMenu() {
    // Args: title, text in admin sidebar, permission to view, shortname/slug, function that outputs the html, icon, number to control how high in the menu it appears
    add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'wordfilter', array($this, 'wordFilterPage'), 'dashicons-smiley', 100);
    // Args: menu to add to (slug), doc title, text in admin sidebar, permission/capability to view, shortname/slug, function to output html
    // First item duplicates top level item but with a different description - avoids having two idential links to the same place
    add_submenu_page('wordfilter', 'Words To Filter', 'Words List', 'manage_options', 'wordfilter', array($this, 'wordFilterPage'));
    add_submenu_page('wordfilter', 'Word Filter Options', 'Options', 'manage_options', 'word-filter-options', array($this, 'optionsSubPage'));
  }

  // Create plugin page
  function wordFilterPage() { ?>
    Hello World
  <?php }

  // Create plugin page
  function optionsSubPage() { ?>
    Hello World from options subpage
  <?php }
}

$wordFilterPlugin = new WordFilterPlugin();