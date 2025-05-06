jQuery(document).ready(function($) {
    // Update Knowledge Base button click handler
    $('#kbc-update-knowledge-base').on('click', function() {
        var $button = $(this);
        $button.prop('disabled', true).text('Updating...');

        $.ajax({
            url: kbcAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_update_knowledge_base',
                nonce: kbcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Refresh the page to show updated data
                    window.location.reload();
                } else {
                    console.error('Update failed:', response);
                    alert('Failed to update knowledge base: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('An error occurred while updating the knowledge base. Please check the console for details.');
            },
            complete: function() {
                $button.prop('disabled', false).text('Update Knowledge Base');
            }
        });
    });

    // Search functionality
    $('#kbc-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.kbc-entry').each(function() {
            var content = $(this).find('.kbc-entry-content').text().toLowerCase();
            var type = $(this).find('.kbc-entry-type').text().toLowerCase();
            if (content.indexOf(searchTerm) > -1 || type.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // View entry modal
    $('.kbc-view-entry').on('click', function() {
        var entryId = $(this).closest('.kbc-entry').data('id');
        var $modal = $('#kbc-view-entry-modal');
        
        $.ajax({
            url: kbcAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_get_knowledge_entry',
                nonce: kbcAdmin.nonce,
                id: entryId
            },
            success: function(response) {
                if (response.success) {
                    var entry = response.data;
                    var content = '<div class="kbc-entry-details">';
                    content += '<h3>' + (entry.metadata.title || 'Untitled') + '</h3>';
                    content += '<div class="kbc-entry-meta">';
                    content += '<span class="kbc-entry-type">Type: ' + entry.type + '</span>';
                    content += '<span class="kbc-entry-date">Last Updated: ' + entry.last_updated + '</span>';
                    if (entry.metadata.url) {
                        content += '<span class="kbc-entry-url"><a href="' + entry.metadata.url + '" target="_blank">View Original</a></span>';
                    }
                    content += '</div>';
                    content += '<div class="kbc-entry-content">' + entry.content + '</div>';
                    content += '</div>';
                    $('#kbc-view-entry-content').html(content);
                    $modal.show();
                } else {
                    console.error('Failed to get entry:', response);
                    alert('Failed to retrieve entry: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('An error occurred while retrieving the entry. Please check the console for details.');
            }
        });
    });

    // Delete entry
    $('.kbc-delete-entry').on('click', function() {
        var entryId = $(this).closest('.kbc-entry').data('id');
        var $modal = $('#kbc-delete-entry-modal');
        $modal.data('entry-id', entryId).show();
    });

    // Confirm delete
    $('.kbc-confirm-delete').on('click', function() {
        var entryId = $('#kbc-delete-entry-modal').data('entry-id');
        var $modal = $('#kbc-delete-entry-modal');
        
        $.ajax({
            url: kbcAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_delete_knowledge_entry',
                nonce: kbcAdmin.nonce,
                id: entryId
            },
            success: function(response) {
                if (response.success) {
                    // Refresh the page to show updated data
                    window.location.reload();
                } else {
                    console.error('Delete failed:', response);
                    alert('Failed to delete entry: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('An error occurred while deleting the entry. Please check the console for details.');
            }
        });
    });

    // Close modals
    $('.kbc-modal-close').on('click', function() {
        $(this).closest('.kbc-modal').hide();
    });

    // Cancel delete
    $('.kbc-cancel-delete').on('click', function() {
        $('#kbc-delete-entry-modal').hide();
    });

    // Add manual entry form submission
    $('#kbc-add-manual-entry').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        
        // Get form values safely
        var type = $form.find('#kbc-entry-type').val() || '';
        var content = $form.find('#kbc-entry-content').val() || '';
        var metadata = $form.find('#kbc-entry-metadata').val() || '';
        
        // Debug values
        console.log('Form values:', {
            type: type,
            content: content,
            metadata: metadata
        });
        
        // Trim values
        type = type.trim();
        content = content.trim();
        metadata = metadata.trim();
        
        // Validate form
        if (!type || !content) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Validate metadata JSON if provided
        if (metadata) {
            try {
                JSON.parse(metadata);
            } catch (e) {
                alert('Invalid JSON in metadata field');
                return;
            }
        }
        
        $button.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: kbcAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_add_manual_knowledge',
                nonce: kbcAdmin.nonce,
                type: type,
                content: content,
                metadata: metadata
            },
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    // Refresh the page to show the new entry
                    window.location.reload();
                } else {
                    console.error('Add failed:', response);
                    alert('Failed to add entry: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                console.error('Response:', xhr.responseText);
                alert('An error occurred while adding the entry. Please check the console for details.');
            },
            complete: function() {
                $button.prop('disabled', false).text('Add Entry');
            }
        });
    });
}); 