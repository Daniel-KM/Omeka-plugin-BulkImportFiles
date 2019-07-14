<?php

$head = array(
    'title' => html_escape(__('Bulk Import Files')),
    'bodyclass' => 'bulk-import-files map-edit',
);

queue_css_file('bulk-import-files');
queue_js_file('bulk-import-files');

$script = 'var basePath = "' . PUBLIC_BASE_URL.'"';
$this->headScript()
    ->appendScript($script);

echo head($head);
echo common('bulkimportfiles-nav');

?>
<?php echo flash(); ?>

<?php
/**
 * @var Zend_View $this
 */

// __ = $this->plugin('translate');
// $escapeHtml = $this->plugin('escapeHtml');
// $this->headLink()->appendStylesheet($this->assetUrl('css/bulk-import-files.css', 'BulkImportFiles'));
// $script = 'var basePath = ' . json_encode($this->basePath(), 320);
// $this->headScript()
//     ->appendScript($script)
//     ->appendFile($this->assetUrl('js/bulk-import-files.js', 'BulkImportFiles'));

?>
<h1>
    <span class="subhead"><?php echo __('Create/configure mappings');?></span>
    <span class="title"><?php echo __('Bulk import files'); ?></span>
</h1>

<form method="POST" name="importform" id="importform"><fieldset id="page-actions">
    <?php //echo $this->formCollection($form, false); ?>

    <div class="selected-files">
    </div>

    <div class="selected-files-source">
    </div>
</form>

<div class="files-map-block">
    <div class="property">
        <p>
            <?= sprintf(__('This helper works only with the formats managed by %sGetId3%s.'), '<a href="https://getid3.org" target="_blank">', '</a>') ?>
            <?= __('For xml metadata like xmp, that may be more complete and precise, you need to write the xpaths yourself.') ?>
        </p>
        <p>
            <?= sprintf(__('All fields should have the same format: either the object notation (%siptc.IPTCApplication.Headline%s), either the xpaths (%s/x:xmpmeta/rdf:RDF/rdf:Description/@xmp:Label%s). They should not be mixed.'), '<code>', '</code>', '<code>', '</code>') ?>
        </p>
        <p>
            <?= __('You may need to create mappings first, with the media type as title.') ?>
        </p>
        <p>
            <?= __('You can select folder with you files with any type (images, etc.) and configure it.') ?>
        </p>
        <section class="ten columns alpha omega">
            <fieldset id="fieldset-edit">
                <div class="field">
                    <div class="two columns alpha">
                        <?= $this->formLabel('directory', __('Select folder')) ?>
                    </div>
                    <div class='inputs five columns omega'>
                        <?= $this->formFile('files[]', array('id' => 'multiFiles', 'class' => 'fileinput button', 'multiple' => true, 'webkitdirectory' => true, 'mozdirectory' => true, 'msdirectory' => true, 'odirectory' => true, 'directory' => true)) ?>
                        <p class="explanation">
                        </p>
                    </div>
                </div>
            </fieldset>
            <fieldset id="fieldset-upload">
                <div class="field">
                    <div class="two columns alpha">
                    </div>
                    <div class='inputs five columns omega'>
                        <?php // This is a hidden button for js. ?>
                        <?= $this->formButton('upload', __('Upload'), array('id' => 'upload', 'type' => 'submit')) ?>
                    </div>
                </div>
            </fieldset>
        </section>
    </div>
</div>

<?php echo foot(); ?>
