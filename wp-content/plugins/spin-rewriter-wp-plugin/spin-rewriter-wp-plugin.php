<?php
/**
 * Plugin Name: Spin Rewriter
 * Plugin URI: http://www.spinrewriter.com/wordpress-plugin
 * Description: This is the official Spin Rewriter plugin, used by thousands of marketers. It can spin and republish your old posts, and it can also create brand new posts automatically.
 * Version: 1.3
 * Author: Aaron Sustar
 * Author URI: http://www.aaronsustar.com/
 * Text Domain: spin-rewriter-wp-plugin
 * License: GPLv3
 */

$SpinRewriterWPPlugin_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function SpinRewriterWPPlugin_noticePhpVersionWrong() {
    global $SpinRewriterWPPlugin_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: Plugin "Spin Rewriter" requires a newer version of PHP to be running.', 'spin-rewriter-wp-plugin').
            '<br/>' . __('Minimal version of PHP required: ', 'spin-rewriter-wp-plugin') . '<strong>' . $SpinRewriterWPPlugin_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'spin-rewriter-wp-plugin') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
}


function SpinRewriterWPPlugin_PhpVersionCheck() {
    global $SpinRewriterWPPlugin_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $SpinRewriterWPPlugin_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'SpinRewriterWPPlugin_noticePhpVersionWrong');
        return false;
    }
    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function SpinRewriterWPPlugin_i18n_init() {
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('spin-rewriter-wp-plugin', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
SpinRewriterWPPlugin_i18n_init();


// Next, run the version check.
// If it is successful, continue with initialization for this plugin
if (SpinRewriterWPPlugin_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('spin-rewriter-wp-plugin_init.php');
    SpinRewriterWPPlugin_init(__FILE__);
}


// Finally, make sure this plugin knows how to auto-update itself.
try {
	include 'plugin-updates/plugin-update-checker.php';
	$MyUpdateChecker = PucFactory::buildUpdateChecker(
		'http://www.spinrewriter.com/wp-plugin/wordpress-plugin-metadata.json',
		__FILE__,
		'spin-rewriter-wp-plugin'
	);
} catch (Exception $e) {
	// Something went wrong with the initialization of the auto-updater library.
}
