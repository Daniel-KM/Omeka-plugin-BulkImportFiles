<nav id="section-nav" class="navigation vertical">
<?php
    $navArray = array(
        array(
            'label' => __('Make import'),
            'module' => 'bulk-import-files',
            'resource' => 'BulkImportFiles_Index',
            'action' => 'index',
            'privilege' => 'make-import',
        ),
        array(
            'label' => __('View mappings'),
            'module' => 'bulk-import-files',
            'resource' => 'BulkImportFiles_Index',
            'action' => 'map-show',
            'privilege' => 'map-show',
        ),
        array(
            'label' => __('Create mappings'),
            'module' => 'bulk-import-files',
            'resource' => 'BulkImportFiles_Index',
            'action' => 'map-edit',
            'privilege' => 'map-edit',
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
?>
</nav>
