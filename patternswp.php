<?php
/**
 * Plugin Name:       PatternsWP
 * Plugin URI:        https://thepatternswp.com
 * Description:       A growing library of ready-made block patterns can help you build websites faster in no time.
 * Author:            PatternsWP
 * Author URI:        https://thepatternswp.com
 * Version:           1.0.4
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       patternswp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants.
define('PWP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PWP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PWP_PLUGIN_FILE', __FILE__);
define('PWP_ABSPATH', dirname(__FILE__) . '/');
define('PWP_VERSION', get_file_data(__FILE__, ['Version'])[0]);

// Include necessary files.
require_once PWP_PLUGIN_DIR . 'includes/class-patternswp-api.php';

/**
 * Enqueue assets for Block Editor.
 */
function patternswp_enqueue_editor_assets() {

    // Scripts.
    $script_asset = patternswp_get_asset_file('build/patternswp-editor');
    wp_enqueue_script(
        'patternswp-editor-scripts',
        PWP_PLUGIN_URL . 'build/patternswp-editor.js',
        array_merge($script_asset['dependencies'], ['wp-api']),
        $script_asset['version'],
        true
    );

    // Styles.
    $style_asset = patternswp_get_asset_file('build/block-pattern-inserter-editor-styles');
    wp_enqueue_style(
        'patternswp-editor-styles',
        PWP_PLUGIN_URL . 'build/style-patternswp-editor-styles.css',
        array(),
        $style_asset['version']
    );
}
add_action('enqueue_block_editor_assets', 'patternswp_enqueue_editor_assets');

/**
 * Get asset file data.
 *
 * @param string $filepath The file path.
 * @return array Asset data.
 */
function patternswp_get_asset_file($filepath) {
    $asset_path = PWP_ABSPATH . $filepath . '.asset.php';
    return file_exists($asset_path)
        ? require_once $asset_path
        : [
            'dependencies' => [],
            'version'      => PWP_VERSION,
        ];
}

// Plugin activation hook.
register_activation_hook(__FILE__, 'patternswp_plugin_activate');
function patternswp_plugin_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'patternswp_patterns';

    // Check if the table already exists.
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Table does not exist, so create it.
        $charset_collate = $wpdb->get_charset_collate();
        $sql             = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            title VARCHAR(255),
            categories VARCHAR(100),
            content LONGTEXT,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Plugin deactivation hook.
register_deactivation_hook(__FILE__, 'patternswp_plugin_deactivate');
function patternswp_plugin_deactivate() {
    // Deactivation tasks if any.
}
