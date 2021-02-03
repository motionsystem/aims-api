<?php


$body = $requestData;
unset($body['action'],$body['field'], $body['content'],$body['id']);

if($requestData['action'] === 'update' && $requestData['field'] && $requestData['content']){
    $field = $requestData['field'];
    $body[$field] =  $requestData['content'];
}

$data = $this->post('process/' .  $requestData['action'] . '/'. $requestData['id'], $body, null);

echo json_encode($data);


