<?php

/**
 * Description of VirtualApp
 *
 * @author Mario Junior
 */
class VirtualApp {
    
    static $virtual_subdirectories = array();
    
    
    
    public static function initialize(){

        //Subdirectorios virtuais da pasta app
        self::$virtual_subdirectories[COMMANDS_DIRECTORY] =array();
        self::$virtual_subdirectories[AUTOMATIONS_DIRECTORY]=array();
        self::$virtual_subdirectories[LIBRARIES_DIRECTORY]= array();
                
    }
    
    public static function virtualize($virtual_subdir,$dir){
        
        $files = scandir($dir);
        $virtual_subdirectory_files = array();
        
        
        if(isset(self::$virtual_subdirectories[$virtual_subdir])){
           
            $virtual_subdirectory_files=self::$virtual_subdirectories[$virtual_subdir];
            
        }
        
        foreach ($files as $file) {
            
            if($file!='.'&&$file!='..'){
                
                $file_path = $dir.'/'.$file; 
                //echo $file_path.' : '.$file.' <br>';
                
                $virtual_subdirectory_files[$file]=$file_path;
                
                
            }
            
            
        }
        
        self::$virtual_subdirectories[$virtual_subdir] = $virtual_subdirectory_files;
        
    }
    
    public static function virtualizeAll($path){
        
        $files_and_dirs = scandir($path);
        
        
        foreach($files_and_dirs as $file){
            
            if($file!='.'&&$file!='..'){
                
            }else{
                
                continue;
                
            }
            
            $file_full_path = $path.'/'.$file;
            
            
            if(is_dir($file_full_path)){
            
                
                //Directorio virtualizavel
                if(isset(self::$virtual_subdirectories[$file])){
            
                    
                    //Virtualiza o directorio
                    self::virtualize($file, $file_full_path);
                    
                        
                }          
            
                
            }
            
            
        }
        
        
    }
    
    
        
    public static function scandir($path){
        
        $existing_files = scandir(APPLICATION_DIR.$path);
        $virtual_files = array();
        
        
        if(isset(self::$virtual_subdirectories[$path])){
            
            $virtual_files = self::$virtual_subdirectories[$path];
            
        }
        
        return array_merge($existing_files, $virtual_files);
        
    }
    
    
    public static function file_exists($path){
       
        $existing_file_path = APPLICATION_DIR.$path;

        
        if(file_exists($existing_file_path)){
            
            return $existing_file_path;
                        
        }
        
        $parts = explode('/', $path);       
        $subdir = $parts[0];
        unset($parts[0]);      
      
        $filename = implode('/',$parts);
     
        
        //Directorio vitual existe
        if(isset(self::$virtual_subdirectories[$subdir])){



            $subdir_virtual_files_list = self::$virtual_subdirectories[$subdir];

            
            //Ficheiro existe na lista de ficheiros virtuais
            if(isset($subdir_virtual_files_list[$filename])){
                
                
                //Obtem o path real do ficheiro
                $real_file_path = $subdir_virtual_files_list[$filename];
                                
                return $real_file_path;
                
            }
            
            
            return false;
            
                        
        }       
        
    }
    
    public static function full_path($file){
        
        return self::file_exists($file);
        
    }
    
    
    
    
}