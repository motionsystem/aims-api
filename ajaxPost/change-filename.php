<?php


function renameFilename($oldfile, $newFile){
    rename($_SERVER['DOCUMENT_ROOT'] . '/' . $oldfile, $_SERVER['DOCUMENT_ROOT'] . '/' . $newFile);
}


$requestData = json_decode(file_get_contents('php://input'), true);
$returnJson =[];
$updateBody = [];

$updateFields = [];
$updateFile = [];
$changeFilename = false;
foreach ($requestData['data'] as $arr) {

    if($arr['id'] === 'input-filename'){
        $arr['file'] = pathinfo($arr['original']);
        if($arr['val'] !==  $arr['file']['filename']){
            $updateFile = $arr;
            $changeFilename = true;
        }
    }

    if($arr['id'] !== 'input-filename' && $arr['val'] !== $arr['original']){
        $updateFields[] = $arr;
    }
}

if(!$updateFields && !$updateFile){
    echo json_encode(['ACTION'=>'none']);
    exit;
}


foreach ($updateFields as $key => $arr) {
    $explodeId = explode('-',$arr['id']);
    unset($explodeId[0]);
    $id = implode('-',$explodeId);
    $updateBody[$id] = $arr['val'];
}

$success = false;
if($updateFile){
    $newFileName = $updateFile['file']['dirname'] . '/' . $updateFile['val']  .'.' . $updateFile['file']['extension'];
    $oldFileName = $updateFile['original'];

    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/' . $oldFileName)) {
        renameFilename($oldFileName,$newFileName) ;
        $returnJson['FILE-ACTION'] = 'succes';
        $returnJson['info'] = 'succes changed';
        $success = true;
    }
    if(!$success && file_exists($_SERVER['DOCUMENT_ROOT'].'/' . $newFileName)) {
        $returnJson['FILE-ACTION'] = 'succes';
        $returnJson['info'] = 'succes exists';
        $success = true;
    }
    if(!$success){
        $returnJson['FILE-ACTION'] = 'error';
        $returnJson['info'] = 'error none file exists';
    }

    if($success){
        $updateBody['url'] = $newFileName;
        $updateBody['*skipupdate*'] = ['oldfile'=>$oldFileName];
    }

}



if($updateBody){
    $updateBody['*addData*'] = 'renameAllContentImages';

    $url = 'process/update/'. $requestData['id'];
    $data = $this->post($url, $updateBody, null);

    print_r($data);exit;
    $action = $data['APPDATA']['updated']['msy_content'][0]['ACTION'];
    $returnJson['data'] = $data['APPDATA']['updated']['msy_content'][0];
    if($action !== 'UPDATE' && $action !== 'NONE'){
        renameFilename($newFileName, $oldFileName) ;
    }
}

echo json_encode($returnJson);


