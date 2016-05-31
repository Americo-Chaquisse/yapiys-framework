<?php

class ErrorHandler {
    
    
    
    private static function getErrorsCSS(){
    
            
        $css = 
        ".yerror{
            width:auto;
            margin:0;
            padding:5px;
            color:#444444;
            background-color:#f1f1f1;
            border:1px solid #d4d4d4;
        }";
        return $css;
    
    }
    
    private static function emitErrors(){

        $app = loadConfiguration('app');
        $emit_errors = true; //Erros are emited by default
        
        if(property_exists($app,'display_errors')){
        
            $emit_errors=$app->display_errors;

            return $emit_errors;
            
            
        }

        return $emit_errors;


    }
    
    public static function error_occured($level,$message,$file,$line,$context){
    
        
        $error_data = array('level'=>$level,'message'=>$message,
                            'file'=>$file,
                            'line'=>$line,
                            'context'=>$context);
        
        
        //Publica o evento ERRO da aplicacao
        ApplicationEvents::fireEvent('error_ocurred',$error_data);
       
		
	
        //Aplicacao offline
        if(self::emitErrors()){
            
            $css = self::getErrorsCSS();
            $style = "<style>$css</style>";
            $type = 'Error';
            
            if($level==2||$level==512){
                
                $type = 'Warning';
                
            }else if($level==8||$level==1024){
            
                $type = 'Notice';
                
                
            }else if($level==256){
            
            
                $type = 'Error';
                
                
            }else if($level==4096){
                
                $type = '::Exception';
                
            }
        
            
            $html = "<div class='yerror'><b><h3>$type</h3></b>".$message.'<br>'.
            ' in <b>'.$file.'</b> : <b>'.$line.'</b><br><br><b></b><br>'.
            ''.
            '</div>'; 
            
            if($level!=256){
                
                if(!isset(VCL::$preppend['error_style'])){
                    
                    VCL::$preppend['error_style']=$style;
                
                }
                VCL::preppendToNext($html);   
                
            }else{
                
                   
                VCL::direct_output($style.$html,true);
                exit();
                
            }
            
            
            
            
            
        }else{
            //Aplicacao online
            
            
            //Erro fatal
            if($level==256){
            
                 //Ocorreu um erro [500] durante o request    
                AppInstance::getAccessManager()->http_internalError($url);
                
                
            }
            
            
        }
        
        
        
    
    }
    
    public static function exception_thrown($e,$url){
    
        $exception_data = array('instance'=>$e,'url'=>$url);
        
        //Publica o evento ERRO da aplicacao
        ApplicationEvents::fireEvent('exception_thrown',$exception_data);
        
        
        //Aplicacao esta offline
        if(self::emitErrors()){
        
            
            $css = self::getErrorsCSS();
            $style = "<style>$css</style>";
        
            
            $html = "<div class='yerror'><b><h3>Exception ".''."</h3></b>".$e->getMessage().'<br>'.
            ' in <b>'.$e->getFile().'</b> : <b>'.$e->getLine().'</b><br><br><b>Stack Trace</b><br>'.
            $e->getTraceAsString().    
            '</div>';  


            VCL::direct_output($style.$html,true);
            
        
        }else{
            //Aplicacao online
             
            //Ocorreu um erro [500] durante o request    
            AppInstance::getAccessManager()->http_internalError($url);  
            
            
            
        }
        
        exit();
    
    }
    
    
    
    
}