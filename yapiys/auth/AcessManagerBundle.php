<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AcessManagerBundle
 *
 * @author Mario Junior
 */
class AcessManagerBundle extends AbstractBundle{
    CONST access_manager_session_variable_name="app_access_manager";
    
    public function contains($key) {
        $accessManager=$_SESSION[self::access_manager_session_variable_name];
        return isset($accessManager[$key]);
    }

    public function get($name) {
        $accessManager=$_SESSION[self::access_manager_session_variable_name];
        if(isset($accessManager[$name])){
            return $accessManager[$name];
        }
        
        return FALSE;
    }

    public function getJSON() {
        $accessManager=$_SESSION[self::access_manager_session_variable_name];
        return json_encode($accessManager);
    }

    public function initialize($array) {
        $_SESSION[self::access_manager_session_variable_name]=$array;
    }

    public function set($name,$value) {
        $_SESSION[self::access_manager_session_variable_name][$name]=$value;
    }

    public function getInternalArray() {
        return $_SESSION[self::access_manager_session_variable_name];
    }

   

}