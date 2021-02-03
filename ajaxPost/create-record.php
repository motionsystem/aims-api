<?php

$body = null;

if(!$requestData['block']){
    echo "block is empty check this: <br>";
    echo  'use "aims-block" attribute in the tag before the twig loop like: <i>aims-block="webshopOverview.text"</i>';
    exit;
}


$arrBlock = explode('.',$requestData['block']);

$body['page'] =  $requestData['page'];
$body['block'] =  $arrBlock[0];
$body['spot'] =  $arrBlock[1];
$body['group'] = ($requestData['group'] ? $requestData['group'] : 0);

$data = $this->post('process/create/'. $requestData['action'], $body, null);

echo json_encode($data);

