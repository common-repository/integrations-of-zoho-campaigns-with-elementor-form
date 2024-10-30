<?php

if (!defined('ABSPATH')) {
    exit;
}
$scheme = parse_url(home_url())['scheme'];

define('IZCEF_PLUGIN_BASENAME', plugin_basename(IZCEF_PLUGIN_MAIN_FILE));
define('IZCEF_PLUGIN_BASEDIR', plugin_dir_path(IZCEF_PLUGIN_MAIN_FILE));
define('IZCEF_ROOT_URI', set_url_scheme(plugins_url('', IZCEF_PLUGIN_MAIN_FILE), $scheme));
define('IZCEF_PLUGIN_DIR_PATH', plugin_dir_path(IZCEF_PLUGIN_MAIN_FILE));
define('IZCEF_ASSET_URI', IZCEF_ROOT_URI . '/assets');
define('IZCEF_ASSET_JS_URI', IZCEF_ROOT_URI . '/assets/js');
// Autoload vendor files.
require_once IZCEF_PLUGIN_BASEDIR . 'vendor/autoload.php';
// Initialize the plugin.
FormInteg\IZCEF\Plugin::load();
