<?php

if(!isset($_GET['action']) === 'payment' || empty($_SESSION['basketCode'])){

    exit;
}

$config = parse_ini_file("aims-api.ini");
require_once '../vendor/autoload.php';

$aimsApi = new Aims\api($config);
$data = $this->post('shop/order/pay' . $_SESSION['basketCode'], null,null);

if(!empty($data['APPDATA']['checkout'])) {
    header("Location: " . $data['APPDATA']['checkout'], \true, 303);
    exit;
}else{
    $error = (isset($data['APPINFO']['fatal'][0]['message']) ? $data['APPINFO']['fatal'][0]['message'] : 'er is iets mis gegaan.');
   // header("Location: https://cichlidekopen.nl/error/" );
   /// header('Location: https://cichlidekopen.nl/payment/'.$error);
    print_r($data);exit;
    exit;
}
