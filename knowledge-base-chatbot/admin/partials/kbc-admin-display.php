<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="kbc-admin-container">
        <div class="kbc-admin-card">
            <h2>Knowledge Base Status</h2>
            <div class="kbc-status">
                <p>Last updated: <span id="kbc-last-updated"><?php echo esc_html(get_option('kbc_last_updated', 'Never')); ?></span></p>
                <button id="kbc-update-now" class="button button-primary">Update Now</button>
            </div>
        </div>

        <div class="kbc-admin-card">
            <h2>Quick Stats</h2>
            <div class="kbc-stats">
                <div class="kbc-stat">
                    <span class="kbc-stat-number" id="kbc-total-entries">0</span>
                    <span class="kbc-stat-label">Total Knowledge Entries</span>
                </div>
                <div class="kbc-stat">
                    <span class="kbc-stat-number" id="kbc-total-chats">0</span>
                    <span class="kbc-stat-label">Total Chat Sessions</span>
                </div>
            </div>
        </div>

        <div class="kbc-admin-card">
            <h2>Add Manual Knowledge</h2>
            <form id="kbc-add-knowledge-form">
                <div class="kbc-form-group">
                    <label for="kbc-knowledge-title">Title</label>
                    <input type="text" id="kbc-knowledge-title" name="title" required>
                </div>
                <div class="kbc-form-group">
                    <label for="kbc-knowledge-content">Content</label>
                    <textarea id="kbc-knowledge-content" name="content" rows="5" required></textarea>
                </div>
                <div class="kbc-form-group">
                    <label for="kbc-knowledge-meta">Additional Metadata (JSON)</label>
                    <textarea id="kbc-knowledge-meta" name="meta" rows="3"></textarea>
                </div>
                <button type="submit" class="button button-primary">Add Knowledge</button>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Update knowledge base
    $('#kbc-update-now').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Updating...');

        $.ajax({
            url: kbc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_update_knowledge_base',
                nonce: kbc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#kbc-last-updated').text(new Date().toLocaleString());
                    alert('Knowledge base updated successfully!');
                } else {
                    alert('Failed to update knowledge base: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while updating the knowledge base.');
            },
            complete: function() {
                button.prop('disabled', false).text('Update Now');
            }
        });
    });

    // Add manual knowledge
    $('#kbc-add-knowledge-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var button = form.find('button[type="submit"]');
        
        button.prop('disabled', true).text('Adding...');

        $.ajax({
            url: kbc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_add_manual_knowledge',
                nonce: kbc_admin.nonce,
                title: $('#kbc-knowledge-title').val(),
                content: $('#kbc-knowledge-content').val(),
                meta: $('#kbc-knowledge-meta').val()
            },
            success: function(response) {
                if (response.success) {
                    form[0].reset();
                    alert('Knowledge added successfully!');
                } else {
                    alert('Failed to add knowledge: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while adding knowledge.');
            },
            complete: function() {
                button.prop('disabled', false).text('Add Knowledge');
            }
        });
    });
});
</script> 