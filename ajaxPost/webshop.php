<?php


if($requestData['action'] === 'addProduct' || $requestData['action'] === 'removeProduct' || $requestData['action'] === 'clearProduct') {

    $body = [
        'action' =>$requestData['action'],
        'product' => $requestData['product'],
        'basket' => (isset($_SESSION['basketCode']) ? $_SESSION['basketCode'] : ''),
    ];

    $data = $this->post('/shop/basket/add', $body, ['APPDATA|basket']);

    if (isset($data['basket'][0]['shpBasketCode'])) {
        $_SESSION['basketCode'] = $data['basket'][0]['shpBasketCode'];
    }
    $arr = $data['basket'];
    echo json_encode($arr);
}


if($requestData['action'] === 'createOrder' && $_SESSION['basketCode']) {
    unset($requestData['action'] );
    $data = $this->post('/shop/order/add' .  $_SESSION['basketCode'], $requestData, ['APPDATA|order']);
    $data["succesUrl"] = '/webshop/checkorder';
    $data["basket"] = $_SESSION['basketCode'];
    echo json_encode($data);
}


/*
if($requestData['action'] === 'addPayment' && $_SESSION['basketCode']) {
    $data = $aimsApi->post('shop/order/pay'.$_SESSION['basketCode'], null,null);

    if(!empty($data['APPDATA']['checkout'])) {
        header("Location: " . $data['APPDATA']['checkout'], \true, 303);
        exit;
    }else{
        $error = (isset($data['APPINFO']['fatal'][0]['message']) ? $data['APPINFO']['fatal'][0]['message'] : 'er is iets mis gegaan.');
        echo json_encode(['error' => $error]);
    }
}
*/
