<?php

/**
 * Description of AbstractBundle
 *
 * @author Mario Junior
 */
abstract class AbstractBundle {
         
    
    public function __construct($data_=NULL) {        
        if($data_){
            
            $this->initialize($data_);              
        }
    }
    
    public abstract function initialize($array);
    
    /**
     * Indica se o bundle contem um determinado valor.z
     * @param type $key Chave do respectivo valor.
     */
    public abstract function contains($key);
    
    public abstract function set($name,$value);
    
    public abstract function get($name);
    
    public abstract function getJSON();
    
    public abstract function getInternalArray();

    private function ifFilter($variable,$arguments){

        if(count($arguments)>0){

            $filter = $arguments[0];

            if(is_array($variable)){

                return filter_var_array($variable,$filter);

            }

            return filter_var($variable,$filter);

        }

        return $variable;

    }
    
    public function __call($name, $arguments) {
        if(is_callable($name)){
            return call_user_func_array($name,$arguments);
        }else{
           if(strlen($name)<5){
               return FALSE;
           } 
        }
                
        
        $prefix = substr($name, 0, 3);
        $prefix=  strtolower($prefix);
        $key=  substr($name, 3);
        $key= lcfirst($key);
        
        if($prefix==='set'||$prefix==='on_'){
            
            $this->set($key, $arguments[0]);
                   
        }else if($prefix==='get'){
            
            $val =  $this->get($key);
            return $this->ifFilter($val,$arguments);
            
            
        }else if($prefix==='has'){
            
            $val =  $this->contains($key);
            
            
        }else if($prefix==='is_'){
            
            $value = $this->get($key);
            
            if(is_bool($value)){
                
                return $value;
                
            }else{
                
                return FALSE;
            }
            
        }
        
        
        
    }
    
    
     /**
      * Actualiza ou insere um valor em um array que eh valor do bundle
      * @param type $valueName nome do valor que corresponde a um array
      * @param type $value valor que deve ser inserido ou actualizado
      * @param type $key chave do valor  no array
      */
     public function updateArrayValue($valueName, $value, $key = null) {
        $array=array();
        
        if($this->contains($valueName)){
            $array = $this->get($valueName);                       
        }
        
         
        if(is_array($array)){
                
            if(is_string($key)){ 
          
                    $array[$key]=$value;
                    
                }else{
                    
                    $array[]=$value;
                }                
            
            $this->set($valueName, $array);                
        }
        
    }
    
    
    
public function unsetArrayValue($valueName, $value_key) {
        $array=array();
        
        if($this->contains($valueName)){
            $array = $this->get($valueName);                   
        }
        
         
        if(is_array($array)){
                
            if(is_string($value_key)){ 
                
                if(isset($array[$value_key])){
                    unset($array[$value_key]);
                }
               
             }
             
             $this->set($valueName, $array);
            
                     
        }
        
    }
    
    
    
public function getArrayValue($valueName, $value) {
        $array=array();
        
        if($this->contains($valueName)){      
            $array = $this->get($valueName);                       
        }
        
         
        if(is_array($array)){
            
            if(isset($array[$value])){
                         
           
                
                return $array[$value];
                
                
            }
                     
        }
        
         return NULL;
        
    }

    
    
    public function importValues($values){
        if(is_array($values)){
            
            foreach ($values as $key => $value) {
                $this->set($key, $value);
            }
        
        }
    }
    
    public function toJSON($name){
        if($this->contains($name)){
            $json_string=$this->get($name);   
            
            if(is_string($json_string)){     
                
                return json_decode($json_string);
                
            }else{
                false;
            }
            
            return false;
          
        }else{
            return false;
        }
    }
    
}