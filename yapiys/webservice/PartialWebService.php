<?php

class PartialWebService extends AppExtendedWebService {
    public $module = false;
    public $partial = false;
    private $virtualModels = [];

    function __get($name){

        //Returns the partial module
        if(strtolower($name)=='module'){

            if($this->module){

                return requireModule($this->module);

            }


        }

        $modelName = ucfirst($name);

        //Virtual model already loaded
        if(isset($this->virtualModels[$modelName])){

            return $this->virtualModels[$modelName];

        }else{//Load the model and virtualize it

            $model_filename = false;

            $model_filename_module = false;

            if($this->module){


                $model_filename = APPLICATION_DIR.MODULES_DIR.'/'.$this->module.'/'.PARTIALS_DIRECTORY.'/'.$this->partial.'/models/'.$modelName.'.php';

                $model_filename_module = APPLICATION_DIR.MODULES_DIR.'/'.$this->module.'/models/'.$modelName.'.php';



            }else{

                $model_filename = APPLICATION_DIR.PARTIALS_DIRECTORY.'/'.$this->partial.'/models/'.$modelName.'.php';

                $model_filename_module = APPLICATION_DIR.MODELS_DIR.'/'.$modelName.'.php';


            }



            //Model found
            if(file_exists($model_filename)){

                require_once $model_filename;


            //Model is in module folder
            }else if(file_exists($model_filename_module)){

                require_once $model_filename_module;


            }else{

                return false;

            }

            $virtualModel = new VirtualModel($modelName);
            $this->virtualModels[$modelName] = $virtualModel;
            return $virtualModel;


        }

        return false;

    }




}