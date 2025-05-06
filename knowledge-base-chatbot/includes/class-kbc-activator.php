<?php

class KBC_Activator {
    public static function activate() {
        global $wpdb;
        
        // Create knowledge base table
        $table_name = $wpdb->prefix . 'kbc_knowledge_base';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            content_id bigint(20) NOT NULL,
            content text NOT NULL,
            metadata text,
            last_updated datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY type_content_id (type, content_id)
        ) $charset_collate;";
        
        // Create chat history table
        $chat_table = $wpdb->prefix . 'kbc_chat_history';
        $sql .= "CREATE TABLE IF NOT EXISTS $chat_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(32) NOT NULL,
            user_message text NOT NULL,
            bot_response text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY session_id (session_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        add_option('kbc_api_key', 'sk-free-test-key-1234567890abcdef');
        add_option('kbc_model', 'gpt-3.5-turbo');
        add_option('kbc_max_tokens', 150);
        add_option('kbc_temperature', 0.7);
        add_option('kbc_content_types', array('post', 'page'));
        add_option('kbc_update_frequency', 'daily');
        add_option('kbc_scheduling_keywords', array('schedule', 'book', 'meeting'));
        add_option('kbc_calendly_url', '');
        add_option('kbc_chatbot_position', 'bottom-right');
        add_option('kbc_chatbot_theme', 'light');
        add_option('kbc_chatbot_icon', 'default');
        add_option('kbc_chatbot_color', '#007bff');
        add_option('kbc_display_method', 'automatic');
        add_option('kbc_exclude_pages', '');
        
        // Create upload directory for knowledge base exports
        $upload_dir = wp_upload_dir();
        $kbc_dir = $upload_dir['basedir'] . '/knowledge-base-chatbot';
        if (!file_exists($kbc_dir)) {
            wp_mkdir_p($kbc_dir);
        }
        
        // Add .htaccess to protect the directory
        $htaccess = $kbc_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            $htaccess_content = "Order deny,allow\nDeny from all";
            file_put_contents($htaccess, $htaccess_content);
        }
    }
} 