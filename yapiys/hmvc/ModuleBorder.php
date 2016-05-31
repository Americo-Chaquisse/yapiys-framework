<?php

abstract class ModuleBorder {

    private $moduleName = false;
    private $loadAllModels = false;
    private $loadAllLibs = false;

    private $alwaysRequireLibs = []; //load only this libraries
    private $alwaysRequireModels = []; //load only this models

    public static $dependencies = [];//Depends on this modules. If they are not present. Ii will not be available

    private $caller = false; //Who called this module border.
    private $virtual_models = [];


    private function getVirtualModel($name){

        if(isset($this->virtual_models[$name])){

            return $this->virtual_models[$name];

        }else{

            $virtualModel = new VirtualModel($name);
            $this->virtual_models[$name] = $virtualModel;

            return $virtualModel;


        }

    }

    function __get($key){


        if(class_exists(ucfirst($key),false)){

            return $this->getVirtualModel($key);

        }else{

            if($this->requireModel($key)){

                return $this->getVirtualModel($key);

            }

        }


    }


    /**
     * Checks if the dependecies of a module where satsfied.
     * @param $module The module to verify
     */
    public static function dependenciesSatisfied($module){

        //The module exists
        if(self::isModuleAvailable($module)){

            $moduleBorderClass = self::generateModuleBorderClassName($module);

            if(class_exists($moduleBorderClass,false)){


                $dependencies = $moduleBorderClass::$dependencies;

                $satsfactions = 0;

                //Dependencies property is an array
                if(is_array($dependencies)){

                    foreach($dependencies as $dependency){


                        //Modulo disponivel
                        if(self::isModuleAvailable($dependency)){

                            $satsfactions++;

                        }


                    }


                }

                return(count($dependencies)==$satsfactions);

            }

            return true;


        }


    }

    /**
     * Generates the module Border class name.
     * @param $moduleName
     */
    public static function generateModuleBorderClassName($moduleName){

        return ucfirst($moduleName).'Border';

    }

    /**
     * Checks if a module Border File exists
     * @param $moduleName module to verify
     */
    public static function isModuleAvailable($moduleName){

        $module_path = APPLICATION_DIR.MODULES_DIR.'/'.$moduleName.'/border/';


        //The module directory exists
        if(file_exists($module_path)){

            if(file_exists($module_path.'Border.php')){

                return $module_path.'Border.php';

            }


        }

        return false;

    }


    /**
     * Gets an instance of the specified module name.
     * @param $name the module name.
     * @param bool $caller the name of the entity/module that called this module.
     * @return bool
     */
    public static function getModule($name,$caller=false){

        //Module is available
        $module_available = self::isModuleAvailable($name);
        $module_class_name = self::generateModuleBorderClassName($name);

        //Module was already loaded
        if(class_exists($module_class_name)){

            return new $module_class_name($name, $caller);

        }

        if($module_available) {

            require_once $module_available;

            if (class_exists($module_class_name, false)) {

                $module_instance = new $module_class_name($name, $caller);

                return $module_instance;

            }

        }

        return false;

    }

    /**
     * @param $name The name of this Module
     * @param $caller The module who called this.
     */
    function __construct($name,$caller){

        $this->moduleName = $name;
        $this->caller = $caller;
        $this->configure($caller);

    }


    /**
     * This function shall be overridden by each module Border
     * @param $caller The name of the module that is requiring this
     */
    public function configure($caller){



    }

    public function prepare(){

        //Load all libs if necessary
        if($this->loadAllLibs){

            $this->doLoadAllLibs();


        }else{


            if(count($this->alwaysRequireLibs)>0){

                foreach($this->alwaysRequireLibs as $toLoadLib){

                    $this->requireLib($toLoadLib);

                }

            }

        }

        //Load all models if necessary
        if($this->loadAllModels){

             loadModels($this->moduleName);

        }else{

            if(count($this->alwaysRequireModels)){

                foreach($this->alwaysRequireModels as $toLoadModel){

                    $this->requireModel($toLoadModel);

                }

            }

        }

    }

    private function doLoadAllLibs(){

        $libs_directory = APPLICATION_DIR.MODULES_DIR.'/'.$this->moduleName.LIBRARIES_DIR;

        //The libraries directory exists
        if(file_exists($libs_directory)){

            $libs_directory_contents = scandir($libs_directory);

            foreach($libs_directory_contents as $libs_directory_content){

                if($libs_directory_content!='.'&&$libs_directory_content!='..'){

                    $libs_directory_subdirectory = $libs_directory.'/'.$libs_directory_content;

                    //It is a lib directory
                    if(is_dir($libs_directory_subdirectory)){

                        //Load the library
                        loadLib($libs_directory_content,$this->moduleName);

                    }

                }

            }


        }

    }


    /**
     * Reads a model from this module directory.
     * @param $name
     */
    public function requireModel($name){


       //Model already loaded
       if(class_exists(ucfirst($name),false)){

           return true;

       }

       //models directory
       $models_directory = APPLICATION_DIR.MODULES_DIR.'/'.$this->moduleName.'/'.MODELS_DIR;

       //Models directory exists for this module
       if(file_exists($models_directory)){

            $model_filename = $models_directory.'/'.ucfirst($name).'.php';

            //Model file exists
            if(file_exists($model_filename)){

                require_once $model_filename;

                return true;

            }


       }

        return false;

    }

    /**
     * Reads a library from this module directory
     * @param $name
     */
    public function requireLib($name){

        $libs_directory = APPLICATION_DIR.MODULES_DIR.'/'.$this->moduleName.'/'.LIBRARIES_DIR;

        //The libraries directory exists
        if(file_exists($libs_directory)){


            $lib_directory = $libs_directory."/".strtolower($name).'/';

            //Directorio de lib existe
            if(file_exists($lib_directory)){

                return loadLib($name,$this->moduleName);

            }


        }

        return false;

    }

    /**
     * Loads an array of libs.
     * @param $libs The libs names array.
     */
    public function requireLibs($libs){

        if(is_array($libs)){

           foreach($libs as $lib){

               $this->requireLib($lib);

           }

        }

    }

    /**
     * Loads an array of models
     * @param $models The models names array.
     */
    public function requireModels($models){

        if(is_array($models)){

            foreach($models as $model){

                $this->requireModel($model);

            }

        }


    }


}