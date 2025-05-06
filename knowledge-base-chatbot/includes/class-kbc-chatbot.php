<?php

class KBC_Chatbot {
    private $knowledge_base;
    private $api_key;
    private $model;
    private $max_tokens;
    private $temperature;
    private $chat_history_table;
    private $max_context_length = 4000; // Maximum context length in tokens

    public function __construct() {
        global $wpdb;
        $this->knowledge_base = new KBC_Knowledge_Base();
        $this->chat_history_table = $wpdb->prefix . 'kbc_chat_history';
        $this->api_key = get_option('kbc_api_key', '');
        $this->model = get_option('kbc_model', 'gpt-3.5-turbo');
        $this->max_tokens = get_option('kbc_max_tokens', 150);
        $this->temperature = get_option('kbc_temperature', 0.7);
    }

    public function init() {
        // Initialize any required components
    }

    public function process_message($message, $session_id = '') {
        if (empty($session_id)) {
            $session_id = wp_generate_password(32, false);
        }

        // Get chat history for context
        $chat_history = $this->get_chat_history($session_id, 5);
        
        // Search knowledge base for relevant content
        $relevant_content = $this->get_relevant_content($message, $chat_history);
        
        // Check for scheduling keywords
        $show_scheduling = $this->should_show_scheduling($message) || 
                          $this->should_show_scheduling_after_response($chat_history);

        // Generate response using AI
        $response = $this->generate_response($message, $relevant_content, $chat_history);

        // Save chat history
        $this->save_chat_history($session_id, $message, $response);

        return array(
            'response' => $response,
            'session_id' => $session_id,
            'show_scheduling' => $show_scheduling
        );
    }

    private function get_chat_history($session_id, $limit = 5) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT user_message, bot_response 
             FROM {$this->chat_history_table} 
             WHERE session_id = %s 
             ORDER BY created_at DESC 
             LIMIT %d",
            $session_id,
            $limit
        );
        
        $history = $wpdb->get_results($query);
        
        $formatted_history = array();
        foreach ($history as $entry) {
            $formatted_history[] = array(
                'role' => 'user',
                'content' => $entry->user_message
            );
            $formatted_history[] = array(
                'role' => 'assistant',
                'content' => $entry->bot_response
            );
        }
        
        return array_reverse($formatted_history);
    }

    private function get_relevant_content($message, $chat_history = array()) {
        // Combine message with recent chat history for better context
        $search_query = $message;
        if (!empty($chat_history)) {
            $last_user_message = '';
            foreach ($chat_history as $entry) {
                if ($entry['role'] === 'user') {
                    $last_user_message = $entry['content'];
                }
            }
            if (!empty($last_user_message)) {
                $search_query = $last_user_message . ' ' . $message;
            }
        }

        $results = $this->knowledge_base->search($search_query, 5);
        
        $content = '';
        foreach ($results as $result) {
            $metadata = json_decode($result->metadata, true);
            $content .= "Title: " . $metadata['title'] . "\n";
            $content .= "Content: " . $result->content . "\n\n";
            
            // Check if we've reached the maximum context length
            if (strlen($content) > $this->max_context_length) {
                break;
            }
        }

        return $content;
    }

    private function generate_response($message, $context, $chat_history = array()) {
        if (empty($this->api_key)) {
            return 'I apologize, but the chatbot is not properly configured. Please contact the administrator.';
        }

        try {
            $messages = array(
                array(
                    'role' => 'system',
                    'content' => "You are a helpful assistant. Use the following context to answer the user's question. If the answer is not in the context, say so and don't make up information."
                )
            );

            // Add context if available
            if (!empty($context)) {
                $messages[] = array(
                    'role' => 'system',
                    'content' => "Context:\n" . $context
                );
            }

            // Add chat history
            foreach ($chat_history as $entry) {
                $messages[] = $entry;
            }

            // Add current message
            $messages[] = array(
                'role' => 'user',
                'content' => $message
            );

            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->max_tokens,
                    'temperature' => $this->temperature
                )),
                'timeout' => 30
            ));

            if (is_wp_error($response)) {
                error_log('OpenAI API Error: ' . $response->get_error_message());
                return 'I apologize, but I encountered an error while processing your request. Please try again later.';
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($body['choices'][0]['message']['content'])) {
                return trim($body['choices'][0]['message']['content']);
            } else {
                error_log('OpenAI API Error: Invalid response format. Response: ' . print_r($body, true));
                return 'I apologize, but I encountered an error while processing your request. Please try again later.';
            }
        } catch (Exception $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            return 'I apologize, but I encountered an error while processing your request. Please try again later.';
        }
    }

    private function save_chat_history($session_id, $message, $response) {
        global $wpdb;
        
        $wpdb->insert(
            $this->chat_history_table,
            array(
                'session_id' => $session_id,
                'user_message' => $message,
                'bot_response' => $response,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }

    private function should_show_scheduling($message) {
        $keywords = get_option('kbc_scheduling_keywords', 'schedule,meeting,appointment,book,calendar');
        $keywords = array_map('trim', explode(',', $keywords));
        $message = strtolower($message);
        
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function should_show_scheduling_after_response($chat_history) {
        if (empty($chat_history)) {
            return false;
        }
        
        $last_bot_response = '';
        foreach (array_reverse($chat_history) as $entry) {
            if ($entry['role'] === 'assistant') {
                $last_bot_response = $entry['content'];
                break;
            }
        }
        
        return strpos(strtolower($last_bot_response), 'schedule') !== false ||
               strpos(strtolower($last_bot_response), 'meeting') !== false ||
               strpos(strtolower($last_bot_response), 'appointment') !== false;
    }
} 