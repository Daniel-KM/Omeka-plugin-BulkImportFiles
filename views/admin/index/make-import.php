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

<h1><?= __('Make bulk import files') ?></h1>
<form method="post" class="make_import_form">
    <section class="ten columns alpha omega">
        <fieldset id="fieldset-make_import_form">
            <legend><?= __('Select folder') ?></legend>
            <div class="field">
                <div class="two columns alpha">
                    <?= $this->formLabel('directory', __('Source directory on server')) ?>
                </div>
                <div class='inputs five columns'>
                    <?= $this->formText('directory', BASE_DIR . '/files/import', null) ?>
                    <p class="explanation">
                        <?= __('Enter the absolute path to the directory where files to be imported will be added.') ?>
                        <?= sprintf(__('The directory can be anywhere on your server. Your root path is: %s'), $_SERVER['DOCUMENT_ROOT']) ?>
                    </p>
                </div>
                <div class='inputs three columns omega'>
                    <div id="save" class="panel">
                        <?= $this->formSubmit('add-item-submit', __('Check'), array('class' => 'submit big green button check_button')) ?>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="two columns alpha">
                    <?= $this->formLabel('delete_file', __('Delete original file from the folder?')) ?>
                </div>
                <div class='inputs five columns omega'>
                    <?= $this->formCheckbox('delete_file', true, array('checked' => false, 'id' => 'delete-file')) ?>
                    <p class="explanation">
                        <?= __('Do you want to delete a file from the source directory after it has been imported? If so, the directory must be server-writable.') ?>
                    </p>
                </div>
            </div>
        </fieldset>
    </section>
</form>

<div class="response"></div>

<div class="modal-loader">
    <div class="modal-loader-info"><?= __('Please wait, analysis in progress…') ?></div>
</div>
<?php echo foot(); ?>
