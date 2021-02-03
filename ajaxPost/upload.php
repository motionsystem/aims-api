<?php

class aimsUpload {
    // Properties
    private $filename;
    private $uploadDir;
    private $filesizes = ['small'=>200, 'normal'=>800];
    private $overwrite;
    private $success = [];

    private $subfolder = 'home';
    private $watermarkspot = null;

    function __construct($uploadDir, $filesizesMaxWidth = null) {
        $this->uploadDir = $uploadDir;
        $this->filesizes = $filesizesMaxWidth ;

    }

    function setOverwrite($overwrite){
        $this->overwrite = $overwrite;
    }

    function extractFilename($filename, $findLastChar){
        //split filename on the dot.
        $pos = strrpos($filename,$findLastChar);
        return [
            substr($filename,0,$pos),
            substr($filename."#",$pos,-1)
        ];
    }

    // Methods
    function saveBase64Content($image) {

        $this->overwrite = (isset($image['overwrite']) ? $image['overwrite'] : false) ;
        $this->subfolder = (isset($image['folder']) ? $image['folder'] : 'home') ;
        $uploadDir =  $this->uploadDir;
        $img = $image['img'];

        $extratFilename = $this->extractFilename($image['filename'], '.');
        $filename = $extratFilename[0];
        $ex =  $extratFilename[1];

        if($filename === 'aims-watermark'){
            //unset other file sizes for resizing
            $this->subfolder = null;
            $this->filesizes = null;
            $this->overwrite = true;
            $uploadDir = '/upload/';
        }

        if(substr($filename,0,15) === 'aims-photospot-'){
            //unset other file sizes for resizing
            $this->subfolder = null;
            $this->filesizes = null;
            $this->overwrite = true;
            $uploadDir = '/upload/';
        }

        if($this->subfolder){
            $uploadDir = $_SERVER['DOCUMENT_ROOT'].'/' .  $uploadDir . $this->subfolder . '/';
            $this->createFolderIfnotExist($uploadDir );
        }

        $this->filename = $uploadDir . $filename . $ex;
        if(file_exists($this->filename) && !$this->overwrite){
            $datetime = date('ydmhi');
            $this->filename = $uploadDir . $filename . $datetime . $ex;
        }


        $find = 'data:image/';
        $posStart = strpos($img,$find) + strlen($find);
        $posEnd = strpos($img,';',$posStart);
        $ex = substr($img,$posStart,($posEnd - $posStart));
        $img = str_replace('data:image/' . $ex . ';base64,', '', $img);
        $img = str_replace(' ', '+', $img);

        //images file
        $data = base64_decode($img);

        $folder = pathinfo( $this->filename);
        $this->createFolderIfnotExist($folder['dirname']);

        // upload location and create a jepg
        $success = file_put_contents( $this->filename, $data);

        if(!$success) {
            echo "ERORR: Unable to save the file.";
            print_r($success);
            exit;
        }

        list($width, $height) = getimagesize( $this->filename);
        $this->success[] = [$this->filename,$height, $width];

        if($this->filesizes){

            $this->resizeImage($this->filename,$this->filesizes);
        }
    }

    function createFolderIfnotExist($folder){

        $strSub = substr($folder,0,strlen($_SERVER['DOCUMENT_ROOT']));

        if($strSub !== $_SERVER['DOCUMENT_ROOT']) {
            $folder = $_SERVER['DOCUMENT_ROOT'] . '/' . $folder;
        }

        if(substr($folder,-1) === '/'){
            $folder = substr($folder,0,-1);
        }
        if(!is_dir($folder)){
            if (!mkdir( $folder, 0777, true)) {
                die('ERROR Failed to create folders...');
            }
        }
    }




    function resizeImage($filename, $filesizes) {

        // use Imagick for better qaullery
        /// $imagick = new \Imagick(realpath($imagePath));
        /// https://stackoverflow.com/questions/34687115/image-color-ruined-while-resizing-it-using-imagecopyresampled;



        list($width, $height) = getimagesize( $filename);

        //split filename on the dot.
        $extractFile = $this->extractFilename($filename , '/');
        $folder = $extractFile[0] . '/';
        $fileExt = $this->extractFilename(substr($extractFile[1], 1), '.');

        foreach ($filesizes as $key => $maxWidth) {

            $useUploadFolder = $folder . '_'. $key ;
            $this->createFolderIfnotExist($useUploadFolder);

            $thumbFilename = $useUploadFolder . '/' . $fileExt[0] . $fileExt[1];

            if(file_exists($thumbFilename) && !$this->overwrite){
                $thumbFilename = $useUploadFolder . '/' . $fileExt[0] . '-' . date('ymdHis'). $fileExt[1];
            }

            // calculate new sizes
            $percent = ($maxWidth / $width);
            if($percent > 1){
                $percent = 1;
            }
            $newwidth = $width * $percent;
            $newheight = $height * $percent;

            // Load
            $thumb = imagecreatetruecolor($newwidth, $newheight);

            if($fileExt[1] === '.png'){
                $source = imagecreatefrompng( $filename);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagepng($thumb, $thumbFilename  , 9);
            }else if($fileExt[1] === '.gif'){
                $source = imagecreatefromgif( $filename);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagegif($thumb, $thumbFilename );
            }else{
                $source = imagecreatefromjpeg( $filename);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagejpeg($thumb, $thumbFilename  , 100);
            }

            imagedestroy($thumb);
            $this->success[] = [$thumbFilename,$newwidth, $newheight];
        }
    }


    function cropImageFromFilename($image)
    {
        $savedFile = null;
        if ($image['filename'][0] === '/') {
            $image['filename'] = substr($image['filename'] . '#', 1, -1);
        }
        $file = $this->extractFilename($image['filename'], '.');
        if ($file[1] === '.jpg' || $file[1] === '.png') {
            $destination = $this->getDestination($image['filename']);
            $savedFile = $this->cropImage($image, $destination);
        }

        return $savedFile;
    }

    function getDestination($orginFilename, $newFilename = null){

        $arrFile =explode('/',$orginFilename);
        $destination = '';
        foreach ($arrFile as $val) {
            if($val === 'original'){
                continue;
            }
            $destination .= ($destination === '' || !$val ? '' : '/') . $val;
        }

        $arrFile =  $this->extractFilename($destination,'/');
        if($newFilename){
            $ext =  $this->extractFilename($destination,'.');
            $destination = $arrFile[0] . '/' . $newFilename . $ext[1];
        }
        $this->createFolderIfnotExist($arrFile[0]);
        return $_SERVER['DOCUMENT_ROOT'].'/' . $destination;
    }

    function cropImage($image,$destination){

        $file = $this->extractFilename($destination,'.');

        $resizeTo_w =(int) $image['resize_width'];
        $resizeTo_h = (int)$image['resize_height'];
        $resizeTo_w = 600;
        $resizeTo_h = 338;

        $original_w = 1200;
        $original_h = 798;

        $ext = $file[1];
        $dst_x = 0;   // X-coordinate of destination point
        $dst_y = 0;   // Y-coordinate of destination point
        $src_x = (int)$image['x']; // Crop Start X position in original image
        $src_y = (int)$image['y']; // Crop Srart Y position in original image
        $dst_w = (int)$image['width']; // Thumb width
        $dst_h = (int)$image['height']; // Thumb height
        // $src_w = ($image['x'] + $dst_w); // Crop end X position in original image
        //        //$src_h = ($image['y'] + $dst_h); // Crop end Y position in original image
        $src_w = ($dst_w); // Crop end X position in original image
        $src_h = ( $dst_h); // Crop end Y position in original image


        // print_r($dst_w . "-" .  $resizeTo_w . "x" . $resizeTo_h);//exit;

        $destination =  $file[0] . '-' . $resizeTo_w.  'x'. $resizeTo_h . $file[1];


        if(file_exists($destination) && !$this->overwrite){
            $destination = $file[0] . '-' . $resizeTo_w.  'x'. $resizeTo_h . '-' . date('ymdHis'). $file[1];;
        }

        // Creating an image with true colors having thumb dimensions (to merge with the original image)
        $dst_image = imagecreatetruecolor($dst_w, $dst_h);


        if($ext === '.jpg') {
            // Get original image
            $src_image = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'].'/' .$image['filename']);
        }
        if($ext === '.png') {
            // Get original image
            $src_image = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] .'/' .$image['filename']);
        }

        // Cropping
        imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        $dst_image = imagescale($dst_image , $resizeTo_w, $resizeTo_h);

        $wattermark = $_SERVER['DOCUMENT_ROOT'].'/upload/watermark.png';
        ////WATERMARK
        $stamp = imagecreatefrompng($wattermark);
        $image_info  = getimagesize($wattermark);
        $newWidth = ($resizeTo_w / 2);
        $ratio = (($newWidth/$image_info[0] ));
        $newHeight = ($image_info[1] * $ratio);

        $stamp = imagescale($stamp , $newWidth, $newHeight);
        // Set the margins for the stamp and get the height/width of the stamp image
        $dst_x = (($resizeTo_w / 2) - ($newWidth / 2));
        $dst_y = (($resizeTo_h / 2) - ($newHeight / 2));;
        // Copy the stamp image onto our photo using the margin offsets and the photo
        // width to calculate positioning of the stamp.
        imagecopy($dst_image, $stamp, $dst_x,  $dst_y, 0, 0, imagesx($stamp), imagesy($stamp));

        $position = rand(1, 2); //1=left 2=right
        $wattermark = $_SERVER['DOCUMENT_ROOT'].'/upload/watermark2.png';
        ////WATERMARK
        $stamp = imagecreatefrompng($wattermark);

        $image_info  = getimagesize($wattermark);
        $newWidth = ($resizeTo_w / 4);
        $ratio = (($newWidth/$image_info[0] ));
        $newHeight = ($image_info[1] * $ratio);

        if($position === 2) {
            imageflip($stamp, IMG_FLIP_HORIZONTAL);
            $dst_x = $resizeTo_w - $newWidth + ($newWidth / 1.6);
        }
        if($position === 1) {
            $dst_x = -($newWidth / 1.6);
        }


        $stamp = imagescale($stamp , $newWidth, $newHeight);
        // Set the margins for the stamp and get the height/width of the stamp image


        $min = 0 +  10;
        $max = $resizeTo_h - $newHeight ;
        $dst_y = rand($min, $max);

        // Copy the stamp image onto our photo using the margin offsets and the photo
        // width to calculate positioning of the stamp.
        imagecopy($dst_image, $stamp, $dst_x, $dst_y, 0, 0, imagesx($stamp), imagesy($stamp));

        if($ext === '.jpg') {
            // Saving
            imagejpeg($dst_image,  $destination, 100);;
        }
        if($ext === '.png') {
            // Saving
            imagepng($dst_image,    $destination);
        }

        return $destination;
    }


    function getSuccess(){
        return $this->success;
    }
}

$upload = new aimsUpload('upload/original/', ['thumb'=>200]);

$image = $requestData;
unset($image['basketCode']);



if($image['action'] === 'base64') {
    $upload->saveBase64Content($image);
    $done = $upload->getSuccess();
    echo json_encode($done);
}



if($image['action'] === 'crop') {
    $body = [];

    $filename = $upload->cropImageFromFilename($image);

    $strSub = substr($filename,0,strlen($_SERVER['DOCUMENT_ROOT']));
    if($strSub === $_SERVER['DOCUMENT_ROOT']) {
        $filename =  substr($filename."#",strlen($_SERVER['DOCUMENT_ROOT']),-1);
    }

    if(file_exists($_SERVER['DOCUMENT_ROOT'] .$filename )) {
        $body['url'] = $filename;
        $this->setPostBasketCode(false);
        $data = $this->post('process/update/' . $image['id'], $body, null);
        echo json_encode($done);
    }else{
        echo "ERROR er is iets mis gegaan file bestaat niet";
    }

}
