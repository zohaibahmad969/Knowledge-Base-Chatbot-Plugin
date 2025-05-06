<?php

class KBC_Core {
    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $knowledge_base;
    protected $chatbot;
    protected $calendly;
    protected $admin;
    protected $public;

    public function __construct() {
        $this->plugin_name = 'knowledge-base-chatbot';
        $this->version = KBC_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        // Load required classes
        $this->knowledge_base = new KBC_Knowledge_Base();
        $this->chatbot = new KBC_Chatbot();
        $this->calendly = new KBC_Calendly();
        $this->admin = new KBC_Admin($this->get_plugin_name(), $this->get_version(), $this->knowledge_base, $this->calendly);
        $this->public = new KBC_Public($this->get_plugin_name(), $this->get_version(), $this->chatbot, $this->calendly);
    }

    private function set_locale() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            $this->plugin_name,
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    private function define_admin_hooks() {
        // Admin styles and scripts
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_scripts'));

        // Admin menu
        add_action('admin_menu', array($this->admin, 'add_menu_pages'));

        // Register settings
        add_action('admin_init', array($this->admin, 'register_settings'));

        // AJAX handlers
        add_action('wp_ajax_kbc_update_knowledge_base', array($this->admin, 'ajax_update_knowledge_base'));
        add_action('wp_ajax_kbc_add_manual_knowledge', array($this->admin, 'ajax_add_manual_knowledge'));
        add_action('wp_ajax_kbc_get_knowledge_entry', array($this->admin, 'ajax_get_knowledge_entry'));
        add_action('wp_ajax_kbc_delete_knowledge_entry', array($this->admin, 'ajax_delete_knowledge_entry'));
        add_action('wp_ajax_kbc_export_knowledge_base', array($this->admin, 'ajax_export_knowledge_base'));
    }

    private function define_public_hooks() {
        // Public styles and scripts
        add_action('wp_enqueue_scripts', array($this->public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this->public, 'enqueue_scripts'));

        // Shortcode
        add_shortcode('knowledge_base_chatbot', array($this->public, 'chatbot_shortcode'));

        // AJAX handlers
        add_action('wp_ajax_kbc_chat', array($this->public, 'ajax_chat'));
        add_action('wp_ajax_nopriv_kbc_chat', array($this->public, 'ajax_chat'));

        // Display chatbot
        add_action('wp_footer', array($this->public, 'display_chatbot'));
    }

    public function run() {
        // Initialize knowledge base
        $this->knowledge_base->init();

        // Initialize chatbot
        $this->chatbot->init();

        // Initialize Calendly
        $this->calendly->init();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
} 