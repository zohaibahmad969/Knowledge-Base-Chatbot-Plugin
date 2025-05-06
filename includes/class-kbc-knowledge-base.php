<?php

class KBC_Knowledge_Base {
    private $table_name;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'kbc_knowledge_base';
    }

    public function init() {
        // Create table if it doesn't exist
        $this->create_table();

        // Schedule knowledge base updates
        $this->schedule_updates();
    }

    private function create_table() {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            content_id bigint(20) NOT NULL,
            content text NOT NULL,
            metadata text,
            last_updated datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY type_content_id (type, content_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function schedule_updates() {
        if (!wp_next_scheduled('kbc_update_knowledge_base')) {
            $frequency = get_option('kbc_update_frequency', 'daily');
            $schedules = array(
                'hourly' => HOUR_IN_SECONDS,
                'daily' => DAY_IN_SECONDS,
                'weekly' => WEEK_IN_SECONDS
            );
            
            wp_schedule_event(time(), $frequency, 'kbc_update_knowledge_base');
        }
    }

    public function update_knowledge_base() {
        // Get content types to include
        $content_types = get_option('kbc_content_types', array('post', 'page'));
        
        // Clear existing entries
        $this->wpdb->query("TRUNCATE TABLE $this->table_name");
        
        // Process each content type
        foreach ($content_types as $type) {
            $this->update_content_type($type);
        }
        
        // Add any manual entries
        $this->add_manual_entries();
        
        return array(
            'success' => true,
            'last_updated' => current_time('mysql')
        );
    }
    
    private function update_content_type($type) {
        global $wpdb;
        
        $args = array(
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                // Get the post content
                $content = get_the_content();
                
                // Clean the content
                $content = $this->clean_content($content);
                
                // Skip if content is empty after cleaning
                if (empty($content)) {
                    continue;
                }
                
                // Get metadata
                $metadata = array(
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'author' => get_the_author(),
                    'date' => get_the_date()
                );
                
                // Insert into knowledge base
                $wpdb->insert(
                    $this->table_name,
                    array(
                        'type' => $type,
                        'content' => $content,
                        'metadata' => json_encode($metadata),
                        'last_updated' => current_time('mysql')
                    ),
                    array('%s', '%s', '%s', '%s')
                );
            }
            wp_reset_postdata();
        }
    }
    
    /**
     * Clean the content by removing unwanted HTML elements and keeping only text content
     *
     * @param string $content The content to clean
     * @return string Cleaned content
     */
    private function clean_content($content) {
        // Remove script and style tags
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
        
        // Remove form elements
        $content = preg_replace('/<form\b[^>]*>(.*?)<\/form>/is', '', $content);
        $content = preg_replace('/<input\b[^>]*>/i', '', $content);
        $content = preg_replace('/<select\b[^>]*>(.*?)<\/select>/is', '', $content);
        $content = preg_replace('/<textarea\b[^>]*>(.*?)<\/textarea>/is', '', $content);
        $content = preg_replace('/<button\b[^>]*>(.*?)<\/button>/is', '', $content);
        
        // Remove images
        $content = preg_replace('/<img\b[^>]*>/i', '', $content);
        
        // Remove iframes
        $content = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $content);
        
        // Remove audio and video elements
        $content = preg_replace('/<audio\b[^>]*>(.*?)<\/audio>/is', '', $content);
        $content = preg_replace('/<video\b[^>]*>(.*?)<\/video>/is', '', $content);
        
        // Remove other media elements
        $content = preg_replace('/<embed\b[^>]*>/i', '', $content);
        $content = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $content);
        
        // Remove comments
        $content = preg_replace('/<!--(.*?)-->/s', '', $content);
        
        // Convert HTML entities
        $content = html_entity_decode($content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        return $content;
    }
    
    private function add_manual_entries() {
        // Get manual entries from options
        $manual_entries = get_option('kbc_manual_entries', array());
        
        foreach ($manual_entries as $entry) {
            $this->wpdb->insert(
                $this->table_name,
                array(
                    'type' => 'manual',
                    'content_id' => 0,
                    'content' => $entry['content'],
                    'metadata' => json_encode($entry['metadata']),
                    'last_updated' => current_time('mysql')
                ),
                array('%s', '%d', '%s', '%s', '%s')
            );
        }
    }

    /**
     * Add a manual entry to the knowledge base
     *
     * @param string $type The type of entry
     * @param string $content The content of the entry
     * @param array $metadata Additional metadata
     * @return bool|int The ID of the inserted entry or false on failure
     */
    public function add_manual_entry($type, $content, $metadata = array()) {
        global $wpdb;
        
        try {
            // Ensure required fields are present
            if (empty($type) || empty($content)) {
                error_log('KBC: Missing required fields - type or content is empty');
                return false;
            }
            
            // Prepare metadata
            $metadata = array_merge($metadata, array(
                'title' => isset($metadata['title']) ? $metadata['title'] : 'Manual Entry',
                'url' => isset($metadata['url']) ? $metadata['url'] : '',
                'author' => isset($metadata['author']) ? $metadata['author'] : 'Manual Entry',
                'date' => current_time('mysql')
            ));
            
            // Debug the data being inserted
            error_log('KBC: Attempting to insert entry with data: ' . print_r(array(
                'type' => $type,
                'content' => substr($content, 0, 100) . '...', // Log first 100 chars
                'metadata' => $metadata
            ), true));
            
            // Insert into knowledge base
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'type' => $type,
                    'content_id' => 0, // Manual entries have content_id 0
                    'content' => $content,
                    'metadata' => json_encode($metadata),
                    'last_updated' => current_time('mysql')
                ),
                array('%s', '%d', '%s', '%s', '%s')
            );
            
            if ($result === false) {
                error_log('KBC: Database error: ' . $wpdb->last_error);
                error_log('KBC: Last query: ' . $wpdb->last_query);
                return false;
            }
            
            $insert_id = $wpdb->insert_id;
            error_log('KBC: Successfully inserted entry with ID: ' . $insert_id);
            
            return $insert_id;
        } catch (Exception $e) {
            error_log('KBC: Exception in add_manual_entry: ' . $e->getMessage());
            error_log('KBC: Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function get_entry($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE id = %d",
                $id
            )
        );
    }

    public function delete_entry($id) {
        return $this->wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }

    public function search($query, $limit = 10) {
        $query = '%' . $this->wpdb->esc_like($query) . '%';
        
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE content LIKE %s ORDER BY last_updated DESC LIMIT %d",
                $query,
                $limit
            )
        );
    }

    public function export() {
        $entries = $this->wpdb->get_results("SELECT * FROM $this->table_name ORDER BY last_updated DESC");
        
        $export = array();
        foreach ($entries as $entry) {
            $export[] = array(
                'type' => $entry->type,
                'content_id' => $entry->content_id,
                'content' => $entry->content,
                'metadata' => json_decode($entry->metadata, true),
                'last_updated' => $entry->last_updated
            );
        }

        return $export;
    }

    public function get_total_entries() {
        return $this->wpdb->get_var("SELECT COUNT(*) FROM $this->table_name");
    }

    public function get_entries($limit = 20, $offset = 0) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM $this->table_name ORDER BY last_updated DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
    }

    public function export_knowledge_base() {
        return $this->wpdb->get_results("SELECT * FROM $this->table_name ORDER BY last_updated DESC");
    }
} 