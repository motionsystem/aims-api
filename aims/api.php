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

    public function __construct($config)
    {
        $this->CONFIG = $config;
        $this->debug = ($config['debug'] ? true : false);
        $this->install = ($config['debug'] && $config['install'] ? true : false);
    }

    public function forcecDebug($debug = false, $install = false){
        $this->debug = $debug;
        $this->install = $install;
    }

    public function post($url,$arrBody, $arrReturn)
    {
        $this->reqeustFields = [];
        if(is_array($arrBody)){
            $this->reqeustFields = $arrBody;
        }

        if(!isset($_SESSION['Oauth-Token'])){
            $this->authorization();
        }

        $data = $this->curlPostUrl($url);
        $returnData = $data;
        if(is_array($arrReturn)) {
            $returnData = [];
            foreach ($arrReturn as $key) {
                $arrKey = explode('|',$key);
                $extract = $this->collectData($data,$arrKey);
                $extractKey = $extract['key'];
                $extractData = $extract['data'];
                $returnData[$extractKey] = $extractData;
            }
        }
        return $returnData;
    }

    private function collectData($data,$arrKey){
        $arr = $data;
        foreach ($arrKey as $key) {
            if(isset($arr[$key])){
                $arr =  $arr[$key];
            }
        }
        return ['key'=>$key,'data'=>$arr];
    }

    private function authorization(){
        $res = $this->curlPostUrl('authorization');
        $_SESSION['Oauth-Token'] = $res['APPDATA']['oauthToken'];
        return  (empty($_SESSION['Oauth-Token']) ? false : true);
    }

    private function setDebugInfo(){
        if($this->debug){
            $this->reqeustFields['STUDIO'] = $this->debug;
            $this->reqeustFields['DEBUG'] = $this->debug;
        }
        if($this->install && $this->debug){
            $this->reqeustFields['INSTALL'] = $this->install;
        }
    }

    private function curlPostUrl($page)
    {

        $url = $this->CONFIG['apiHost'] . '/' . $this->CONFIG['apiVersion'] . '/' . $page;
        $token = (!isset($_SESSION['Oauth-Token']) ? $_SESSION['Oauth-Token'] : null );

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
        $json = curl_exec($curl);
        return json_decode($json,true);

    }


}
