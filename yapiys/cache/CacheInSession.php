<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CacheInSession
 *
 * @author Mario Junior
 */
class CacheInSession {

    public static function isCached($name){
        $cache = $_SESSION['cache'];
        return isset($cache[$name]);
    }
    
    
    public static function push($name,$value){
        $_SESSION['cache'][$name]=$value; 
    }
    
    public static function get($name){
        
        if(isset($_SESSION['cache'])){

            $cache=$_SESSION['cache'];
            if(isset($cache[$name])){
                return $cache[$name];
            }
            
        }
        
        return false;
    }
    
    public static function flushCache(){
        if(isset($_SESSION['cache'])){
            $_SESSION['cache']=array();
        }
    }
    
    public static function initialize(){
        if(!isset($_SESSION['cache'])){
            $_SESSION['cache']=array();
        }

    }


    
}