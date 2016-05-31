<?php
/**
 * Description of Framework
 *
 * @author Mario Junior
 */
class Framework {
    
    private static $precedences = [];

    private static $loadedLibraries = [];
    
    public static function setBaseClassLoadPrecedence($toLoad,$precedence){
        
        if(!($toLoad&&$precedence)){
            
            return;
            
        }        
        
        $prs = array();
        
        
        if(is_array($precedence)){
                       
            $prs = $precedence;
            
        }else{
            
            $prs = array($precedence);                    
            
        }
        
        self::$precedences[$toLoad] = $prs;
        
    }
    
    
    public static function loadBaseClass($name){
        
        if(isset(self::$precedences[$name])){
            
           
            $precedences = self::$precedences[$name];
            
            foreach ($precedences as $presedence_Class) {
                
                if(!class_exists($presedence_Class)){
                                        
                    self::loadBaseClass($presedence_Class);
                    
                }else{
                    
                    continue;
                }
                
            }
            
            unset(self::$precedences[$name]);
            self::loadBaseClass($name);
                        
        }else{
            
            if(!class_exists($name)){
                        
          
                $base_classes_file = APPLICATION_DIR.BASE_CLASSES_DIR.'/'.$name.'.php';
                            
                if(file_exists($base_classes_file)){
                    
                    include_once $base_classes_file;
                    
                }
           
                
            } 
        }
        
    }
    
    public static function loadBaseClasses(){
        
        $base_classes_dir = APPLICATION_DIR.BASE_CLASSES_DIR.'/';
        
        //Exise directorio de classes Base
        if(file_exists($base_classes_dir)){
            
            if(is_dir($base_classes_dir)){
                
                
                $files = scandir($base_classes_dir);
                
                foreach ($files as $file) {
                    
                    if($file!=='.'&&$file!=='..'){
                        
                        $file_full_path = $base_classes_dir.$file;
                        
                        //Classe base
                        $class_name = pathinfo($file_full_path,PATHINFO_FILENAME);
                        
                        
                        /*
                        //Classe ainda nao foi carregada
                        if(!class_exists($class_name)){
                            
                            //Carrega o ficheiro
                            include_once $file_full_path;
                            
                            
                        }*;
                         * 
                         * 
                         */
                        
                        self::loadBaseClass($class_name);
                        
                    }
                    
                }
                
                
            }
            
            
        }
        
        
    }
    
    //put your code here
    
    public static function loadLibrary($name,$force=false){

        //Dont load it again
        if(isset(self::$loadedLibraries[$name])){

            return false;

        }

        //Path da biblioteca
        $library_dir = FRAMEWORK_ROOT_DIR.FRAMEWORK_LIBRARIES_DIRECTORY.'/'.$name;
        
        //Nao carregar a library
        if(file_exists($library_dir.'/skip.ignore')&&!$force){
            
            return false;
            
        }
        
        
        $library_loader = $library_dir.'/loader.php';
        $virtual_path = $library_dir.'/app';
        
        if(file_exists($library_dir)&&  file_exists($library_loader)){
            
            require_once $library_loader;
            
        }
        
        //Virtualizar o directorio app da library
        if(file_exists($virtual_path)){
            
            VirtualApp::virtualizeAll($virtual_path);
            
        }

        $virtual_yapiys = $library_dir.'/yapiys';

        if(file_exists($virtual_yapiys)){

            VirtualYapiys::virtualizeAll($virtual_yapiys);


        }

      
    }
    
    
    public static function loadAllLibraries(){
        
        $libraries = scandir(FRAMEWORK_ROOT_DIR.FRAMEWORK_LIBRARIES_DIRECTORY);
        
        foreach ($libraries as $lib_name) {
            
            if($lib_name!='.'&&$lib_name!='..'){
                
                self::loadLibrary($lib_name);
                
            }
            
        }
        
    }
    
    
    
}
