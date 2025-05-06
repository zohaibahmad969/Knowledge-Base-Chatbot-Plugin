<?php

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

class KBC_Uninstaller {
    public static function uninstall() {
        // Delete plugin options
        self::delete_options();
        
        // Drop custom tables
        self::drop_tables();
        
        // Clear any remaining scheduled events
        self::clear_scheduled_events();
    }
    
    private static function delete_options() {
        $options = array(
            'kbc_ai_provider',
            'kbc_api_key',
            'kbc_model',
            'kbc_max_tokens',
            'kbc_temperature',
            'kbc_calendly_url',
            'kbc_scheduling_keywords',
            'kbc_content_types',
            'kbc_update_frequency',
            'kbc_chatbot_position',
            'kbc_chatbot_theme'
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
    }
    
    private static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'kbc_knowledge_base',
            $wpdb->prefix . 'kbc_chat_history'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    private static function clear_scheduled_events() {
        wp_clear_scheduled_hook('kbc_update_knowledge_base');
    }
}

// Run uninstaller
KBC_Uninstaller::uninstall(); 