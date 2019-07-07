<nav id="section-nav" class="navigation vertical">
<?php
    $navArray = array(
        array(
            'label' => __('Make import'),
            'action' => 'index',
            'module' => 'bulk-import-files',
        ),
        array(
            'label' => __('View mappings'),
            'action' => 'map-show',
            'module' => 'bulk-import-files',
        ),
        array(
            'label' => __('Create mapping'),
            'action' => 'map-edit',
            'module' => 'bulk-import-files',
        ),

    );
    echo nav($navArray, 'admin_navigation_settings');
?>
</nav>
