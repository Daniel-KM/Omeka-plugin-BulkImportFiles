<?php
/**
 * @var Zend_View $this
 */

$head = array(
    'title' => html_escape(__('Bulk Import Files')),
    'bodyclass' => 'bulk-import-files make-import',
);

queue_css_file('bulk-import-files');
$script = 'var basePath = "' . PUBLIC_BASE_URL.'"';
$this->headScript()
    ->appendScript($script);
queue_js_file('bulk-import-files');

echo head($head);
echo common('bulkimportfiles-nav');

?>
<?php echo flash(); ?>

<h1><?= __('Make bulk import files') ?></h1>

<form method="post" class="make_import_form">
    <section class="ten columns alpha omega">
        <fieldset id="fieldset-make_import_form">
            <legend><?= __('Select folder') ?></legend>
            <p class="explanation">
                <?= __('You can either select a folder on your computer or on the server.') ?>
                <?= __('It is recommended to use the server for big import to allow to continue to use the browser and to avoid the server upload limits for total size and number of files.') ?>
            </p>
            <div class="field">
                <div class="two columns alpha">
                    <?= $this->formLabel('directory', __('Source directory on server')) ?>
                </div>
                <div class="inputs five columns">
                    <?= $this->formText('directory', BASE_DIR . '/files/import', null) ?>
                    <p class="explanation">
                        <?= __('Enter the absolute path to the directory where files to be imported will be added.') ?>
                        <?= sprintf(__('The directory can be anywhere on your server. Your root path is: %s'), $_SERVER['DOCUMENT_ROOT']) ?>
                    </p>
                </div>
                <div class="inputs three columns omega">
                    <div id="save" class="panel">
                        <?= $this->formSubmit('add-item-submit', __('Check'), array('class' => 'submit big green button check_button')) ?>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="two columns alpha">
                    <?= $this->formLabel('multiFiles', __('Select folder on computer')) ?>
                </div>
                <div class="inputs five columns omega">
                    <?= $this->formFile('files[]', array('id' => 'multiFiles', 'class' => 'fileinput button', 'multiple' => true, 'webkitdirectory' => true, 'mozdirectory' => true, 'msdirectory' => true, 'odirectory' => true, 'directory' => true)) ?>
                    <p class="explanation">
                    </p>
                </div>
            </div>
            <?php // This is a hidden button for js. ?>
            <div class="field">
                <div class="two columns alpha">
                </div>
                <div class="inputs five columns omega">
                    <?= $this->formButton('upload', __('Upload'), array('id' => 'upload', 'type' => 'submit')) ?>
                </div>
            </div>
        </fieldset>
        <fieldset id="fieldset-make_import_params">
            <legend><?= __('Parameters') ?></legend>
            <div class="field">
                <div class="two columns alpha">
                    <?= $this->formLabel('import_unmapped', __('Import unmapped files')) ?>
                </div>
                <div class="inputs five columns omega">
                    <?= $this->formCheckbox('import_unmapped') ?>
                    <p class="explanation">
                        <?= __('Allow to import all files of a folder. Unmapped files will be imported without metadata, except the file name as title.') ?>
                    </p>
                </div>
            </div>
            <div class="field">
                <div class="two columns alpha">
                    <?= $this->formLabel('delete_file', __('Delete original file from the folder on the server')) ?>
                </div>
                <div class="inputs five columns omega">
                    <?= $this->formCheckbox('delete_file') ?>
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
