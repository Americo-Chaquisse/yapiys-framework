<?php


/**
 * Description of MarioShell
 *
 * @author Mario Junior
 */
class GenerateShell extends AppExtendedShellCommand{
    
        
    private function create($name){
        
        $path = APPLICATION_DIR.MODULES_DIR.'/'.$name;            
        $controllers = APPLICATION_DIR.MODULES_DIR.'/'.$name."/controllers";
        $models = APPLICATION_DIR.MODULES_DIR.'/'.$name."/models";
        $views = APPLICATION_DIR.MODULES_DIR.'/'.$name."/views";
        $apis = APPLICATION_DIR.MODULES_DIR.'/'.$name."/API";
        $commands = APPLICATION_DIR.MODULES_DIR.'/'.$name."/commands";
        $partials = APPLICATION_DIR.MODULES_DIR.'/'.$name."/partials";
        $partials_controllers = APPLICATION_DIR.MODULES_DIR.'/'.$name."/partials/controllers";
               
        mkdir($path);            
        mkdir($controllers);
        mkdir($commands);
        mkdir($apis);
        mkdir($models);
        mkdir($views);
        mkdir($partials);
        mkdir($partials_controllers);
        
        
    }
 
    
    
    public function view($module,$name){
        
        $path = APPLICATION_DIR.MODULES_DIR.'/'.$module.'/views';
        $view_html = $path."/$name.html";
        $view_javascript = $path."/$name.html";
        
        
        
    }
    
    
    public function action($module,$name,$view_filaname=false){
        
        $path = APPLICATION_DIR.MODULES_DIR.'/'.$module.'/views';
        $view_html = $path."/$name.html";
        $view_javascript = $path."/$name.html";
        
        
        
    }
    
  
    
    public function module($name,$option=false){
        
        $path = APPLICATION_DIR.MODULES_DIR.'/'.$name;
                        
        
        if(file_exists($path)){
            
            //Nao substituir
            if($option!='-r'){
                
                 echo "Module already exists use -r option";
                
            }else{
                
                //Remover directorio
                rmdir($path);
                                
                //Criar o modulo
                $this->create($name);                
                
                echo "Module $name was re-created successfully!";
                
            }
           
            
        }else{
            
            $this->create($name);                       
            echo "Module $name was created successfully!";
        }
      
        
    }
    
}
