<?php

if (!defined('BULKIMPORTFILES_PLUGIN_DIR')) {
    define('BULKIMPORTFILES_PLUGIN_DIR', dirname(__FILE__));
}

require_once __DIR__. '/vendor/autoload.php';

class BulkImportFilesPlugin extends Omeka_Plugin_AbstractPlugin
{
    // Hooks and Filters
    protected $_hooks = array(
        'define_acl'
    );

    protected $_filters = array(
        'admin_navigation_main'
    );

    /**
     *  define_acl hook
     */
    public function hookDefineAcl($args)
    {
        /** @var Zend_Acl $acl */
        $acl = $args['acl'];

        $acl->addResource('BulkImportFiles_Index');

        // Only super admin can edit mapping, other people can only import.
        // The rules include some ajax actions.
        $roles = array('admin', 'researcher', 'contributor');
        $acl
            ->deny(
                $roles,
                'BulkImportFiles_Index'
            )
            ->allow(
                $roles,
                'BulkImportFiles_Index',
                array(
                    'index',
                    // 'map-show',
                    // 'map-edit',
                    'make-import',
                    'get-files',
                    // 'save-options',
                    'add-files',
                    'delete-file',
                    'check-folder',
                    'check-files',
                    'process-import',
                )
            );
    }

    /**
     * Adds a button to the admin's main navigation.
     *
     * @param array $nav
     * @return array
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Bulk Import Files'),
            'uri' => url('bulk-import-files'),
            'resource' => 'BulkImportFiles_Index',
            'privilege' => 'index',
        );

        return $nav;
    }

    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getModuleName() == 'bulk-import-files') {
            queue_css_file('bulk-import-files');
            queue_js_file('bulk-import-files');
        }
    }
}
