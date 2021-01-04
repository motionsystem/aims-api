<?php
namespace Aims;


/**
 * smartUrl
 */


class smartUrl
{

    private $parameter;
    private $adminMode = FALSE;
    private $languages ;
    private $defilterArray = ['admin'];
    private $languagesArray = ['nl','en'];

    public function  __construct(){
        $this->set_arrayParameter();
    }

    public function friendlyUrl($url){
        if($url[0] === '/'){
            $url = substr($url."#",1,-1);
        }
        $url = strtolower($url);
        $convert_table = array('à' => 'a',' ' => '_',  'ô' => 'o',  '�' => 'd',  'ḟ' => 'f',  'ë' => 'e',  'š' => 's',  'ơ' => 'o', 'ß' => 'ss', 'ă' => 'a',  'ř' => 'r',  'ț' => 't',  'ň' => 'n',  '�' => 'a',  'ķ' => 'k','�' => 's',  'ỳ' => 'y',  'ņ' => 'n',  'ĺ' => 'l',  'ħ' => 'h',  'ṗ' => 'p',  'ó' => 'o','ú' => 'u',  'ě' => 'e',  'é' => 'e',  'ç' => 'c',  '�' => 'w',  'ċ' => 'c',  'õ' => 'o','ṡ' => 's',  'ø' => 'o',  'ģ' => 'g',  'ŧ' => 't',  'ś' => 's',  'ė' => 'e',  'ĉ' => 'c','ś' => 's',  'î' => 'i',  'ű' => 'u',  'ć' => 'c',  'ę' => 'e',  'ŵ' => 'w',  'ṫ' => 't','ū' => 'u',  '�' => 'c',  'ö' => 'o',  'è' => 'e',  'ŷ' => 'y',  'ą' => 'a',  'ł' => 'l','ų' => 'u',  'ů' => 'u',  'ş' => 's',  'ğ' => 'g',  'ļ' => 'l',  'ƒ' => 'f',  'ž' => 'z','ẃ' => 'w',  'ḃ' => 'b',  'å' => 'a',  'ì' => 'i',  'ï' => 'i',  'ḋ' => 'd',  'ť' => 't','ŗ' => 'r',  'ä' => 'a',  'í' => 'i',  'ŕ' => 'r',  'ê' => 'e',  'ü' => 'u',  'ò' => 'o','ē' => 'e',  'ñ' => 'n',  'ń' => 'n',  'ĥ' => 'h',  '�' => 'g',  'đ' => 'd',  'ĵ' => 'j','ÿ' => 'y',  'ũ' => 'u',  'ŭ' => 'u',  'ư' => 'u',  'ţ' => 't',  'ý' => 'y',  'ő' => 'o','â' => 'a',  'ľ' => 'l',  'ẅ' => 'w',  'ż' => 'z',  'ī' => 'i',  'ã' => 'a',  'ġ' => 'g','�' => 'm',  '�' => 'o',  'ĩ' => 'i',  'ù' => 'u',  'į' => 'i',  'ź' => 'z',  'á' => 'a','û' => 'u',  'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',  'ĕ' => 'e','À' => 'A',  'Ô' => 'O',  'Ď' => 'D',  'Ḟ' => 'F',  'Ë' => 'E',  'Š' => 'S',  'Ơ' => 'O','Ă' => 'A',  'Ř' => 'R',  'Ț' => 'T',  'Ň' => 'N',  'Ā' => 'A',  'Ķ' => 'K',  'Ĕ' => 'E','Ŝ' => 'S',  'Ỳ' => 'Y',  'Ņ' => 'N',  'Ĺ' => 'L',  'Ħ' => 'H',  'Ṗ' => 'P',  'Ó' => 'O','Ú' => 'U',  'Ě' => 'E',  'É' => 'E',  'Ç' => 'C',  'Ẁ' => 'W',  'Ċ' => 'C',  'Õ' => 'O','Ṡ' => 'S',  'Ø' => 'O',  'Ģ' => 'G',  'Ŧ' => 'T',  'Ș' => 'S',  'Ė' => 'E',  'Ĉ' => 'C','Ś' => 'S',  'Î' => 'I',  'Ű' => 'U',  'Ć' => 'C',  'Ę' => 'E',  'Ŵ' => 'W',  'Ṫ' => 'T','Ū' => 'U',  'Č' => 'C',  'Ö' => 'O',  'È' => 'E',  'Ŷ' => 'Y',  'Ą' => 'A',  '�' => 'L','Ų' => 'U',  'Ů' => 'U',  'Ş' => 'S',  'Ğ' => 'G',  'Ļ' => 'L',  'Ƒ' => 'F',  'Ž' => 'Z','Ẃ' => 'W',  'Ḃ' => 'B',  'Å' => 'A',  'Ì' => 'I',  '�' => 'I',  'Ḋ' => 'D',  'Ť' => 'T','Ŗ' => 'R',  'Ä' => 'A',  '�' => 'I',  'Ŕ' => 'R',  'Ê' => 'E',  'Ü' => 'U',  'Ò' => 'O','Ē' => 'E',  'Ñ' => 'N',  'Ń' => 'N',  'Ĥ' => 'H',  'Ĝ' => 'G',  '�' => 'D',  'Ĵ' => 'J','Ÿ' => 'Y',  'Ũ' => 'U',  'Ŭ' => 'U',  'Ư' => 'U',  'Ţ' => 'T',  '�' => 'Y',  '�' => 'O','Â' => 'A',  'Ľ' => 'L',  'Ẅ' => 'W',  'Ż' => 'Z',  'Ī' => 'I',  'Ã' => 'A',  'Ġ' => 'G','Ṁ' => 'M',  'Ō' => 'O',  'Ĩ' => 'I',  'Ù' => 'U',  'Į' => 'I',  'Ź' => 'Z',  '�' => 'A','Û' => 'U',  'Þ' => 'Th', '�' => 'Dh', 'Æ' => 'Ae', ' ' => '_', '%20' => '_', ',' => '', '\'' => '', ';' => '', ':' => '',  '<' => '', '>' => '',  '\\' => '', '{' => '', '}' => '', '[' => '', ']' => '', '|' => '', '+' => '',  '!' => '', '@' => '', '#' => '', '$' => '', '%' => '', '^' => '', '&' => '', '*' => '', '(' => '', ')' => '', '"' => '', '`' => '', '~' => '', '^' => '', );
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
            print_r($requestURL[$i]);
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
    }

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    public function getAdminMode(){
        return $this->adminMode;
    }

};

