<?php
class aimsLibrary
{

    private $dir;
    private $getSubFolder;
    private $structure;

    function __construct($dir, $getSubFolder = '_thumb') {
        $this->dir = $dir;
        $this->getSubFolder = $getSubFolder ."/";
        $data = $this->readFolder($dir );
        $this->structure = $this->reorderHomeAsTop($data);
    }

    function readFolder($dir)
    {
        $data = [];
        $folder = scandir($_SERVER['DOCUMENT_ROOT'] . $dir);

        foreach ($folder as $name) {
            $file = $dir . $name;
            if ($name === '.' || $name === '..') {
                continue;
            }
            if (is_dir($_SERVER['DOCUMENT_ROOT'] .$file)) {
                if ($name[0] !== "_") {
                    $data[$name] = $this->readFolder($file . '/' . $this->getSubFolder. '/');
                }
            } else {
                $data[] = [
                    'url' => $file,
                    'name' => $name,
                ];

            }
        }
        return $data;
    }

    function getStructure(){

        return $this->structure;
    }

    function reorderHomeAsTop($data){
        $structure[] = ['folder'=>'home','data'=>$data['home']];
        foreach ($data as $key => $arr) {
            if($key !== 'home'){
                $structure[] =  ['folder'=>$key,'data'=>$arr];
            }
        }
        return $structure;
    }

    function getHtmlFolders(){
        $html = '';
        foreach ($this->structure  as $arr) {
            if (!$arr['data'] &&  $arr['folder'] !== 'home') {
                $dir = $_SERVER['DOCUMENT_ROOT'] .$this->dir . $arr['folder'];
                $thumb = $_SERVER['DOCUMENT_ROOT'] .$this->dir . $arr['folder'] . '/_thumb';
                if (is_dir($thumb)) {
                    rmdir($thumb);
                }
                if (is_dir($dir)) {
                    rmdir($dir);
                }
                continue;
            }

            $selected = (!isset($selected) ? 'selected' : '');
            $id = (strtolower($arr['folder']) === 'home' ? 'id="homeDir" ' : '');
            $html .= '<div ' . $id . ' class="aimsImgLibraryFolder item ' . $selected . '">' . $arr['folder'] . '</div>';

        }
        return $html;
    }

    function getHtmlContent(){

        $html = '<div id="libraryResult">';
        foreach ($this->structure  as $arr) {
            $selected = (!isset($selected) ? 'selected' : null);
            $html .= '<div class="w100 libraryResultFolder ' . $selected . '" id="content-folder-'.$arr['folder'].'">';

            foreach ($arr['data']  as $file) {
                $html .= '
                <div class="item  aimsImgLibraryImage">
                    <div class="title">'.$file['name'].'</div>
                    <img src="'.$file['url'].'">
                </div>';
            }

            $html .= '</div>';

        }
        $html .= '</div>';
        return $html;
    }


}

$blockUrl = explode('.',$requestData['block']);


$updateFields = $this->post('process-contentblock/'.$blockUrl[1] .'/' .$requestData['id'] , [], ['APPDATA|fieldLabel']);

$library = new aimsLibrary('/upload/original/','_thumb');
$data = $library->getStructure();


$inputFileBlockHtml = '';
foreach ($updateFields['fieldLabel'] as $key => $arr) {

    if($arr['field'] === 'url') {
        $inputFileBlockHtml .= '<label>Filename</label>';
        $arrFile = pathinfo($arr['value']);
        $inputFileBlockHtml .= '<input class="saveFileInfo" id="input-filename" type="text" name="alt" value="' . $arrFile['filename'] . '" original="' . $arr['value'] . '"/>';
    }else{
        $inputFileBlockHtml .= '<label>'.$arr['label'].'</label>';
        $inputFileBlockHtml .= '<input class="saveFileInfo" id="input-' . $arr['label'] . '"type="text" name="alt" value="' . $arr['value'] . '" original="' . $arr['value'] . '"/>';
    }
}
$inputFileBlockHtml .='<button id="saveInputs" class="w100 center">opslaan</button><br><br>';




?>
<div id="imgLibrary">
    <div class="header" >
        Aims image bibiotheek
        <span style="position: absolute;right:30px" id="aimsImgLibraryRefresh">R</span>
        <span style="position: absolute;right:10px" id="aimsImgLibraryClose">X</span>
    </div>
<div class="container">

    <div id="originalImgContainer" class="center">
        <button id="selectNewImage" class="w100 center">Selecteer een nieuwe foto</button><br><br>
        <div class="w50">
            <img id="originalSrc" src="" class="center" />
        </div>
        <div class="w50">
            <div id="changeFileNameBlock">
                <?php echo $inputFileBlockHtml ?>
            </div>
            <button id="removeImageBlock" class="w100 center">verwijderen foto spot</button>
        </div>


    </div>

    <div id="cropImage" class="w100">
        <div class="center">
            <img id="cropOriginalImg" src="" />
        </div>
        <div id="formCrop" style="display: none">
            w:<input id="input-croppr-width" type="hidden"><br>
            h:<input id="input-croppr-height" type="hidden"><br>
            x:<input id="input-croppr-x" type="hidden"><br>
            Y:<input id="input-croppr-y" type="hidden"><br>
            <button id="clickCrop">crop</button>
        </div>
    </div>
    <div id="selectImage">
        <div id="imgLibraryFolder" class="w25 folder">
            <div id="imgLibraryFolderContainer">
                <?php echo $library->getHtmlFolders();?>
            </div>
            <div class="aimsImgLibraryFolder item newfolder">nieuwe folder</div>
        </div>
        <div class="w25" id="onImageSelected" class="center">
            <img id="selectedImageImg" src="">
            <div id="selectedImageTitle">NAAM Bestand</div>
            <button id="useImage" class="w100">Gebruiken</button>
            <button id="renameImageName" class="w100">Naam wijzigen</button>
            <button id="removeImageName" class="w100">Verwijderen</button>
        </div>
        <div class="w75 library">
            <div id="formUpload" class="w100 upload">
                <label class="custom-file-upload">
                    <input id="imageFile" name="imageFile" type="file" class="imageFile"  accept="image/*"   />
                    Upload nieuwe foto
                </label>
            </div>
            <div id="uploadResult" class="w100" style="display: none">
                <input type="button" value="Deze foto nu uploaden" class="w100" id="resizeUploadImage"/>
                <div class="w100 center" >
                    <img src="" id="preview" >
                </div>
            </div>

            <?php echo $library->getHtmlContent();?>
        </div>
    </div>
</div>

</div>

