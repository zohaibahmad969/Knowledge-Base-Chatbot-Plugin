<?php

class KBC_Deactivator {
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private static function clear_scheduled_events() {
        wp_clear_scheduled_hook('kbc_update_knowledge_base');
    }
} 