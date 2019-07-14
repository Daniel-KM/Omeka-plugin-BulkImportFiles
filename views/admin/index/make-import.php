<?php

$head = array('title' => html_escape(__('Bulk Import Files')));

queue_css_file('bulk-import-files');
queue_js_file('bulk-import-files');

$script = 'var basePath = "' . PUBLIC_BASE_URL.'"';
$this->headScript()
    ->appendScript($script);

echo head($head);
echo common('bulkimportfiles-nav');

?>
<?php echo flash(); ?>

<h1>
    <span class="subhead"><?php echo __('Make bulk import files');?></span>
    <span class="title"><?php echo __('Bulk import files'); ?></span>
</h1>
<form method="post" class="make_import_form">
    <div class="field required">
        <div class="field-meta">
            <label for="directory"><?= __('Source directory') ?></label>
            <a href="#" class="expand" aria-label="<?= __('Expand') ?>"></a>
            <div class="collapsible">
                <div class="field-description"><?= __('Enter the absolute path to the directory where files to be imported will be added.') ?>
                    <?= sprintf(__('The directory can be anywhere on your server. Your root path is: %s'), $_SERVER['DOCUMENT_ROOT']) ?>
                </div>
            </div>
        </div>
        <div class="inputs">
            <input type="text" name="directory" required="required" id="directory" value="<?= BASE_DIR ?>/files/import">
        </div>
        <button type="submit" name="add-item-submit" class="check_button"><?= __('Check') ?></button>
    </div>
    <div class="field">
        <div class="field-meta">
            <label for="delete-file"><?= __('Delete original file from the folder?') ?></label>
            <a href="#" class="expand" aria-label="<?= __('Expand') ?>"></a>
            <div class="collapsible">
                <div class="field-description">
                    <?= __('Do you want to delete a file from the source directory after it has been imported? If so, the directory must be server-writable.') ?>
                </div>
            </div>
        </div>
        <div class="inputs">
            <input type="hidden" name="delete_file" value="no">
            <input type="checkbox" name="delete_file" id="delete-file" value="no">
        </div>
    </div>
</form>

<div class="response"></div>

<div class="modal-loader">
    <div class="modal-loader-info"><?= __('Please wait, analysis in progress…') ?></div>
</div>
<?php echo foot(); ?>
