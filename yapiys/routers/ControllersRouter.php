<?php

/**
 * ControllersRouter
 * This is the parent class of the MVC and HMVC Routers.
 * This class defines the necessary interface to handle requests to a controller.
 */
abstract class ControllersRouter extends Router {


    public static function matchesRequest(){

        if(AppSwitch::getRequestRouter()){

            $router = AppSwitch::getRequestRouter();
            return $router instanceof HMVCRouter || $router instanceof MVCRouter;

        }


        return false;

    }

    /**
     * Decides if a url should be ignored
     * @param $url URL to be checked
     * @return bool Returns TRUE is the url gotta me ignored.
     */
    public function letItPass($url){
        
        return $url[0]==='@'||$url[0]==='$';
        
    }
    
    /**
     * Checks if a controller exists.
     * @param type $controllerName controllers name.
     * @param type $module controllers module.
     * @return bool Returns TRUE if the controller file exists and returns FALSE if not.
     */
    public function controllerExist($controllerName,$module=false){
        $filename=false;
           
        if ($module){
            
            $filename =APPLICATION_DIR.MODULES_DIR . '/'.$module.'/controllers/';
            
        }else{
            
            $filename =APPLICATION_DIR.ROOT_CONTROLLERS_DIR . '/';
            
        }

        $filename=$filename.$controllerName.'.php';
        
        if(file_exists($filename)){
            return $filename;
        }else{
            return FALSE;
        }
    }
    

      
    /**
     * Calls the function that corresponds to the controller action name.
     * @param type $model controller name
     * @param type $action controller action name.
     * @param type $params params passed to the action
     */
    protected function invokeControllerAction($model,$action,$params,$module=null,$access_level=-1){


        AppInstance::$output_view = $this->outputView();     

        $controllerName=  $this->generateControllerName($model);
                       

            if($this->controllerHasAction($controllerName, $action,$module)){
                              
                $rclass = new ReflectionClass($controllerName);
                $raction=$rclass->getMethod($action);
                

                
                if(!$raction->isStatic()&&!$raction->isPrivate()&&!$raction->isProtected()){


                    $controllerInstance = $this->getControllerInstance($model, $module, $action);
                  

                    $controllerInstance->accessLevel=$access_level;
                    

                    if(!$controllerInstance){
                        return FALSE;
                    }


                    $controllerInstance->setViewParams($params);





                    //Required params satisfied and count params is valid
                    if(count($params)>=$raction->getNumberOfRequiredParameters()&&count($params)<=$raction->getNumberOfParameters()){


                        $controllerInstance->beforeAction();


                        $raction->invokeArgs($controllerInstance,$params);


                        $controllerInstance->afterAction();




                    }else{



                        $url = $_GET['route_url'];
                        ApplicationEvents::fireEvent('invalid_action_params',$url);
                        AppInstance::getAccessManager()->http_notFound();
                        //throw new Exception('Invalid number of parameters passed to action.');

                    }


                }
                 
            
            }else{
                
                AppInstance::getAccessManager()->http_notFound();
            }
        
        }
        
        
        /**
         * Gets a fresh instance of a controller.
         * @param string $model controller name.
         * @param string $module module that contains the controller
         * @param string $action the action that is requesting the controller instance
         * @return boolean|AppController
         */
        private function getControllerInstance($model,$module,$action){
            
            $controllerName= $this->generateControllerName($model);
            
            if(class_exists($controllerName)){


                    $controllerInstance = new $controllerName();


                    if($module){
                        $controllerInstance->setModuleName($module);
                    }
                    

                    $controllerInstance->setCurrentDirectory($this->getCurrentDirectory($module));


                    $controllerInstance->setModelName($model);


                    $controllerInstance->setCurrentAction($action);
                    
                    
                    return $controllerInstance;
                    
                    
            }
            
            
            return FALSE;
        }
        

        public function getCurrentDirectory($module=null){
            if($module){
                return APPLICATION_DIR.MODULES_DIR.'/'.$module.'/';
            }else{
                return "";
            }
        }
    
    
    public function outputView(){

        $block = isset($_GET['server_block_view_output']);
        
        return !$block;
              
    }
    
     
    public function moduleExists($name){
        return (file_exists(APPLICATION_DIR.MODULES_DIR.'/'.$name));
    }
    
    public function generateControllerName($modelName){
        return ucfirst($modelName).('Controller');
    }
    
    public function urlHasExtension($url){
        $hasExtension=false;
        $exploded= explode('.',$url);
        if(count($exploded)>1){
            return true;   
        }
        
        return false;
        
    }
    
    
    public function processConditionedRoute($route,$contextable=true){
        

            if($contextable){

                $rconfig = loadConfiguration('routing');

                if(property_exists($rconfig,'no_context')){


                    $no_context_routes = $rconfig->no_context;


                        if(in_array($route,$no_context_routes)){
                            
                            //Do not validate context
                            return false;
                                    
                        }
                          
                                
                        //Validate context
                        return true;
                                
                                
                        }else{
                                
                            //Validate context
                            return true;
                                
                        }
                    
                    
                }else{
                
                    return false;    
                
                }
                        
        
    }
    
    
                              
     
    /**
     * Checks if a controller has an action by the name.
     * @param type $controllerName controller name.
     * @param type $actionName controller action to check
     */
    public function controllerHasAction($controllerName,$actionName,$module=false){
        $controllerFilename=  $this->controllerExist($controllerName,$module);
                       
        if($controllerFilename){            
            require_once $controllerFilename;            
        }else{
            return FALSE;
        }
        
        return method_exists($controllerName, $actionName);
    }
}
