<?php

$head = array(
    'title' => html_escape(__('Bulk Import Files')),
    'bodyclass' => 'bulk-import-files edit',
);

echo head($head);
echo common('bulkimportfiles-nav');

?>
<?php echo flash(); ?>

<?php echo $this->value; ?>

<?php echo foot(); ?>
