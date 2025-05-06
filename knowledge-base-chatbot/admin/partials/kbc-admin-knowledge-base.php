<?php
if (!defined('ABSPATH')) {
    exit;
}

$total_entries = $this->knowledge_base->get_total_entries();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="kbc-admin-container">
        <div class="kbc-admin-card">
            <div class="kbc-status">
                <div class="kbc-stats">
                    <div class="kbc-stat">
                        <span class="kbc-stat-label">Total Entries</span>
                        <span class="kbc-stat-value"><?php echo esc_html($total_entries); ?></span>
                    </div>
                </div>
                <button id="kbc-update-knowledge-base" class="button button-primary">Update Knowledge Base</button>
            </div>
        </div>
        
        <div class="kbc-admin-card">
            <div class="kbc-knowledge-container">
                <div class="kbc-knowledge-header">
                    <h2>Knowledge Base Entries</h2>
                    <div class="kbc-search-box">
                        <input type="text" id="kbc-search" placeholder="Search entries...">
                    </div>
                </div>
                
                <div class="kbc-entries-list">
                    <?php
                    $entries = $this->knowledge_base->get_entries();
                    if (!empty($entries)) {
                        foreach ($entries as $entry) {
                            ?>
                            <div class="kbc-entry" data-id="<?php echo esc_attr($entry->id); ?>">
                                <div class="kbc-entry-header">
                                    <span class="kbc-entry-type"><?php echo esc_html($entry->type); ?></span>
                                    <span class="kbc-entry-date"><?php echo esc_html(date('Y-m-d H:i', strtotime($entry->last_updated))); ?></span>
                                </div>
                                <div class="kbc-entry-content">
                                    <?php echo wp_trim_words(esc_html($entry->content), 50); ?>
                                </div>
                                <div class="kbc-entry-actions">
                                    <button class="button kbc-view-entry">View</button>
                                    <button class="button kbc-delete-entry">Delete</button>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p>No knowledge base entries found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="kbc-admin-card">
            <h2>Add Manual Entry</h2>
            <form id="kbc-add-manual-entry">
                <div class="kbc-form-group">
                    <label for="kbc-entry-type">Type</label>
                    <input type="text" name="type" id="kbc-entry-type" required>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc-entry-content">Content</label>
                    <textarea name="content" id="kbc-entry-content" rows="5" required></textarea>
                </div>
                
                <div class="kbc-form-group">
                    <label for="kbc-entry-metadata">Metadata (JSON)</label>
                    <textarea name="metadata" id="kbc-entry-metadata" rows="3"></textarea>
                </div>
                
                <button type="submit" class="button button-primary">Add Entry</button>
            </form>
        </div>
    </div>
</div>

<!-- View Entry Modal -->
<div id="kbc-view-entry-modal" class="kbc-modal">
    <div class="kbc-modal-content">
        <span class="kbc-modal-close">&times;</span>
        <h2>View Entry</h2>
        <div id="kbc-view-entry-content"></div>
    </div>
</div>

<!-- Delete Entry Modal -->
<div id="kbc-delete-entry-modal" class="kbc-modal">
    <div class="kbc-modal-content">
        <span class="kbc-modal-close">&times;</span>
        <h2>Delete Entry</h2>
        <p>Are you sure you want to delete this entry?</p>
        <div class="kbc-modal-actions">
            <button class="button kbc-confirm-delete">Delete</button>
            <button class="button kbc-cancel-delete">Cancel</button>
        </div>
    </div>
</div>

<!-- Add Manual Entry Form -->
<div class="kbc-modal" id="kbc-add-entry-modal">
    <div class="kbc-modal-content">
        <div class="kbc-modal-header">
            <h2>Add Manual Entry</h2>
            <span class="kbc-modal-close">&times;</span>
        </div>
        <div class="kbc-modal-body">
            <form id="kbc-add-manual-entry">
                <div class="kbc-form-group">
                    <label for="kbc-entry-type">Type</label>
                    <input type="text" id="kbc-entry-type" name="type" required>
                </div>
                <div class="kbc-form-group">
                    <label for="kbc-entry-content">Content</label>
                    <textarea id="kbc-entry-content" name="content" rows="10" required></textarea>
                </div>
                <div class="kbc-form-group">
                    <label for="kbc-entry-metadata">Metadata (JSON)</label>
                    <textarea id="kbc-entry-metadata" name="metadata" rows="3"></textarea>
                </div>
                <div class="kbc-modal-footer">
                    <button type="submit" class="button button-primary">Add Entry</button>
                    <button type="button" class="button kbc-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div> 