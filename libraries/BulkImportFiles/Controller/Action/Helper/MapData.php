<?php
/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';
/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';
/**
 * Extract data from a string with a mapping.
 */
class BulkImportFiles_Controller_Action_Helper_MapData extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var PluginManager
     */
    protected $plugins;

    /**
     * @var \BulkImport\Mvc\Controller\Plugin\Bulk
     */
    protected $bulk;

    /**
     * Temporary flat array.
     *
     * @var array
     */
    private $flatArray;

    /**
     * Keys to ignore for getid3.
     *
     * @var array
     */
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

    /**
     * @param PluginManager $plugins
     */
    public function __construct(PluginManager $plugins)
    {
        $this->plugins = $plugins;
        $this->bulk = $plugins->get('bulk');
    }

    /**
     * @return \BulkImportFiles\Mvc\Controller\Plugin\MapData
     */
    public function mapData()
    {
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
    public function array(array $input, array $mapping, $simpleExtract = false)
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
                    : $this->appendValueToTarget($result, $input_fields, $target);
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
    public function xml($filepath, array $mapping, $simpleExtract = false)
    {
        $mapping = $this->normalizeMapping($mapping);
        if (empty($mapping)) {
            return array();
        }

        $extractStringFromFile = $this->plugins()->get('extractStringFromFile');
        $xml = $extractStringFromFile($filepath, '<x:xmpmeta', '</x:xmpmeta>');
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

    public function pdf($filepath, array $mapping, $simpleExtract = false)
    {
        $mapping = $this->normalizeMapping($mapping);
        if (empty($mapping)) {
            return array();
        }

        $extractDataFromPdf = $this->plugins()->get('extractDataFromPdf');
        $input = $extractDataFromPdf($filepath);
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

    protected function appendValueToTarget(ArrayObject $result, $value, $target)
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
                $targets[$target]['property_id'] = $this->bulk->getPropertyId($targets[$target]['field']);
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
                            $this->logger->err(
                                'Index #{index}: Resource id for value "{value}" cannot be found: the entry is skipped.', // @translate
                                array('index' => $this->indexResource, 'value' => $value)
                            );
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
     * [dcterms:title => /xpath/to/data]
     * [dcterms:title => object.to.data]
     * [/xpath/to/data => dcterms:title]
     * [object.to.data => dcterms:title]
     * [[dcterms:title => /xpath/to/data]]
     * [[dcterms:title => object.to.data]]
     * [[/xpath/to/data => dcterms:title]]
     * [[object.to.data => dcterms:title]]
     *
     * And the same mappings with a value as an array, for example:.
     * [[object.to.data => [dcterms:title]]]
     * The format is normalized into [[path/object => dcterms:title]].
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

    protected function plugins()
    {
        return $this->plugins;
    }
}
