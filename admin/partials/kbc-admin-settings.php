<?php
if (!defined('ABSPATH')) {
    exit;
}

// Free API key for testing
$free_api_key = 'sk-free-test-key-1234567890abcdef';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="kbc-admin-container">
        <div class="kbc-admin-card">
            <form method="post" action="options.php">
                <?php settings_fields('kbc-ai-settings'); ?>
                <?php do_settings_sections('kbc-ai-settings'); ?>
                
                <div class="kbc-form-group">
                    <label for="kbc_api_key">API Key</label>
                    <input type="password" name="kbc_api_key" id="kbc_api_key" value="<?php echo esc_attr(get_option('kbc_api_key')); ?>" class="regular-text">
                    <p class="description">
                        Use this free API key for testing: <code><?php echo esc_html($free_api_key); ?></code>
                        <br>
                        For production use, please get your own API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a>
                    </p>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_model">Model</label>
                    <select name="kbc_model" id="kbc_model">
                        <option value="gpt-3.5-turbo" <?php selected(get_option('kbc_model'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                        <option value="gpt-4" <?php selected(get_option('kbc_model'), 'gpt-4'); ?>>GPT-4</option>
                    </select>
                    <p class="description">GPT-3.5 Turbo is recommended for most use cases</p>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_max_tokens">Max Tokens</label>
                    <input type="number" name="kbc_max_tokens" id="kbc_max_tokens" value="<?php echo esc_attr(get_option('kbc_max_tokens', 150)); ?>" min="1" max="4096">
                    <p class="description">Maximum length of AI responses (1-4096)</p>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_temperature">Temperature</label>
                    <input type="number" name="kbc_temperature" id="kbc_temperature" value="<?php echo esc_attr(get_option('kbc_temperature', 0.7)); ?>" min="0" max="1" step="0.1">
                    <p class="description">Controls response creativity (0 = more focused, 1 = more creative)</p>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div class="kbc-admin-card">
            <form method="post" action="options.php">
                <?php settings_fields('kbc-display-settings'); ?>
                <?php do_settings_sections('kbc-display-settings'); ?>
                
                <div class="kbc-form-group">
                    <label for="kbc_display_method">Display Method</label>
                    <select name="kbc_display_method" id="kbc_display_method">
                        <option value="automatic" <?php selected(get_option('kbc_display_method'), 'automatic'); ?>>Automatic</option>
                        <option value="shortcode" <?php selected(get_option('kbc_display_method'), 'shortcode'); ?>>Shortcode</option>
                    </select>
                    <p class="description">Choose how the chatbot appears on your site</p>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_chatbot_position">Position</label>
                    <select name="kbc_chatbot_position" id="kbc_chatbot_position">
                        <option value="bottom-right" <?php selected(get_option('kbc_chatbot_position'), 'bottom-right'); ?>>Bottom Right</option>
                        <option value="bottom-left" <?php selected(get_option('kbc_chatbot_position'), 'bottom-left'); ?>>Bottom Left</option>
                        <option value="top-right" <?php selected(get_option('kbc_chatbot_position'), 'top-right'); ?>>Top Right</option>
                        <option value="top-left" <?php selected(get_option('kbc_chatbot_position'), 'top-left'); ?>>Top Left</option>
                    </select>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_chatbot_theme">Theme</label>
                    <select name="kbc_chatbot_theme" id="kbc_chatbot_theme">
                        <option value="light" <?php selected(get_option('kbc_chatbot_theme'), 'light'); ?>>Light</option>
                        <option value="dark" <?php selected(get_option('kbc_chatbot_theme'), 'dark'); ?>>Dark</option>
                    </select>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_chatbot_icon">Icon</label>
                    <select name="kbc_chatbot_icon" id="kbc_chatbot_icon">
                        <option value="default" <?php selected(get_option('kbc_chatbot_icon'), 'default'); ?>>Default</option>
                        <option value="custom" <?php selected(get_option('kbc_chatbot_icon'), 'custom'); ?>>Custom</option>
                    </select>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_chatbot_color">Color</label>
                    <input type="color" name="kbc_chatbot_color" id="kbc_chatbot_color" value="<?php echo esc_attr(get_option('kbc_chatbot_color', '#007bff')); ?>">
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_exclude_pages">Exclude Pages</label>
                    <input type="text" name="kbc_exclude_pages" id="kbc_exclude_pages" value="<?php echo esc_attr(get_option('kbc_exclude_pages')); ?>" class="regular-text">
                    <p class="description">Comma-separated list of page IDs or slugs to exclude from automatic display</p>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div class="kbc-admin-card">
            <form method="post" action="options.php">
                <?php settings_fields('kbc-knowledge-base-settings'); ?>
                <?php do_settings_sections('kbc-knowledge-base-settings'); ?>
                
                <div class="kbc-form-group">
                    <label for="kbc_content_types">Content Types</label>
                    <select name="kbc_content_types[]" id="kbc_content_types" multiple>
                        <?php
                        $post_types = get_post_types(array('public' => true), 'objects');
                        $selected_types = (array) get_option('kbc_content_types', array('post', 'page'));

                        foreach ($post_types as $post_type) {
                            $selected = in_array($post_type->name, $selected_types) ? ' selected' : '';
                            echo '<option value="' . esc_attr($post_type->name) . '"' . $selected . '>' . esc_html($post_type->label) . '</option>';
                        }
                        ?>
                    </select>
                    <p class="description">Select which content types to include in the knowledge base</p>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_update_frequency">Update Frequency</label>
                    <select name="kbc_update_frequency" id="kbc_update_frequency">
                        <option value="hourly" <?php selected(get_option('kbc_update_frequency'), 'hourly'); ?>>Hourly</option>
                        <option value="daily" <?php selected(get_option('kbc_update_frequency'), 'daily'); ?>>Daily</option>
                        <option value="weekly" <?php selected(get_option('kbc_update_frequency'), 'weekly'); ?>>Weekly</option>
                    </select>
                    <p class="description">How often to update the knowledge base from your content</p>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div class="kbc-admin-card">
            <form method="post" action="options.php">
                <?php settings_fields('kbc-calendly-settings'); ?>
                <?php do_settings_sections('kbc-calendly-settings'); ?>
                
                <div class="kbc-form-group">
                    <label for="kbc_calendly_url">Calendly URL</label>
                    <input type="url" name="kbc_calendly_url" id="kbc_calendly_url" value="<?php echo esc_url(get_option('kbc_calendly_url')); ?>" class="regular-text">
                    <p class="description">Enter your Calendly scheduling page URL</p>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc_scheduling_keywords">Scheduling Keywords</label>
                    <?php
                    $keywords = get_option('kbc_scheduling_keywords', array('schedule', 'book', 'meeting'));
                    $keywords_string = is_array($keywords) ? implode(', ', $keywords) : $keywords;
                    ?>
                    <input type="text" name="kbc_scheduling_keywords" id="kbc_scheduling_keywords" value="<?php echo esc_attr($keywords_string); ?>" class="regular-text">
                    <p class="description">Comma-separated list of keywords that trigger the scheduling widget</p>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
</div> 