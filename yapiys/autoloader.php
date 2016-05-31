<?php
//Autoloader configuration
$loaderConfig=null;

/**
 * The directories that contains classes are specified in the configurarion file.
 * This autoload functions checks if in any of that folders exists a file
 * class_name.php and requires such file if exists.
 *
 * Some directories have the property [app], what means that they are application directories
 * and not Yapiys folders directories as expected.
 *
 */


loadConfig();
//Registers the autoload function
spl_autoload_register(function($class){  
    global $loaderConfig;
    if($loaderConfig){
        $dirs= $loaderConfig->directories;
        
            foreach ($dirs as $dir) {

                  $file_path_lc_antept = false;
                  $file_path_uc_antept = false;

                  //Application directory
                  if(property_exists($dir,'app')){


                      $file_path_lc_antept=dirname(__DIR__).'/app/'.$dir->path.lcfirst($class).'.php';
                      $file_path_uc_antept=dirname(__DIR__).'/app/'.$dir->path.ucfirst($class).'.php';


                  }else{
                      //Yapiys directory

                      $file_path_lc_antept=__DIR__.'/'.$dir->path.lcfirst($class).'.php';
                      $file_path_uc_antept=__DIR__.'/'.$dir->path.ucfirst($class).'.php';

                  }


                  if(file_exists($file_path_lc_antept)){
                      require_once $file_path_lc_antept;
                      return;
                  }
                  
                  if(file_exists($file_path_uc_antept)){
                      require_once $file_path_uc_antept;
                      return;
                  }
            }
    
    }
    
});


/**
 * Loads the JSON configuration file with the
 * folders to use to autoload classes
 */
function loadConfig(){
    global $loaderConfig;
    $filename='loader.json';
    if(file_exists($filename)){
        $loaderConfig= json_decode(file_get_contents($filename));
    }
}

