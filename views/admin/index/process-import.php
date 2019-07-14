<?php
/**
 * @var Zend_View $this
 */
$result = array();
$result['status'] = 200;
if (!empty($this->notice)) {
    $result['severity'] = 'notice';
    $result['message'] = $this->notice;
}
if (!empty($this->warning)) {
    $result['severity'] = 'warning';
    $result['message'] = $this->warning;
}
if (!empty($this->error)) {
    $result['severity'] = 'error';
    $result['message'] = $this->error;
}
if (isset($result['severity'])) {
    echo json_encode($result, 320);
}
exit;
