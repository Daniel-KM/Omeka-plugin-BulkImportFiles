<?php
/**
 * @var Zend_View $this
 * @var int $row_id
 * @var string $notice
 * @var string $warning
 * @var string $error
 */

$result = array();
$result['status'] = 200;
if (!empty($notice)) {
    $result['severity'] = 'notice';
    $result['message'] = $notice;
}
if (!empty($warning)) {
    $result['severity'] = 'warning';
    $result['message'] = $warning;
}
if (!empty($error)) {
    $result['severity'] = 'error';
    $result['message'] = $error;
}
if (isset($result['severity'])) {
    echo json_encode($result, 320);
}
