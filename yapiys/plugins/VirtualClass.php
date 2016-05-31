<?php

class VirtualClass{
    
    public $methods = array();
    
    
    public function hasMethod($name){
        
        return isset($this->methods[$name]);
    
    }
    
    
    public function setMethod($name,$callable){
        
        $this->methods[$name] = $callable;
    
    
    }
    
    
    
        
     public function __call($name, $arguments) {
         
         //Metodo real
         if(is_callable($name)){
             return call_user_func_array($name, $arguments);
         }
         
         
         //Existe o metodo virtual
         if(isset($this->methods[$name])){
            
             return call_user_func_array($this->methods[$name],$arguments);
         
         }
        
         
     }
    
    
    


}