<?php
/**
 * Plugin Name: Knowledge Base Chatbot
 * Plugin URI: https://github.com/zohaibahmad969/knowledge-base-chatbot
 * Description: An AI-powered chatbot interface for your knowledge base, with Calendly integration for scheduling meetings.
 * Version: 1.0.0
 * Author: Zohaib Ahmad
 * Author URI: https://zohaibahmad.com
 * Text Domain: knowledge-base-chatbot
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('KBC_VERSION', '1.0.0');
define('KBC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KBC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once KBC_PLUGIN_DIR . 'includes/class-kbc-activator.php';
require_once KBC_PLUGIN_DIR . 'includes/class-kbc-deactivator.php';
require_once KBC_PLUGIN_DIR . 'includes/class-kbc-core.php';
require_once KBC_PLUGIN_DIR . 'includes/class-kbc-knowledge-base.php';
require_once KBC_PLUGIN_DIR . 'includes/class-kbc-chatbot.php';
require_once KBC_PLUGIN_DIR . 'includes/class-kbc-calendly.php';
require_once KBC_PLUGIN_DIR . 'admin/class-kbc-admin.php';
require_once KBC_PLUGIN_DIR . 'public/class-kbc-public.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('KBC_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('KBC_Deactivator', 'deactivate'));

// Initialize the plugin
function run_knowledge_base_chatbot() {
    $plugin = new KBC_Core('knowledge-base-chatbot', KBC_VERSION);
    $plugin->run();
}

run_knowledge_base_chatbot(); 