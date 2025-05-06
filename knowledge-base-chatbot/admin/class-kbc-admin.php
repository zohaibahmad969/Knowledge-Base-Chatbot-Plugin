<?php

class KBC_Admin {
    private $plugin_name;
    private $version;
    private $knowledge_base;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->knowledge_base = new KBC_Knowledge_Base();
        
        // Register AJAX actions
        add_action('wp_ajax_kbc_update_knowledge_base', array($this, 'ajax_update_knowledge_base'));
        add_action('wp_ajax_kbc_add_manual_knowledge', array($this, 'ajax_add_manual_knowledge'));
        add_action('wp_ajax_kbc_get_knowledge_entry', array($this, 'ajax_get_knowledge_entry'));
        add_action('wp_ajax_kbc_delete_knowledge_entry', array($this, 'ajax_delete_knowledge_entry'));
        add_action('wp_ajax_kbc_export_knowledge_base', array($this, 'ajax_export_knowledge_base'));
    }
    
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/kbc-admin.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/kbc-admin.js',
            array('jquery'),
            $this->version,
            false
        );
        
        wp_localize_script($this->plugin_name, 'kbcAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kbc_admin_nonce')
        ));
    }
    
    public function add_menu_pages() {
        add_menu_page(
            'Knowledge Base Chatbot',
            'KB Chatbot',
            'manage_options',
            'kbc-settings',
            array($this, 'display_settings_page'),
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'kbc-settings',
            'Knowledge Base',
            'Knowledge Base',
            'manage_options',
            'kbc-knowledge-base',
            array($this, 'display_knowledge_base_page')
        );
    }
    
    public function register_settings() {
        // Register settings sections
        add_settings_section(
            'kbc_ai_settings',
            'AI Settings',
            array($this, 'render_ai_settings_section'),
            'kbc-ai-settings'
        );

        add_settings_section(
            'kbc_display_settings',
            'Chatbot Settings',
            array($this, 'render_display_settings_section'),
            'kbc-display-settings'
        );

        add_settings_section(
            'kbc_knowledge_base_settings',
            'Knowledge Base Settings',
            array($this, 'render_knowledge_base_settings_section'),
            'kbc-knowledge-base-settings'
        );

        add_settings_section(
            'kbc_calendly_settings',
            'Calendly Integration',
            array($this, 'render_calendly_settings_section'),
            'kbc-calendly-settings'
        );

        // Register settings fields
        // AI Settings
        register_setting('kbc-ai-settings', 'kbc_api_key');
        register_setting('kbc-ai-settings', 'kbc_model');
        register_setting('kbc-ai-settings', 'kbc_max_tokens');
        register_setting('kbc-ai-settings', 'kbc_temperature');

        // Display Settings
        register_setting('kbc-display-settings', 'kbc_display_method');
        register_setting('kbc-display-settings', 'kbc_chatbot_position');
        register_setting('kbc-display-settings', 'kbc_chatbot_theme');
        register_setting('kbc-display-settings', 'kbc_chatbot_icon');
        register_setting('kbc-display-settings', 'kbc_chatbot_color');
        register_setting('kbc-display-settings', 'kbc_exclude_pages');

        // Knowledge Base Settings
        register_setting('kbc-knowledge-base-settings', 'kbc_content_types');
        register_setting('kbc-knowledge-base-settings', 'kbc_update_frequency');

        // Calendly Settings
        register_setting('kbc-calendly-settings', 'kbc_calendly_url');
        register_setting('kbc-calendly-settings', 'kbc_scheduling_keywords');
    }

    public function render_ai_settings_section() {
        echo '<p>Configure your OpenAI settings below. You can use the free API key provided or enter your own.</p>';
    }

    public function render_display_settings_section() {
        echo '<p>Configure how and where the chatbot appears on your website.</p>';
    }

    public function render_knowledge_base_settings_section() {
        echo '<p>Configure how your knowledge base is managed and updated.</p>';
    }

    public function render_calendly_settings_section() {
        echo '<p>Set up Calendly integration for scheduling meetings.</p>';
    }
    
    public function display_settings_page() {
        include_once plugin_dir_path(__FILE__) . 'partials/kbc-admin-settings.php';
    }
    
    public function display_knowledge_base_page() {
        include_once plugin_dir_path(__FILE__) . 'partials/kbc-admin-knowledge-base.php';
    }
    
    public function handle_ajax_requests() {
        check_ajax_referer('kbc_admin_nonce', 'nonce');
        
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        
        switch ($action) {
            case 'kbc_update_knowledge_base':
                $this->handle_update_knowledge_base();
                break;
                
            case 'kbc_add_manual_knowledge':
                $this->handle_add_manual_knowledge();
                break;
                
            case 'kbc_get_knowledge_entry':
                $this->handle_get_knowledge_entry();
                break;
                
            case 'kbc_delete_knowledge_entry':
                $this->handle_delete_knowledge_entry();
                break;
                
            case 'kbc_export_knowledge_base':
                $this->handle_export_knowledge_base();
                break;
        }
    }
    
    private function handle_update_knowledge_base() {
        $result = $this->knowledge_base->update_knowledge_base();
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => 'Knowledge base updated successfully',
                'count' => $this->knowledge_base->get_total_entries()
            ));
        }
    }
    
    private function handle_add_manual_knowledge() {
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
        $metadata = isset($_POST['metadata']) ? sanitize_textarea_field($_POST['metadata']) : '';
        
        if (empty($type) || empty($content)) {
            wp_send_json_error('Type and content are required');
        }
        
        $result = $this->knowledge_base->add_manual_entry($type, $content, $metadata);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => 'Knowledge entry added successfully',
                'entry' => $result
            ));
        }
    }
    
    private function handle_get_knowledge_entry() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error('Invalid entry ID');
        }
        
        $entry = $this->knowledge_base->get_entry($id);
        
        if (!$entry) {
            wp_send_json_error('Entry not found');
        }
        
        wp_send_json_success($entry);
    }
    
    private function handle_delete_knowledge_entry() {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error('Invalid entry ID');
        }
        
        $result = $this->knowledge_base->delete_entry($id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success('Entry deleted successfully');
        }
    }
    
    private function handle_export_knowledge_base() {
        $entries = $this->knowledge_base->export_knowledge_base();
        
        if (is_wp_error($entries)) {
            wp_send_json_error($entries->get_error_message());
        }
        
        $filename = 'knowledge-base-export-' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($entries);
        exit;
    }

    public function ajax_update_knowledge_base() {
        check_ajax_referer('kbc_admin_nonce', 'nonce');
        
        try {
            $result = $this->knowledge_base->update_knowledge_base();
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            } else {
                wp_send_json_success(array(
                    'message' => 'Knowledge base updated successfully',
                    'count' => $this->knowledge_base->get_total_entries()
                ));
            }
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function ajax_get_knowledge_entry() {
        check_ajax_referer('kbc_admin_nonce', 'nonce');
        
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if (!$id) {
                wp_send_json_error('Invalid entry ID');
            }
            
            $entry = $this->knowledge_base->get_entry($id);
            
            if (!$entry) {
                wp_send_json_error('Entry not found');
            }
            
            // Decode metadata if it's a JSON string
            if (is_string($entry->metadata)) {
                $entry->metadata = json_decode($entry->metadata, true);
            }
            
            wp_send_json_success($entry);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function ajax_delete_knowledge_entry() {
        check_ajax_referer('kbc_admin_nonce', 'nonce');
        
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if (!$id) {
                wp_send_json_error('Invalid entry ID');
            }
            
            $result = $this->knowledge_base->delete_entry($id);
            
            if ($result === false) {
                wp_send_json_error('Failed to delete entry');
            } else {
                wp_send_json_success('Entry deleted successfully');
            }
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Handle AJAX request to add manual knowledge entry
     */
    public function ajax_add_manual_knowledge() {
        check_ajax_referer('kbc_admin_nonce', 'nonce');
        
        try {
            // Get and sanitize input
            $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
            $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
            $metadata = isset($_POST['metadata']) ? sanitize_textarea_field($_POST['metadata']) : '';
            
            // Validate required fields
            if (empty($type) || empty($content)) {
                wp_send_json_error('Type and content are required');
                return;
            }
            
            // Clean the content
            // $content = $this->knowledge_base->clean_content($content);
            
            if (empty($content)) {
                wp_send_json_error('Content is empty after cleaning');
                return;
            }
            
            // Parse metadata if provided
            $metadata_array = array();
            if (!empty($metadata)) {
                $metadata_array = json_decode($metadata, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Invalid metadata JSON format');
                    return;
                }
            }
            
            // Add default metadata
            $metadata_array = array_merge($metadata_array, array(
                'title' => $type,
                'url' => '',
                'author' => 'Manual Entry',
                'date' => current_time('mysql')
            ));
            
            // Add the entry
            $result = $this->knowledge_base->add_manual_entry($type, $content, $metadata_array);
            
            if ($result) {
                wp_send_json_success('Entry added successfully');
            } else {
                wp_send_json_error('Failed to add entry');
            }
        } catch (Exception $e) {
            error_log('KBC: Error in ajax_add_manual_knowledge: ' . $e->getMessage());
            wp_send_json_error('An error occurred: ' . $e->getMessage());
        }
    }
} 