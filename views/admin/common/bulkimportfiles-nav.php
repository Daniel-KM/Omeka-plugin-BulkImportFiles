<nav id="section-nav" class="navigation vertical aaa">
<?php
    $navArray = array(
        array(
            'label' => 'View mappings',
            'action' => 'index',
            'module' => 'bulk-import-files',
        ),
        array(
            'label' => 'Create mapping',
            'action' => 'update',
            'module' => 'bulk-import-files',
        ),
        array(
            'label' => 'Make import',
            'action' => 'import',
            'module' => 'bulk-import-files',
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
?>
</nav>
