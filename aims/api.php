<?php
namespace Aims;


/**
 * API
 */
class api
{

    private $CONFIG = [];
    private $debug = [];
    private $install = [];
    private $msyUser = null;
    private $postData = [];

    private $ip;

    private $parameter;
    private $adminMode = FALSE;
    private $languages ;
    private $defilterArray = ['admin'];
    private $languagesArray = ['nl','en'];



    public function __construct($config)
    {
        $this->CONFIG = $config;
        $this->debug = ($config['debug'] ? true : false);
        $this->install = ($config['debug'] && $config['install'] ? true : false);
        $this->checkAjaxPost();
        $this->set_arrayParameter();
        $this->getIp();

        $arrWhitelist = explode('|', $this->CONFIG['whitelistIp']);

        $studio = (in_array($this->ip,$arrWhitelist) ? true: false);
        define('STUDIO', $studio);
    }

    private function getIp(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $this->ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
    }

    private  function array_key_first(array $arr){

        foreach ($arr as $key => $unused){
            return $key;
        }
        return NULL;

    }

    function checkAjaxPost(){
        $requestData = json_decode(file_get_contents('php://input'), true);
        if(!$requestData && $_POST){
            $requestData = $_POST;
        }
        if(!$requestData['aimsPage']){
            return false;
        }

        $page = $requestData['aimsPage'];
        unset($requestData['aimsPage']);
        require_once($_SERVER['DOCUMENT_ROOT'] .'/vendor/allinmotion/aims/ajaxPost/'.$page.'.php');
        exit;
    }

    public function forcecDebug($debug = false, $install = false){
        $this->debug = $debug;
        $this->install = $install;
    }

    public function post($url,$arrBody, $arrReturn = null)
    {

        $this->reqeustFields = [];
        if(is_array($arrBody)){
            $this->reqeustFields = $arrBody;
        }

        if(!empty($_SESSION['basketCode'])){
            $this->reqeustFields['basketCode'] = $_SESSION['basketCode'];
        }

        if(!isset($_SESSION['Oauth-Token'])){
            $this->authorization();
        }

        $data = $this->curlPostUrl($url);

        //No Access On MotionSystem the time is expired renew you'r token
        if(isset($data['ERROR']['199'])){
            if($this->renewtoken()){
                $data = $this->curlPostUrl($url);
            }
        }

        $this->extractDefaultInformation($data);

        if(isset($_POST['email']) &&  isset($_POST['password'])){
            if($this->CONFIG ['autoCleanImage']){

                $data = $this->curlPostUrl('process-get/content-images');
                if($data['APPDATA']['images']) {
                    $images = $data['APPDATA']['images'];
                    $this->removeNonUsedImages($images);
                }
                // remove $_POST object by redirecting to this page.
                header("Location: ". $_SERVER['SCRIPT_URI']);
                exit;
            }
        }

        $returnData = $data;
        if($arrReturn && is_array($arrReturn)) {
            $returnData = [];
            foreach ($arrReturn as $key) {
                $arrKey = explode('|',$key);
                $extract = $this->collectData($data,$arrKey);
                $extractKey = $extract['key'];
                $extractData = $extract['data'];
                $returnData[$extractKey] = ($extractData ? $extractData : null);
            }
        }

        $this->postData = $returnData;
        return $returnData;
    }

    private function extractDefaultInformation($data){

        $this->msyUser =  (isset($data['APPDATA']['msyUser']) ? $data['APPDATA']['msyUser'] : null);
        $this->template = $data['APPDATA']['pageInfo'];
    }

    private function collectData($data,$arrKey){
        $arr = $data;
        foreach ($arrKey as $key) {
            if(!isset($arr[$key])){
                return ['key'=>$key,'data'=>null];
            }
            $arr =  $arr[$key];
        }
        return ['key'=>$key,'data'=>$arr];
    }

    private function authorization(){
        $res = $this->curlPostUrl('authorization');
        $_SESSION['Oauth-Token'] = $res['APPDATA']['oauthToken'];
        return  (empty($_SESSION['Oauth-Token']) ? false : true);
    }

    private function renewtoken(){
        $res = $this->curlPostUrl('renewtoken');
        $_SESSION['Oauth-Token'] = $res['APPDATA']['oauthToken'];
        return  (empty($_SESSION['Oauth-Token']) ? false : true);
    }

    private function setDebugInfo(){
        if($this->debug){
            $this->reqeustFields['STUDIO'] = ($this->debug ? 'true':'false');
            $this->reqeustFields['DEBUG'] = ($this->debug ? 'true':'false');;
        }
        if($this->install && $this->debug){
            $this->reqeustFields['INSTALL'] = ($this->install ? 'true':'false');
        }
    }

    private function curlPostUrl($page)
    {

        $url = $this->CONFIG['apiHost'] . '/' . $this->CONFIG['apiVersion'] . '/' . $page;
        $token = (isset($_SESSION['Oauth-Token']) ? $_SESSION['Oauth-Token'] : null );

        $this->setDebugInfo();
        $curl = curl_init($url );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Oauth-Key: " . $this->CONFIG['apiKey'],"Oauth-Token: ".$token ));

        if(isset($this->reqeustFields) && $this->reqeustFields)
        {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->reqeustFields));
            $this->reqeustFields = false;
        }

        // Make the REST call, returning the result
        $data = curl_exec($curl);
        $json = json_decode($data,true);
        if($this->debug && ( !$json  || (!empty($json['ERROR']) && !$json['ERROR']['199'] ))){
            //    print_r($data);
            //    exit;
        }

        return $json;

    }


    public function getPageData(){
        $data = $this->postData;

        $data['pageContent']['data'] = $this->replaceEmptyContentOnlyByAdmin($data['pageContent']['data'],'NIEUWE TEKST');
        $data['admin'] = $this->getAdminMode();
        $data['login'] = $this->msyUser();
        $data['url'] = $strurl;
        $data['strUrl'] = str_replace(['/','-','_'],' ',$strurl);

        if($data['admin']) {
            foreach ($data['pageMenu'] as $key => $arr) {
                //$data['pageMenu'][$key]['msyPageUrl'] = 'admin/' . $data['pageMenu'][$key]['msyPageUrl'];
                $data['pageMenu'][$key]['class'] .= ' admin';
            }
        }

        if(STUDIO || $data['login']) {
            $url = str_replace('admin/','',$data['url']);
            if (!$data['admin']) {
                $data['pageMenu'][] = [
                    'msyPageName' => '(admin)',
                    'msyPageUrl' => 'admin/' .$url ,
                    'class' => '',
                ];
            } else {
                $data['pageMenu'][] = [
                    'msyPageName' => '(public)',
                    'msyPageUrl' => $url,
                    'class' => 'skipAdminLink',
                ];
            }
        }


        $pageContent = [];
        if(isset($data['pageContent']['data'])) {
            foreach ($data['pageContent']['data'] as $key => $arr) {
                $countActiveSpots = count($data['pageContent']['spot'][$key]);

                if ($countActiveSpots === 1) {

                    foreach ($arr as $num => $arrItem) {
                        $itemKey = $this->array_key_first($arrItem);
                        $pageContent[$key][] = $arrItem[$itemKey][0];
                    }

                } else {
                    $pageContent[$key] = $arr;
                }
            }
        }
        $data['pc'] = $pageContent;

        /*
        /// voor  een nieuw content  block in de ADMIN
        $newContent = $data['pageContent']['newRow'];
        foreach ($data['pageContent']['spot'] as $keySpot => $arrSpot) {
            $spotName = 'mainText';
            $arrSpot = $data['pageContent']['spot'][$spotName];
            foreach ($arrSpot as $key => $arr) {
                $firstInput = array_values($arr['label'])[0];
                print_r($firstInput);
            }
        }
        print_r($data['pageContent']['data']['mainText']);
        print_r($data['pageContent']['spot']['mainText']);exit;
        */

        return $data;
    }


    /*********************************/

    public function friendlyUrl($url){
        if($url[0] === '/'){
            $url = substr($url."#",1,-1);
        }
        $url = strtolower($url);
        $convert_table = array('à' => 'a',' ' => '-',  'ô' => 'o',  '�' => 'd',  'ḟ' => 'f',  'ë' => 'e',  'š' => 's',  'ơ' => 'o', 'ß' => 'ss', 'ă' => 'a',  'ř' => 'r',  'ț' => 't',  'ň' => 'n',  '�' => 'a',  'ķ' => 'k','�' => 's',  'ỳ' => 'y',  'ņ' => 'n',  'ĺ' => 'l',  'ħ' => 'h',  'ṗ' => 'p',  'ó' => 'o','ú' => 'u',  'ě' => 'e',  'é' => 'e',  'ç' => 'c',  '�' => 'w',  'ċ' => 'c',  'õ' => 'o','ṡ' => 's',  'ø' => 'o',  'ģ' => 'g',  'ŧ' => 't',  'ś' => 's',  'ė' => 'e',  'ĉ' => 'c','ś' => 's',  'î' => 'i',  'ű' => 'u',  'ć' => 'c',  'ę' => 'e',  'ŵ' => 'w',  'ṫ' => 't','ū' => 'u',  '�' => 'c',  'ö' => 'o',  'è' => 'e',  'ŷ' => 'y',  'ą' => 'a',  'ł' => 'l','ų' => 'u',  'ů' => 'u',  'ş' => 's',  'ğ' => 'g',  'ļ' => 'l',  'ƒ' => 'f',  'ž' => 'z','ẃ' => 'w',  'ḃ' => 'b',  'å' => 'a',  'ì' => 'i',  'ï' => 'i',  'ḋ' => 'd',  'ť' => 't','ŗ' => 'r',  'ä' => 'a',  'í' => 'i',  'ŕ' => 'r',  'ê' => 'e',  'ü' => 'u',  'ò' => 'o','ē' => 'e',  'ñ' => 'n',  'ń' => 'n',  'ĥ' => 'h',  '�' => 'g',  'đ' => 'd',  'ĵ' => 'j','ÿ' => 'y',  'ũ' => 'u',  'ŭ' => 'u',  'ư' => 'u',  'ţ' => 't',  'ý' => 'y',  'ő' => 'o','â' => 'a',  'ľ' => 'l',  'ẅ' => 'w',  'ż' => 'z',  'ī' => 'i',  'ã' => 'a',  'ġ' => 'g','�' => 'm',  '�' => 'o',  'ĩ' => 'i',  'ù' => 'u',  'į' => 'i',  'ź' => 'z',  'á' => 'a','û' => 'u',  'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',  'ĕ' => 'e','À' => 'A',  'Ô' => 'O',  'Ď' => 'D',  'Ḟ' => 'F',  'Ë' => 'E',  'Š' => 'S',  'Ơ' => 'O','Ă' => 'A',  'Ř' => 'R',  'Ț' => 'T',  'Ň' => 'N',  'Ā' => 'A',  'Ķ' => 'K',  'Ĕ' => 'E','Ŝ' => 'S',  'Ỳ' => 'Y',  'Ņ' => 'N',  'Ĺ' => 'L',  'Ħ' => 'H',  'Ṗ' => 'P',  'Ó' => 'O','Ú' => 'U',  'Ě' => 'E',  'É' => 'E',  'Ç' => 'C',  'Ẁ' => 'W',  'Ċ' => 'C',  'Õ' => 'O','Ṡ' => 'S',  'Ø' => 'O',  'Ģ' => 'G',  'Ŧ' => 'T',  'Ș' => 'S',  'Ė' => 'E',  'Ĉ' => 'C','Ś' => 'S',  'Î' => 'I',  'Ű' => 'U',  'Ć' => 'C',  'Ę' => 'E',  'Ŵ' => 'W',  'Ṫ' => 'T','Ū' => 'U',  'Č' => 'C',  'Ö' => 'O',  'È' => 'E',  'Ŷ' => 'Y',  'Ą' => 'A',  '�' => 'L','Ų' => 'U',  'Ů' => 'U',  'Ş' => 'S',  'Ğ' => 'G',  'Ļ' => 'L',  'Ƒ' => 'F',  'Ž' => 'Z','Ẃ' => 'W',  'Ḃ' => 'B',  'Å' => 'A',  'Ì' => 'I',  '�' => 'I',  'Ḋ' => 'D',  'Ť' => 'T','Ŗ' => 'R',  'Ä' => 'A',  '�' => 'I',  'Ŕ' => 'R',  'Ê' => 'E',  'Ü' => 'U',  'Ò' => 'O','Ē' => 'E',  'Ñ' => 'N',  'Ń' => 'N',  'Ĥ' => 'H',  'Ĝ' => 'G',  '�' => 'D',  'Ĵ' => 'J','Ÿ' => 'Y',  'Ũ' => 'U',  'Ŭ' => 'U',  'Ư' => 'U',  'Ţ' => 'T',  '�' => 'Y',  '�' => 'O','Â' => 'A',  'Ľ' => 'L',  'Ẅ' => 'W',  'Ż' => 'Z',  'Ī' => 'I',  'Ã' => 'A',  'Ġ' => 'G','Ṁ' => 'M',  'Ō' => 'O',  'Ĩ' => 'I',  'Ù' => 'U',  'Į' => 'I',  'Ź' => 'Z',  '�' => 'A','Û' => 'U',  'Þ' => 'Th', '�' => 'Dh', 'Æ' => 'Ae', '%20' => '-', ',' => '', '\'' => '', ';' => '', ':' => '',  '<' => '', '>' => '',  '\\' => '', '{' => '', '}' => '', '[' => '', ']' => '', '|' => '', '+' => '',  '!' => '', '@' => '', '#' => '', '$' => '', '%' => '', '^' => '', '&' => '', '*' => '', '(' => '', ')' => '', '"' => '', '`' => '', '~' => '', '^' => '', );
        return str_replace(array_keys($convert_table), array_values($convert_table), $url );
    }

    public function set_arrayParameter(){
        $replaceREQUEST_URI = $this->friendlyUrl($_SERVER['REQUEST_URI'] );

        $filterQuestionMark = explode('?',$replaceREQUEST_URI);
        $requestURL = explode('/',$filterQuestionMark[0]);


        for($i= 0;$i < count($requestURL) ;$i++){
            if(empty($requestURL[$i])){
                continue;
            }
            if( in_array($requestURL[$i] ,$this->defilterArray)  ){

                if( in_array($requestURL[$i], $this->languagesArray)  ){
                    $this->languages = $requestURL[$i];
                }
                if($requestURL[$i] === "admin"){
                    $this->adminMode = TRUE;
                }
                unset($requestURL[$i]);
            }
        }

        $this->parameter = array_values($requestURL);

        if(!$this->parameter[0]){
            $this->parameter[0] = 'home';
        }
    }


    private function removeNonUsedImages($usedImg){
        $data = $this->readFolder('/upload/');
        $deleteImg = [];
        foreach ($data as $folderName => $arrFolder) {
            if(!is_string($folderName)){
                continue;
            }
            foreach ($arrFolder as $fileKey => $arrFile) {
                if(!$arrFile['url']){
                    // print_r($arrFile);
                    continue;
                }
                if(!in_array($arrFile['url'], $usedImg)) {
                    $arrDeleteImg[] = $arrFile['url'];
                }
            }
        }
        if($arrDeleteImg) {
            $this->deleteUnusedImage($arrDeleteImg);
        }
    }

    function readFolder($dir)
    {
        $data = [];
        $folder = scandir($_SERVER['DOCUMENT_ROOT'] . $dir);
        foreach ($folder as $name) {
            $file = $dir . $name;
            if ($name === '.' || $name === '..' || $name === 'orignal' || $name === '_deleted') {
                continue;
            }
            if (is_dir($_SERVER['DOCUMENT_ROOT'] .$file )) {
                if ($name[0] !== "_") {
                    $data[$name] = $this->readFolder($file. '/' );
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

    private function createFolderIfnotExist($folder){
        if(!is_dir($folder)){
            if (!mkdir($folder, 0777, true)) {
                die('ERROR Failed to create folders...');
            }
        }
    }

    private function deleteUnusedImage($arrDeleteImg){
        foreach ($arrDeleteImg as $deleteFile) {
            $arrFile = pathinfo($deleteFile);
            $folder = $arrFile['dirname'] . '/_deleted/';
            $this->createFolderIfnotExist($_SERVER['DOCUMENT_ROOT'] . $folder);
            rename($_SERVER['DOCUMENT_ROOT']. $deleteFile, $_SERVER['DOCUMENT_ROOT'] . $folder . $arrFile['basename']);
        }
    }


    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    public function getStringParameter()
    {
        return implode('/',$this->parameter);
    }

    public function getAdminMode(){
        return $this->adminMode;
    }

    public function msyUser(){
        return $this->msyUser;
    }

    public function replaceEmptyContentOnlyByAdmin($content, $defaultLine){
        if(!$content || !$this->getAdminMode() || !$this->msyUser()){
            return $content;
        }

        foreach ($content as $block => $arrBlock) {
            foreach ($arrBlock as $group => $arrGroup) {
                foreach ($arrGroup  as $spot => $arrSpot) {
                    foreach ($arrSpot as $item => $arrItem) {
                        foreach ($arrItem as $key => $val) {
                            if (!trim($val)) {
                                $content[$block][$group][$spot][$item][$key] = $defaultLine;
                            }
                        }
                    }
                }
            }
        }
        return $content;
    }


    public function getTemplatePage(){
        $page = $this->template['msyTemplateName'];
        $admin = $this->getAdminMode();
        $login = $this->msyUser();

        if($admin  && !$login){
            //$page = 'login';
        }

        if(!$page){
            $page = '404';
        }

        return $page;
    }

};

