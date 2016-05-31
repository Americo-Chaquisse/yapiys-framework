<?php

/**
 * Description of UIBundle
 *
 * @author Mario Junior
 */
class Bundle extends AbstractBundle {
    var $data= array();    
 
    public function set($name,$value){
        $this->data[$name]=$value;
    }
    
    public function get($name){
        if(isset($this->data[$name])){
            return $this->data[$name];
        }
    }
    
    public function defineIn($values,$name){
        if(isset($this->data[$name])){
            
            if(is_array($values)&&is_array($this->data[$name])){
                
                foreach($values as $key => $value){
                    
                    if(!isset($this->data[$name][$key])){
                        $this->data[$name][$key] = $value;                        
                    }
                       
                }
                
            }
            
        }else{
            
            $this->set($name,$values);
            
        }
    }
    
    
    public function item($index){
        if(isset($this->data[$index])){
            return $this->data[$index];
        }               
    }
    
    public function remove($name){
        
        if(isset($this->data[$name])){
            unset($this->data);
        }        
    }
    
       
    public function getJSON($name=false){
        
        if(!$name){
             return json_encode($this->data); 
        }
        
        if(isset($this->data[$name])){
            return json_decode($this->data[$name]);
        }
        
        return false;
     }
    
    public function getInternalArray(){
        return $this->data;
    }
    
    public function toArray(){
        return $this->data;
    }
    
    public function isEmpty(){
        
        return count($this->data)==0;
        
    }
    
    public function total(){
        
        return count($this->data);
        
    }
    
    public function contains($keys) {
        $matches = 0;
        if(is_array($keys)){

            foreach($keys as $key){

                if($this->contains($key)){
                    $matches++;
                }

            }

            return $matches == count($keys);

        }
        return isset($this->data[$keys]);
    }

    public function initialize($array) {
        $this->data=$array;
    }
    
}