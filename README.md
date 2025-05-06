# Knowledge Base Chatbot

A WordPress plugin that creates an AI-powered chatbot interface for your knowledge base, with Calendly integration for scheduling meetings.

## Features

- 🔍 **Knowledge Base Management**
  - Automatically extracts content from WordPress posts, pages, and custom post types
  - Stores content in a structured format for efficient retrieval
  - Admin panel for managing and editing knowledge base content
  - Support for manual knowledge entry

- 💬 **AI-Powered Chatbot**
  - Uses OpenAI or Google Vertex AI for intelligent responses
  - Context-aware responses based on your knowledge base
  - Maintains conversation history for better context
  - Customizable appearance and positioning

- 📅 **Calendly Integration**
  - Automatic scheduling suggestions based on conversation context
  - Customizable scheduling keywords
  - Seamless integration with Calendly scheduling widget

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- OpenAI API key or Google Cloud credentials (for Vertex AI)

## Installation

1. Upload the `knowledge-base-chatbot` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the plugin settings page and configure:
   - AI provider (OpenAI or Vertex AI)
   - API credentials
   - Knowledge base settings
   - Chatbot appearance
   - Calendly integration

## Configuration

### AI Settings
- Choose between OpenAI and Google Vertex AI
- Enter your API key or credentials
- Configure model settings (temperature, max tokens)

### Knowledge Base Settings
- Select content types to include in the knowledge base
- Set update frequency
- Configure content cleaning rules

### Chatbot Settings
- Choose display method (automatic or shortcode)
- Set position and theme
- Customize colors and icons
- Configure scheduling keywords

### Calendly Settings
- Enter your Calendly URL
- Configure scheduling triggers

## Usage

### Shortcode
Use the following shortcode to display the chatbot on any page:

```
[knowledge_base_chatbot]
```

Optional parameters:
- `position`: bottom-right, bottom-left, top-right, top-left
- `theme`: light, dark

Example:
```
[knowledge_base_chatbot position="bottom-right" theme="light"]
```

### Automatic Display
The chatbot can be automatically displayed on all pages except those specified in the exclude list.

## Development

### File Structure
```
knowledge-base-chatbot/
├── admin/              # Admin interface files
├── includes/           # Core plugin classes
├── languages/          # Translation files
├── public/             # Frontend files
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── partials/      # Template parts
├── knowledge-base-chatbot.php
└── README.md
```

### Hooks and Filters

#### Actions
- `kbc_before_knowledge_base_update`
- `kbc_after_knowledge_base_update`
- `kbc_before_chat_response`
- `kbc_after_chat_response`

#### Filters
- `kbc_content_types`
- `kbc_scheduling_keywords`
- `kbc_chatbot_settings`
- `kbc_calendly_settings`

## Support

For support, feature requests, or bug reports, please create an issue in the GitHub repository.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- OpenAI API
- Google Vertex AI
- Calendly
- WordPress 