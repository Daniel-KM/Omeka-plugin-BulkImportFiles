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

<?php
/**
 * @var \Zend\View\Renderer\PhpRenderer $this
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
        <h4><?= __('Select folder') ?></h4>
        <div><?= __('You can select folder with you files with any type (images, etc.) and configure it.') ?></div>
        <input type="file" id="multiFiles" name="files[]" multiple="multiple" webkitdirectory mozdirectory msdirectory odirectory directory />
        <button id="upload"><?= __('Upload') ?></button>
    </div>
</div>

<?php echo foot(); ?>
