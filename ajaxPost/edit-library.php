<?php

function renameFilename($oldfile, $newFile){
    rename($_SERVER['DOCUMENT_ROOT'] . '/' . $oldfile, $_SERVER['DOCUMENT_ROOT'] . '/' . $newFile);
}


//$requestData['filename'] = '/upload/original/home/_thumb/AimsLogo2020.png';
$arrFile = pathinfo( $requestData['filename']);
$arrDir = explode('/',$arrFile['dirname']);

if(end($arrDir) === '_thumb'){
    $fileThumb = $requestData['filename'];
    $fileOrigin = str_replace('/_thumb/','/',  $requestData['filename']);
}else{
    $fileOrigin = $requestData['filename'];
    $fileThumb = $arrFile['dirname'] . "/_thumb/" . $arrFile['basename'];
}

if($requestData['action'] === 'delete'){
    if(file_exists($_SERVER['DOCUMENT_ROOT'] .'/' . $fileThumb)){
        unlink($_SERVER['DOCUMENT_ROOT'] .'/' . $fileThumb);
    }
    if(file_exists($_SERVER['DOCUMENT_ROOT'] .'/' . $fileOrigin)){
        unlink($_SERVER['DOCUMENT_ROOT'] .'/'  . $fileOrigin);
    }

    if(
        !file_exists($_SERVER['DOCUMENT_ROOT'] .'/' . $fileThumb) &&
        !file_exists($_SERVER['DOCUMENT_ROOT'] .'/' . $fileOrigin)
    ){
        echo json_encode( ['succes'=> true] );
    }
}


if($requestData['action'] === 'rename'){

    $changeFile = $newFile = [];
    $changeFile['thumb'] = $fileThumb;
    $changeFile['origin'] = $fileOrigin;

    //$requestData['newFilename'] = 'OKE';

    foreach ($changeFile as $key => $file) {

        $arrFile = pathinfo($file);
        $newFileName = $arrFile['dirname'] . '/' . $requestData['newFilename'] . '.' . $arrFile['extension'];


        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $newFileName)) {
            $date = date('ymdhis');
            $newFileName = $arrFile['dirname'] . '/' . $updateFile['val'] . $date . '.' . $arrFile['extension'];
        }
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $newFileName)) {
            renameFilename($file, $newFileName);
        }
        $newFile[$newFileName] = $newFileName;
    }

    if(
        file_exists($_SERVER['DOCUMENT_ROOT'] .'/' . $newFile['origin'] ) &&
        file_exists($_SERVER['DOCUMENT_ROOT'] .'/' . $newFile['thumb'] )
    ){
        echo json_encode( ['succes'=> true] );
    }
}
