<?php
/**
 * Bulk Import Files Controller
 *
 * This is a backport of the module BulkImportFiles for Omeka S.
 * Controller plugins helpers are currently integrated here.
 * @todo Clean backport of the module for controller plugin helpers.
 */
use mikehaertl\pdftk\Pdf;

// use GetId3\GetId3Core as GetId3;
require_once dirname(dirname(__FILE__)) . '/../../application/libraries/getid3/getid3.php';

class BulkImportFiles_IndexController extends Omeka_Controller_AbstractActionController
{
    /**
     * Mapping by item id.
     *
     * @var array
     */
    protected $filesMaps;

    /**
     * @var string
     */
    protected $resourceTemplateLabel = 'Bulk import files';

    /**
     * Mapping by media type like 'dcterms:created' => ['jpg/exif/IFD0/DateTime'].
     *
     * @var array
     */
    protected $filesMapsArray;

    private $flatArray;

    protected $parsed_data;

    protected $filesData;

    protected $basePath;

    protected $directory;

    protected $ignoredKeys = array(
        'GETID3_VERSION',
        'filesize',
        'filename',
        'filepath',
        'filenamepath',
        'avdataoffset',
        'avdataend',
        'fileformat',
        'encoding',
        'mime_type',
        'md5_data',
    );

    protected $getId3IgnoredKeys = array(
        'GETID3_VERSION',
        'filesize',
        'filename',
        'filepath',
        'filenamepath',
        'avdataoffset',
        'avdataend',
        'fileformat',
        'encoding',
        'mime_type',
        'md5_data',
    );

    protected $bulk;

    public function indexAction()
    {
        $this->forward('make-import');
    }

    public function makeImportAction()
    {
        $this->view->value = "import";
    }

    public function getFilesAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $this->prepareFilesMaps();
        // $request = $this->getRequest();
        $files = $_FILES;
        $files_data_for_view = array();

        foreach ($files['files']['name'] as $key => $file_name) {
            // Skip dot files.
            if (strpos($file_name, '.') === 0) {
                continue;
            }

            $file = array();
            $file['type'] = $files['files']['type'][$key];
            $file['name'] = $files['files']['name'][$key];
            $file['tmp_name'] = $files['files']['tmp_name'][$key];
            $file['error'] = $files['files']['error'][$key];
            $file['size'] = $files['files']['size'][$key];

            $media_type = $file['type'];
            $data = array();
            $this->parsed_data = array();
            $errors = '';

            if (isset($this->filesMapsArray[$media_type])) {
                $filesMapsArray = $this->filesMapsArray[$media_type];
                $file['item_id'] = $filesMapsArray['item_id'];
                unset($filesMapsArray['media_type']);
                unset($filesMapsArray['item_id']);

                switch ($media_type) {
                    case 'application/pdf':
                        $data = $this->extractDataFromPdf($file['tmp_name']);
                        $this->parsed_data = $this->flatArray($data);
                        $data = $this->mapData()->array($data, $filesMapsArray, true);
                        break;

                    default:
                        $getId3 = new GetId3();
                        // TODO Fix GetId3 that uses create_function(), deprecated.
                        $file_source = @$getId3
                        // ->setOptionMD5Data(true)
                        // ->setOptionMD5DataSource(true)
                        // ->setEncoding('UTF-8')
                        ->analyze($file['tmp_name']);
                        $this->parsed_data = $this->flatArray($file_source, $this->ignoredKeys);
                        $data = $this->mapData()->array($file_source, $filesMapsArray, true);
                        break;
                }
            }

            $files_data_for_view[] = array(
                'file' => $file,
                'source_data' => $this->parsed_data,
                'recognized_data' => $data,
                'errors' => $errors,
            );
        }

        $this->view->files_data_for_view = $files_data_for_view;

        $db = get_db();
        $elementTable = $db->getTable('Element');
        $elementSetTable = $db->getTable('ElementSet');

        $select_list = $elementTable->findPairsForSelectForm(array('record_types' => array('All', 'Item')));

        $listTerms = array();
        foreach ($select_list as $elementSetName => $elements) {
            foreach ($elements as $elementId => $elementName) {
                // Keep the untranslated name.
                $element = $elementTable->find($elementId);
                $elementSet = $elementSetTable->find($element->element_set_id);
                $listTerms[$elementSet->name][$elementSet->name . ':' . $element->name] = $elementName;
            }
        }

        $this->view->listTerms = $listTerms;
        $this->view->filesMaps = $this->filesMaps;
    }

    public function checkFilesAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        // $request = $this->getRequest();
        $files = $_FILES;

        $this->prepareFilesMaps();

        $files_data = array();
        $total_files = 0;
        $total_files_can_recognized = 0;
        $error = '';

        // Save the files temporary for the next request.
        $dest = sys_get_temp_dir() . '/bulkimportfiles_upload/';
        if (!file_exists($dest)) {
            mkdir($dest, 0777, true);
        }

        if (!empty($files['files']['name'])) {
            foreach ($files['files']['name'] as $key => $file_name) {
                // Skip dot files.
                if (strpos($file_name, '.') === 0) {
                    continue;
                }

                // Check name for security.
                if (basename($file_name) !== $file_name) {
                    $error = __('All files must have a regular name. Check ended.'); // @translate;
                    break;
                }

                if ($files['files']['error'][$key] === UPLOAD_ERR_OK) {
                    $getId3 = new GetId3();
                    // TODO Fix GetId3 that uses create_function(), deprecated.
                    $file_source = @$getId3
                        // ->setOptionMD5Data(true)
                        // ->setOptionMD5DataSource(true)
                        // ->setEncoding('UTF-8')
                        ->analyze($files['files']['tmp_name'][$key]);

                    ++$total_files;

                    $media_type = 'undefined';
                    $file_isset_maps = 'no';

                    if (isset($file_source['mime_type'])) {
                        $media_type = $file_source['mime_type'];
                        if (isset($this->filesMapsArray[$media_type])) {
                            $file_isset_maps = 'yes';
                            ++$total_files_can_recognized;
                        }
                    }

                    $files_data[] = array(
                        'source' => $file_name,
                        'filename' => basename($files['files']['tmp_name'][$key]),
                        'file_size' => $file_source['filesize'],
                        'file_type' => $media_type,
                        'file_isset_maps' => $file_isset_maps,
                        'has_error' => $files['files']['error'][$key],
                    );

                    $full_file_path = $dest . basename($files['files']['tmp_name'][$key]);
                    move_uploaded_file($files['files']['tmp_name'][$key], $full_file_path);
                } else {
                    if (isset($this->filesMapsArray[$files['files']['type'][$key]])) {
                        $file_isset_maps = 'yes';
                        ++$total_files_can_recognized;
                    } else {
                        $file_isset_maps = 'no';
                    }

                    $files_data[] = array(
                        'source' => $files['files']['name'][$key],
                        'filename' => basename($files['files']['tmp_name'][$key]),
                        'file_size' => $files['files']['size'][$key],
                        'file_type' => $files['files']['type'][$key],
                        'file_isset_maps' => $file_isset_maps,
                        'has_error' => $files['files']['error'][$key] || true,
                    );
                }
            }

            if (!$error && count($files_data) == 0) {
                $error = __('Folder is empty'); // @translate;
            }
        } else {
            $error = __('Can’t check empty folder'); // @translate;
        }

        $this->view->files_data = $files_data;
        $this->view->total_files = $total_files;
        $this->view->total_files_can_recognized = $total_files_can_recognized;
        $this->view->error = $error;
        $this->view->is_server = false;

        $this->render('check-folder');
    }

    public function checkFolderAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $this->prepareFilesMaps();

        $files_data = array();
        $total_files = 0;
        $total_files_can_recognized = 0;
        $error = '';

        $params = array();
        $params['folder'] = $this->getParam('folder');

        if (!empty($params['folder'])) {
            if (file_exists($params['folder']) && is_dir($params['folder'])) {
                $files = $this->listFilesInDir($params['folder']);
                // Skip dot files.
                $files = array_filter($files, function($v) {
                    return strpos($v, '.') !== 0;
                });
                $file_path = $params['folder'] . '/';
                foreach ($files as $file) {
                    $getId3 = new GetId3();
                    // TODO Fix GetId3 that uses create_function(), deprecated.
                    $file_source = @$getId3
                        // ->setOptionMD5Data(true)
                        // ->setOptionMD5DataSource(true)
                        // ->setEncoding('UTF-8')
                        ->analyze($file_path . $file);

                    ++$total_files;

                    $media_type = 'undefined';
                    $file_isset_maps = 'no';

                    if (isset($file_source['mime_type'])) {
                        $media_type = $file_source['mime_type'];
                        if (isset($this->filesMapsArray[$media_type])) {
                            $file_isset_maps = 'yes';
                            ++$total_files_can_recognized;
                        }
                    }

                    $files_data[] = array(
                        'source' => $file,
                        'filename' => $file_source['filename'],
                        'file_size' => $file_source['filesize'],
                        'file_type' => $media_type,
                        'file_isset_maps' => $file_isset_maps,
                    );
                }

                if (count($files_data) == 0) {
                    $error = __('Folder is empty'); // @translate;
                }
            } else {
                $error = __('Folder not exist'); // @translate;
            }
        } else {
            $error = __('Can’t check empty folder'); // @translate;
        }

        $this->view->files_data = $files_data;
        $this->view->total_files = $total_files;
        $this->view->total_files_can_recognized = $total_files_can_recognized;
        $this->view->error = $error;
        $this->view->is_server = true;
    }

    public function processImportAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $this->prepareFilesMaps();

        // $baseUri = FILES_DIR;

        $isServer = $this->getParam('is_server') === 'true';

        $params = array();
        $params['row_id'] = $this->getParam('row_id');
        $params['filename'] = $this->getParam('filename');
        $params['source'] = $this->getParam('source');
        $params['directory'] = $this->getParam('directory');
        $params['import_unmapped'] = $this->getParam('import_unmapped') === 'true';
        $params['delete_file'] = $this->getParam('delete_file') === 'true';

        $row_id = $params['row_id'];
        $notice = null;
        $warning = null;
        $error = null;

        if (isset($params['filename'])) {
            if ($isServer) {
                $full_file_path = $params['directory'] . '/' . $params['filename'];
            } else {
                $full_file_path = sys_get_temp_dir() . '/bulkimportfiles_upload/' . $params['filename'];
            }

            $delete_file_action = $params['delete_file'];

            // TODO Use api standard method, not direct creation.
            // Create new media via temporary factory.

            // $fileinfo = new \SplFileInfo($full_file_path);

            $getId3 = new GetId3();
            // TODO Fix GetId3 that uses create_function(), deprecated.
            $file_source = @$getId3
                // ->setOptionMD5Data(true)
                // ->setOptionMD5DataSource(true)
                // ->setEncoding('UTF-8')
                ->analyze($full_file_path);

            $media_type = isset($file_source['mime_type']) ? $file_source['mime_type'] : 'undefined';

            if ($media_type == 'undefined') {
                $file_extension = pathinfo($full_file_path, PATHINFO_EXTENSION);
                $file_extension = strtolower($file_extension);

                if ($file_extension == 'pdf') {
                    $media_type = 'application/pdf';
                }
            }

            $isMapped = isset($this->filesMapsArray[$media_type]);
            if (!$isMapped) {
                if (!$params['import_unmapped']) {
                    $this->view->row_id = $row_id;
                    $this->view->error = sprintf(__('The media type "%s" is not managed or has no mapping.'), $media_type);
                    return;
                }

                $data = [];
                $notice = __('No mapping for this file.'); // @translate
            } else {
                $filesMapsArray = $this->filesMapsArray[$media_type];

                unset($filesMapsArray['media_type']);
                unset($filesMapsArray['item_id']);

                // Use xml or array according to item mapping.
                $query = reset($filesMapsArray);
                $query = $query ? reset($query) : null;
                $isXpath = $query && strpos($query, '/') !== false;

                if ($isXpath) {
                    $data = $this->mapData()->xml($full_file_path, $filesMapsArray);
                } else {
                    switch ($media_type) {
                        case 'application/pdf':
                            $data = $this->mapData()->pdf($full_file_path, $filesMapsArray);
                            break;
                        default:
                            $data = $this->mapData()->array($file_source, $filesMapsArray);
                            break;
                    }
                }

                if (count($data) <= 0) {
                    if ($query) {
                        $warning = __('No metadata to import. You may see log for more info.'); // @translate
                    } else {
                        $notice = __('No metadata: mapping is empty.'); // @translate
                    }
                }
            }

            if (!isset($data['Dublin Core']['Title'])) {
                $data['Dublin Core']['Title'] = array(
                    array('text' => $isServer ? $params['filename'] : $params['source'], 'html' => false)
                );
            }

            // Save the file if not to be deleted.
            if ($delete_file_action) {
                $tmpPath = $full_file_path;
            } else {
                $tmpPath = tempnam(sys_get_temp_dir(), 'omk_bif_');
                copy($full_file_path, $tmpPath);
            }

            $hasNewItem = @insert_item(
                array(
                    'public' => true,
                ),
                $data,
                // $fileMetadata
                array(
                    Builder_Item::FILE_TRANSFER_TYPE => 'Filesystem',
                    Builder_Item::FILE_INGEST_OPTIONS => array(
                        'ignore_invalid_files' => true,
                        'ignoreNoFile' => true,
                    ),
                    Builder_Item::FILES => array(
                        array(
                            'source' => $tmpPath,
                            'name' => basename($params['source']),
                        ),
                    ),
                )
            );

            if ($hasNewItem && $delete_file_action) {
                @unlink($tmpPath);
            }
        }

        $this->view->row_id = $row_id;
        $this->view->notice = empty($notice) ? null : $notice;
        $this->view->warning = empty($warning) ? null : $warning;
        $this->view->error = empty($error) ? null : $error;
    }

    public function mapShowAction()
    {
        $this->prepareFilesMaps();

        // $view = new ViewModel;
        // $view->setVariable('filesMaps', $this->filesMaps);
        // $view->setVariable('form', $form);
        // require_once BULKIMPORTFILES_PLUGIN_DIR . '/forms/SettingsForm.php';
        // $form = new BulkUsers_Form_Settings;
        // $this->view->form = $form;
        $this->view->filesMaps = $this->filesMaps;
    }

    public function mapEditAction()
    {
        $this->view->value = "update";
    }

    public function addFileTypeAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $request = array();
        $request['state'] = false;
        $request['reloadURL'] = $this->view->url('bulk-import-files/index/map-edit');

        $mediaType = $this->getParam('media_type');
        if (empty($mediaType)) {
            $request['msg'] = __('Request empty.'); // @translate
        } else {
            $filename = 'map_' . explode('/', $mediaType)[0] . '_' . explode('/', $mediaType)[1] . '.ini';
            $filepath = dirname(dirname(__FILE__)) . '/data/mapping/' . $filename;
            if (($handle = fopen($filepath, 'w')) === false) {
                $request['msg'] = __(sprintf('Could not save file "%s" for writing.', $filepath));
            } else {
                $content = "$mediaType = media_type\n";
                fwrite($handle, $content);
                fclose($handle);
                $request['state'] = true;
                $request['msg'] = __('File successfully added!');
            }
        }

        $this->_helper->json($request);
    }

    public function deleteFileTypeAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $request = array();
        $request['state'] = false;
        $request['reloadURL'] = $this->view->url('bulk-import-files/index/map-edit');

        $mediaType = $this->getParam('media_type');
        if (empty($mediaType)) {
            $request['msg'] = __('Request empty.');
        } else {
            $filename = 'map_' . explode('/', $mediaType)[0] . '_' . explode('/', $mediaType)[1] . '.ini';
            $filepath = dirname(dirname(__FILE__)) . '/data/mapping/' . $filename;
            if (!strlen($filepath)) {
                $request['msg'] = __('Filepath string should be longer that zero character.');
            } elseif (!is_writeable($filepath)) {
                $request['msg'] = __(sprintf('File "%s" is not writeable. Check rights.', $filepath));
            } elseif (($handle = fopen($filepath, 'w')) === false) {
                $request['msg'] = __(sprintf('Could not save file "%s" for writing.', $filepath));
            } else {
                fclose($handle);
                $result = unlink($filepath);
                if (!$result) {
                    $request['msg'] = __(sprintf('Could not delete file "%s".', $filepath));
                } else {
                    $request['state'] = true;
                    $request['msg'] = __('File successfully deleted!');
                }
            }
        }

        $this->_helper->json($request);
    }

    public function saveOptionsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $params = [];
        $params['omeka_file_id'] = $this->getParam('omeka_file_id');
        $params['media_type'] = $this->getParam('media_type');
        $params['listterms_select'] = $this->getParam('listterms_select');

        $error = '';
        $request = '';

        if (!empty($params['omeka_file_id'])) {
            $omeka_file_id = $params['omeka_file_id'];
            $media_type = $params['media_type'];
            $listterms_select = $params['listterms_select'];

            /** @var \Omeka\Api\Representation\ItemRepresentation $item */
            $file_content = "$media_type = media_type\n";
            $db = get_db();
            $elementTable = $db->getTable('Element');
            // $elementSetTable = $db->getTable('ElementSet');

            foreach ($listterms_select as $term_item_name) {
                foreach ($term_item_name['property'] as $term) {
                    list($elementSetName, $elementName) = array_map('trim', explode(':', $term));
                    $element = $elementTable->findByElementSetNameAndElementName($elementSetName, $elementName);
                    if (!$element) {
                        continue;
                    }
                    $file_content .= $term_item_name['field'] . ' = ' . $elementSetName . ' : ' . $elementName . "\n";
                }
            }

            $folder_path = dirname(dirname(__FILE__)) . '/data/mapping';
            $response = false;
            if (!empty($folder_path)) {
                if (file_exists($folder_path) && is_dir($folder_path)) {
                    $files = $this->listFilesInDir($folder_path);
                    $file_path = $folder_path . '/';
                    foreach ($files as $file) {
                        if ($file != $omeka_file_id) {
                            continue;
                        }

                        if (!is_writeable($file_path . $file)) {
                            $error = __('Filepath "%s" is not writeable.', $file_path . $file); // @translate
                        }

                        $response = file_put_contents($file_path . $file, $file_content);
                    }
                } else {
                    $error = __('Folder not exist'); // @translate;
                }
            } else {
                $error = __('Can’t check empty folder'); // @translate;
            }

            if ($response) {
                $request = __('Mapping of elements successfully updated.'); // @translate
            } else {
                $request = __('Can’t update mapping.'); // @translate
            }
        } else {
            $request = __('Request empty.'); // @translate
        }

        $result = $error
            ? array('state' => false, 'msg' => $error)
            : array('state' => true, 'msg' => $request);
        $this->_helper->json($result);
    }

    /**
     * Create a flat array from a recursive array.
     *
     * @example
     * ```
     * // The following recursive array:
     * 'video' => [
     *      'dataformat' => 'jpg',
     *      'bits_per_sample' => 24;
     * ]
     * // is converted into:
     * [
     *     'video.dataformat' => 'jpg',
     *     'video.bits_per_sample' => 24,
     * ]
     * ```
     *
     * @param array $data
     * @param array $ignoredKeys
     * @return array
     */
    protected function flatArray(array $data, array $ignoredKeys = array())
    {
        $this->flatArray = array();
        $this->_flatArray($data, $ignoredKeys);
        $result = $this->flatArray;
        $this->flatArray = array();
        return $result;
    }

    /**
     * Recursive helper to flat an array with separator ".".
     *
     * @param array $data
     * @param array $ignoredKeys
     * @param string $keys
     */
    private function _flatArray(array $data, array $ignoredKeys = array(), $keys = null)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->_flatArray($value, $ignoredKeys, $keys . '.' . $key);
            } elseif (!in_array($key, $ignoredKeys)) {
                $this->flatArray[] = array(
                    'key' => trim($keys . '.' . $key, '.'),
                    'value' => $value,
                );
            }
        }
    }

    protected function listFilesInDir($dir)
    {
        if (empty($dir) || !file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return array();
        }
        $result = array_values(array_filter(scandir($dir), function ($file) use ($dir) {
            return is_file($dir . DIRECTORY_SEPARATOR . $file);
        }));
        natcasesort($result);
        return $result;
    }

    protected function extractStringFromFile($filepath, $startString, $endString, $chunkSize = 131072)
    {
        if (!strlen($filepath) || !strlen($startString) || !strlen($endString)) {
            throw new RuntimeException('Filepath, start string and end string should be longer that zero character.');
        }

        $chunkSize = (int) $chunkSize;
        if ($chunkSize <= strlen($startString) || $chunkSize <= strlen($endString)) {
            throw new RuntimeException('Chunk size should be longer than start and end strings.');
        }

        if (($handle = fopen($filepath, 'r')) === false) {
            throw new RuntimeException(sprintf('Could not open file "%s" for reading/'), $filepath);
        }

        $buffer = '';
        $hasString = false;

        while (($chunk = fread($handle, $chunkSize)) !== false) {
            if ($chunk === '') {
                break;
            }

            $buffer .= $chunk;
            $startPosition = strpos($buffer, $startString);
            $endPosition = strpos($buffer, $endString);

            if ($startPosition !== false && $endPosition !== false) {
                $buffer = substr($buffer, $startPosition, $endPosition - $startPosition + 12);
                $hasString = true;
                break;
            } elseif ($startPosition !== false) {
                $buffer = substr($buffer, $startPosition);
                $hasString = true;
            } elseif (strlen($buffer) > (strlen($startString) * 2)) {
                $buffer = substr($buffer, strlen($startString));
            }
        }

        fclose($handle);

        return $hasString ? $buffer : null;
    }

    protected function prepareFilesMaps()
    {
        $this->filesMaps = array();
        $folder_path = dirname(dirname(__FILE__)) . '/data/mapping';
        $db = get_db();

        if (!empty($folder_path)) {
            if (file_exists($folder_path) && is_dir($folder_path)) {
                $files = $this->listFilesInDir($folder_path);
                $file_path = $folder_path . '/';

                $db = get_db();
                $elementTable = $db->getTable('Element');

                foreach ($files as $file) {
                    $data = file_get_contents($file_path . $file);
                    $data = trim($data);
                    if (empty($data)) {
                        continue;
                    }

                    $data_rows = array_filter(array_map('trim', preg_split('/\n|\r\n?/', $data)));

                    $mediaType = null;
                    $current_maps = array();
                    foreach ($data_rows as $value) {
                        $value = array_map('trim', explode('=', $value));
                        if (count($value) !== 2) {
                            continue;
                        }

                        if (in_array('media_type', $value)) {
                            $mediaType = $value[0] === 'media_type' ? $value[1] : $value[0];
                            continue;
                        }

                        // Reorder as mapping = element.
                        if (strpos($value[0], '/') === false
                            && strpos($value[0], '.') === false
                            && strpos($value[0], ':') !== false
                        ) {
                            $elementFullName = $value[0];
                            $map = $value[1];
                        } else {
                            $elementFullName = $value[1];
                            $map = $value[0];
                        }

                        if (strpos($elementFullName, ':') === false || count(explode(':', $elementFullName)) !== 2) {
                            continue;
                        }
                        list($elementSetName, $elementName) = array_map('trim', explode(':', $elementFullName));
                        $element = $elementTable->findByElementSetNameAndElementName($elementSetName, $elementName);
                        if (!$element) {
                            continue;
                        }

                        $current_maps[$elementSetName . ':' . $elementName][] = $map;
                    }

                    if ($mediaType) {
                        $current_maps['item_id'] = $file;
                        $this->filesMapsArray[$mediaType] = $current_maps;
                    }

                    $current_maps['media_type'] = $mediaType;
                    $this->filesMaps[$file] = $current_maps;
                }
            } else {
                $error = 'Folder not exist'; // @translate;
            }
        } else {
            $error = 'Can’t check empty folder'; // @translate;
        }
    }

    /**
     * @return BulkImportFiles_IndexController
     */
    protected function mapData()
    {
        //$this->bulk = get_db()->getTable('Element')->findPairsForSelectForm();
        return $this;
    }

    /**
     * Extract data from an array with a mapping.
     *
     * @param array $input Array of metadata..
     * @param array $mapping The mapping adapted to the input.
     * @param bool $simpleExtract Only extract metadata, don't map them.
     * @return array A resource array by property, suitable for api creation
     * or update.
     */
    protected function array(array $input, array $mapping, $simpleExtract = false)
    {
        $mapping = $this->normalizeMapping($mapping);
        if (empty($input) || empty($mapping)) {
            return array();
        }

        $result = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);

        foreach ($mapping as $map) {
            $target = reset($map);
            $query = key($map);

            $queryMapping = explode('.', $query);
            $input_fields = $input;

            foreach ($queryMapping as $qm) {
                if (isset($input_fields[$qm])) {
                    $input_fields = $input_fields[$qm];
                }
            }

            if (!is_array($input_fields)) {
                $simpleExtract
                    ? $this->simpleExtract($result, $input_fields, $target, $query)
                    : $this->appendValueToTarget($result, $input_fields, $target, $query);
            }
        }
        return $result->exchangeArray(array());
    }

    /**
     * Extract data from a xml file with a mapping.
     *
     * @param string $filepath
     * @param array $mapping The mapping adapted to the input.
     * @param bool $simpleExtract Only extract metadata, don't map them.
     * @return array A resource array by property, suitable for api creation
     * or update.
     */
    protected function xml($filepath, array $mapping, $simpleExtract = false)
    {
        $mapping = $this->normalizeMapping($mapping);
        if (empty($mapping)) {
            return array();
        }

        $xml = $this->extractStringFromFile($filepath, '<x:xmpmeta', '</x:xmpmeta>');
        if (empty($xml)) {
            return array();
        }

        // Check if the xml is fully formed.
        $xml = trim($xml);
        if (strpos($xml, '<?xml ') !== 0) {
            $xml = '<?xml version="1.1" encoding="utf-8"?>' . $xml;
        }

        $result = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);

        // Register all namespaces to allow prefixes.
        $xpathN = new DOMXPath($doc);
        foreach ($xpathN->query('//namespace::*') as $node) {
            $xpath->registerNamespace($node->prefix, $node->nodeValue);
        }

        foreach ($mapping as $map) {
            $target = reset($map);
            $query = key($map);
            $nodeList = $xpath->query($query);
            if (!$nodeList || !$nodeList->length) {
                continue;
            }

            // The answer has many nodes.
            foreach ($nodeList as $node) {
                $simpleExtract
                    ? $this->simpleExtract($result, $node->nodeValue, $target, $query)
                    : $this->appendValueToTarget($result, $node->nodeValue, $target);
            }
        }

        return $result->exchangeArray(array());
    }

    protected function pdf($filepath, array $mapping, $simpleExtract = false)
    {
        $mapping = $this->normalizeMapping($mapping);
        if (empty($mapping)) {
            return array();
        }

        $input = $this->extractDataFromPdf($filepath);
        return $this->array($input, $mapping, $simpleExtract);
    }

    protected function simpleExtract(ArrayObject $result, $value, $target, $source)
    {
        $result[] = array(
            'field' => $source,
            'target' => $target,
            'value' => $value,
        );
    }

    protected function appendValueToTarget(ArrayObject $result, $value, $target, $source)
    {
        static $targets = array();

        if (isset($targets[$target])) {
            if (empty($targets[$target])) {
                return;
            }
        } else {
            $targets[$target] = array();

            $targets[$target]['is'] = 'resource';

            $target_list = explode(":", $target);

            $targets[$target]['field'] = $target_list[1];

            $element = get_db()->getTable('Element')->findByElementSetNameAndElementName($target_list[0], $target_list[1]);
            $targets[$target]['element_set_id'] = $element->element_set_id;
            $targets[$target]['element_set_name'] = get_db()->getTable('ElementSet')->find($element->element_set_id)->name;
        }

        if ($targets[$target]['is']) {
            $result[$targets[$target]['element_set_name']][$targets[$target]['field']][] = array('text' => $value, 'html' => false);
        }
    }

    protected function appendValueToTarget1(ArrayObject $result, $value, $target)
    {
        static $targets = array();

        // First prepare the target keys.
        // TODO This normalization of the mapping can be done one time outside.

        // @see BulkImport\View\Helper\AutomapFields
        // The pattern checks a term or keyword, then an optional @language, then
        // an optional ^^ data type.
        $pattern = '~'
            // Check a term/keyword.
            . '^([a-zA-Z][^@^]*)'
            // Check a language + country.
            . '\s*(?:@\s*([a-zA-Z]+-[a-zA-Z]+|[a-zA-Z]+|))?'
            // Check a data type.
            . '\s*(?:\^\^\s*([a-zA-Z][a-zA-Z0-9]*:[a-zA-Z][\w-]*|[a-zA-Z][\w-]*|))?$'
            . '~';
            $matches = array();

            if (isset($targets[$target])) {
                if (empty($targets[$target])) {
                    return;
                }
            } else {
                $meta = preg_match($pattern, $target, $matches);
                if (!$meta) {
                    $targets[$target] = false;
                    return;
                }
                $targets[$target] = array();
                $targets[$target]['field'] = trim($matches[1]);
                $targets[$target]['@language'] = empty($matches[2]) ? null : trim($matches[2]);
                $targets[$target]['type'] = empty($matches[3]) ? null : trim($matches[3]);

                $targets[$target]['is'] = $this->isField($targets[$target]['field']);

                if ($targets[$target]['is'] === 'property') {
                    $targets[$target]['property_id'] = get_db()->getTable('Element')->findBy(array('name' => $targets[$target]['field']), 1)->id;
                }
            }

            // Second, fill the result with the value.
            switch ($targets[$target]['is']) {
                case 'property':
                    $v = array();
                    $v['property_id'] = $targets[$target]['property_id'];
                    $v['type'] = $targets[$target]['type'] ?: 'literal';
                    switch ($v['type']) {
                        case 'literal':
                            // case strpos($resourceValue['type'], 'customvocab:') === 0:
                        default:
                            $v['@value'] = $value;
                            $v['@language'] = $targets[$target]['@language'];
                            break;
                        case 'uri':
                        case strpos($targets[$target]['type'], 'valuesuggest:') === 0:
                            $v['o:label'] = null;
                            $v['@language'] = $targets[$target]['@language'];
                            $v['@id'] = $value;
                            break;
                        case 'resource':
                        case 'resource:item':
                        case 'resource:media':
                        case 'resource:itemset':
                            $id = $this->findResourceFromIdentifier($value, null, $targets[$target]['type']);
                            if ($id) {
                                $v['value_resource_id'] = $id;
                                $v['@language'] = null;
                            } else {
                                $v['has_error'] = true;

                                // $this->logger->err(
                                //     'Index #{index}: Resource id for value "{value}" cannot be found: the entry is skipped.', // @translate
                                //     ['index' => $this->indexResource, 'value' => $value]
                                // );
                            }
                            break;
                    }
                    if (empty($v['has_error'])) {
                        $result[$targets[$target]['field']][] = $v;
                    }
                    break;
                    // Item is used only for media, that has only one item.
                case $targets[$target]['field'] === 'o:item':
                case 'id':
                    $result[$targets[$target]['field']] = array('o:id' => $value);
                    break;
                case 'resource':
                    $result[$targets[$target]['field']][] = array('o:id' => $value);
                    break;
                case 'boolean':
                    $result[$targets[$target]['field']] = in_array($value, array('false', false, 0, '0', 'off', 'close'), true)
                        ? false
                        : (bool) $value;
                    break;
                case 'single':
                    // TODO Check email and owner.
                    $v = array();
                    $v['value'] = $value;
                    $result[$targets[$target]['field']] = $v;
                    break;
                case 'custom':
                default:
                    $v = array();
                    $v['value'] = $value;
                    if (isset($targets[$target]['@language'])) {
                        $v['@language'] = $targets[$target]['@language'];
                    }
                    $v['type'] = empty($targets[$target]['type'])
                        ? 'literal'
                        : $targets[$target]['type'];
                    $result[$targets[$target]['field']][] = $v;
                    break;
            }
    }

    /**
     * Determine the type of field.
     *
     * @param string $field
     * @return string
     */
    protected function isField($field)
    {
        return "resource";

        $resources = array(
            'o:item',
            'o:item_set',
            'o:media',
        );
        if (in_array($field, $resources)) {
            return 'resource';
        }
        $ids = array(
            'o:resource_template',
            'o:resource_class',
            'o:owner',
        );
        if (in_array($field, $ids)) {
            return 'id';
        }
        $booleans = array(
            'o:is_open',
            'o:is_public',
        );
        if (in_array($field, $booleans)) {
            return 'boolean';
        }
        $singleData = array(
            'o:email',
        );
        if (in_array($field, $singleData)) {
            return 'single';
        }
        return $this->bulk->isPropertyTerm($field)
            ? 'property'
            : 'custom';
    }

    /**
     * Normalize a mapping.
     *
     * Mapping is either a single or a multiple list, either a target
     * key or value, and either a xpath or a array:
     * [Title => /xpath/to/data]
     * [Title => object.to.data]
     * [/xpath/to/data => Title]
     * [object.to.data => Title]
     * [[Title => /xpath/to/data]]
     * [[Title => object.to.data]]
     * [[/xpath/to/data => Title]]
     * [[object.to.data => Title]]
     *
     * And the same mappings with a value as an array, for example:.
     * [[object.to.data => [Title]]]
     * The format is normalized into [[path/object => Title]].
     *
     * @param array $mapping
     * @return array
     */
    protected function normalizeMapping(array $mapping)
    {
        if (empty($mapping)) {
            return $mapping;
        }

        // Normalize the mapping to multiple data with source to target.
        $keyValue = reset($mapping);
        $isMultipleMapping = is_numeric(key($mapping));
        if (!$isMultipleMapping) {
            $mapping = $this->multipleFromSingle($mapping);
            $keyValue = reset($mapping);
        }

        $value = reset($keyValue);
        if (is_array($value)) {
            $mapping = $this->multipleFromMultiple($mapping);
            $keyValue = reset($mapping);
        }

        $key = key($keyValue);
        $isTargetKey = strpos($key, ':') && strpos($key, '::') === false;
        if ($isTargetKey) {
            $mapping = $this->flipTargetToValues($mapping);
        }

        return $mapping;
    }

    /**
     * Convert a single mapping to a multiple mapping.
     *
     * @param array $mapping
     * @return array
     */
    protected function multipleFromSingle(array $mapping)
    {
        $result = array();
        foreach ($mapping as $key => $value) {
            $result[] = array($key => $value);
        }
        return $result;
    }

    /**
     * Convert a multiple level mapping to a multiple mapping.
     *
     * @param array $mapping
     * @return array
     */
    protected function multipleFromMultiple(array $mapping)
    {
        $result = array();
        foreach ($mapping as $value) {
            foreach ($value as $key => $val) {
                foreach ($val as $v) {
                    $result[] = array($key => $v);
                }
            }
        }
        return $result;
    }

    /**
     * Flip keys and values of a full mapping.
     *
     * @param array $mapping
     * @return array
     */
    protected function flipTargetToValues(array $mapping)
    {
        $result = array();
        foreach ($mapping as $value) {
            $result[] = array(reset($value) => key($value));
        }
        return $result;
    }

    protected function extractStringToFile($filepath, $content)
    {
        if (!strlen($filepath)) {
            throw new RuntimeException('Filepath string should be longer that zero character.');
        }

        if (!is_writeable($filepath)) {
            throw new RuntimeException(sprintf('Filepath "%s" is not writeable. Check rights.', $filepath));
        }

        if (($handle = fopen($filepath, 'w')) === false) {
            throw new RuntimeException(sprintf('Could not save file "%s".', $filepath));
        }

        fwrite($handle, $content);
        fclose($handle);

        // return $hasString ? $buffer : null;
        return true;
    }

    // ----------------------------  pdf plugin  --------------------------------- //
    protected $pdftkPath;

    /**
     * @var string
     */
    protected $executeStrategy;

    /**
     * @param string $pdftkPath
     * @param string $executeStrategy
     */
    // public function __construct($pdftkPath, $executeStrategy)
    // {
    //     $this->pdftkPath = $pdftkPath;
    //     $this->executeStrategy = $executeStrategy;
    // }

    /**
     * Extract medadata from a pdf.
     *
     * @param string $filepath
     * @return array
     */
    protected function extractDataFromPdf($filepath)
    {
        $this->pdftkPath = "";
        $this->executeStrategy = "exec";

        $options = array();
        if ($this->pdftkPath) {
            $options['command'] = $this->pdftkPath;
        }
        if ($this->executeStrategy === 'exec') {
            $options['useExec'] = true;
        }

        $pdf = new Pdf($filepath, $options);
        $data = (string) $pdf->getData();
        if (empty($data)) {
            $error = $pdf->getError() ?: sprintf('Command pdftk unavailable or failed: %s', $pdf->getCommand()); // @translate
            _log(sprintf('Unable to process pdf: %s', $error));

            return array();
        }

        $result = array();

        $regex = '~^InfoBegin\nInfoKey: (.+)\nInfoValue: (.+)$~m';
        $matches = array();
        preg_match_all($regex, $data, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            $result[$match[1]] = $match[2];
        }

        $regex = '~^NumberOfPages: (\d+)$~m';
        preg_match($regex, $data, $matches);
        if ($matches[1]) {
            $result['NumberOfPages'] = $matches[1];
        }
        return $result;
    }
}
