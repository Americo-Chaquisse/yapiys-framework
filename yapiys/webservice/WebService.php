<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Webservice
 *
 * @author Mario Junior
 */
abstract class WebService {
    
    
    public function onRequest($params){
        
        
    }

    private function output($msg){
                 
        $prefix = "";
        $suffix = "";
        
        if(isset($_REQUEST['callback'])){
            
            $prefix = $_REQUEST['callback'].'(';
            $suffix = ")";
            
            
        }
        
        echo $prefix.json_encode(ArrayEncoder::array_encode_safe($msg)).$suffix;
        
    }
    
    public function error($message,$code=0){
        $error =array('code'=>$code,'message'=>$message);
        $response=array('error'=>$error);
       
        $this->output($response);
        exit();
    }
    
    public function onGet($params){
        
            
    }
    
    
    public function onPost($params){
        
        
    }
    
    public function send_response($resp,$code=1){
        $respx=array('data'=>$resp,'code'=>$code);
        $response=array('response'=>$respx);       
        $this->output($response);
        exit();
    }
    
    public function json_response($resp,$code=1){
        $respx=array('data'=>$resp,'code'=>$code);
        $this->output($resp);
        exit();
    }
    
    public function send_json($resp,$code=1){
        $this->output($resp);
        exit();
    }
    
    public function send_answer($resp,$code=1){
        $this->output(array('answer'=>$resp));
        exit();
    }
    
     public function send_valid($resp,$code=1){
        $this->output(array('valid'=>$resp));
        exit();
    }
    
    
}