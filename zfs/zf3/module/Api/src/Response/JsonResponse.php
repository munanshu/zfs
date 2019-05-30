<?php

namespace Api\Response;

use Zend\Http\Response;
use Zend\Json\Json;

 
class JsonResponse extends Response
{
    
    protected $_content_type = 'application/json';

    public function getHeaders()
    {
        $headers = parent::getHeaders();
        if (!$headers->has('content-type')) {
            $headers->addHeaderLine('content-type', $this->_content_type);
        }
        return $headers;
    }

     
    public function setContent($value)
    {
        $this->content = Json::encode($value);
        return $this;
    }


    public function SendResponse(){
        $headers = $this->set_headers();
        header("content-type:application/json");
        echo $this->getContent();
        exit;
    }

    private function set_headers(){
        $headers = parent::getHeaders();
        $headers->addHeaderLine('HTTP/1.1',$this->getStatusCode()." ".$this->getReasonPhrase() );
        $headers->addHeaderLine('Content-type', $this->_content_type);
        return $headers;
     }
    public function isJson($string)
    {
        // decode the JSON data
    $result = json_decode($string,true);
    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        return array('error'=>true,'message'=>$error);
    }
    
    return $result;
    } 
}
