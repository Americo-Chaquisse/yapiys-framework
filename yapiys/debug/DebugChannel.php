<?php

class DebugChannel{
    public static $levels = false;
    public static $enabled = false;

    public static function init(){
    
                
         $app = loadConfiguration('app');
        
         if(property_exists($app,'debug')){
            
             $debug_settings = $app->debug;
             
             //Mostrar mensages de um certo nivel ou de varios
             if(property_exists($debug_settings,'levels')){
                
                   $lvs = $debug_settings->levels;
                   $levels = false;
                    
                   if(is_array($lvs)){
                        
                       $levels = $lvs;
                   
                   }else{
                       
                        $levels = array($lvs);   
                       
                   }
                 
                   DebugChannel::$levels = $levels;
             
             }else{
                 
                 DebugChannel::$levels = array();
                 
             }
             
             //Debug activo ou inactivo
             if(property_exists($debug_settings,'enabled')){
                    
                    DebugChannel::$enabled = $debug_settings->enabled;
                    
             }
             
             
         
         }
        
    }

        
    public static function message($data,$title,$level){
        
        $debug_data = array('data'=>$data,'title'=>$title,'level'=>$level);
        ApplicationEvents::fireEvent('debug_message',$debug_data);
        
         if(!self::$enabled){
            
             return;
            
         }
        
            
         if(count(self::$levels)>0){
            
             //Nao fazer debug deste nivel
             if(!in_array($level,self::$levels)){
             
                 return;
                 
             }
             
         }else{
            
             //Debug de todos niveis
               
         }
        
         
         
         $data_string = print_r($data,true);
         $css = self::getDebugCSS();
         $style = "<style>$css</style>";
        
        
         $html = "<div class='ydebug'><strong><h5>$title</h5></strong>".$data_string.'<br></div>'; 
        
        
         if(!isset(VCL::$preppend['debug_csss'])){
             
             VCL::$preppend['debug_css']=$style;   
             
         }
          
         VCL::preppendToNext($html);              
    
    }

    
    
     private static function getDebugCSS(){
    
            
        $css = 
        ".ydebug{
            width:auto;
            margin:0;
            padding:5px;
            color:#444444;
            background-color:#f1f1f1;
            border:1px solid #d4d4d4;
        }";
        return $css;
    
    }





}