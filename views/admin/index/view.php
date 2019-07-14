<?php

$head = array(
    'title' => html_escape(__('Bulk Import Files')),
    'bodyclass' => 'bulk-import-files view',
);

echo head($head);
echo common('bulkimportfiles-nav');

?>
<?php echo flash(); ?>

<?php echo $this->value; ?>

<?php echo foot(); ?>
