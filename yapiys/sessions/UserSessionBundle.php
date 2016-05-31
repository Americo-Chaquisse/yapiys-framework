<?php

/**
 * Description of UserSessionBundle
 *
 * @author Mario Junior
 */
class UserSessionBundle extends AbstractBundle {

    CONST user_data_session_variable_name="user_session_data";
    
    public function contains($key) {
        $accessManager=$_SESSION[self::user_data_session_variable_name];
        return isset($accessManager[$key]);
    }

    public function get($name) {
        $accessManager=$_SESSION[self::user_data_session_variable_name];
        if(isset($accessManager[$name])){
            return $accessManager[$name];
        }
        
        return FALSE;
    }

    public function getJSON() {
        $accessManager=$_SESSION[self::user_data_session_variable_name];
        return json_encode($accessManager);
    }

    public function initialize($array) {
        $_SESSION[self::user_data_session_variable_name]=$array;
    }
    
    public function remove($name){        
        if(isset($_SESSION[self::user_data_session_variable_name][$name])){
            
            unset($_SESSION[self::user_data_session_variable_name][$name]);
            
        }   
        
    }

    public function set($name,$value) {
        $_SESSION[self::user_data_session_variable_name][$name]=$value;
    }

    public function getInternalArray() {
        return $_SESSION[self::user_data_session_variable_name];
    }

}