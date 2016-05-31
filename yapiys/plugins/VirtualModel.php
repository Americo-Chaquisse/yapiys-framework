<?php

class VirtualModel {

    private $model = false;
    private $model_reflection = false;

    function __construct($modelName){

        $this->model = $modelName;
        $this->model_reflection =new ReflectionClass(ucfirst($modelName));

    }


    function __call($name, $arguments){


        if($this->model_reflection->hasMethod($name)){

            return call_user_func_array(array($this->model,$name),$arguments);

        }


    }

}