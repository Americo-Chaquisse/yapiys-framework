<?php
/**
 * Class HMVCRouter
 *
 * This router is responsible for handling Hierarquical-Model-View-Controller requests.
 * URLS in format : 'module/controller/action'.
 */
class HMVCRouter extends ControllersRouter{
    const module_access_session_variable_name="module_access";
   
    
    
    public function routeExists($contextable,$url){
          if($this->urlHasExtension($url)||$this->letItPass($url)){
              
              return false;   
            
          }
        
        
        $url_parts = explode('/',$url);

        if($url_parts[0]==='API'){

                return false;

        }
        

            
            //Contexts enabled
            if($contextable){
                
                
                
                $contexto = false;

                $module = false;

                $model = false;                

                $action = false;               
                
                
                
                if(count($url_parts)<3){
                    return false;   
                }
                
                
                if(count($url_parts)==3){
                    
                    $contexto = $url_parts[0];


                    //it is an API. Forget it.
                    if(strtolower($contexto)==='api'){
                        
                        return false;

                    }

                    $module = $url_parts[1];

                    $model = $url_parts[2];                

                    $action = "index";                
                                     
                 }else if(count($url_parts)>3){

                    $contexto = $url_parts[0];

                    $module = $url_parts[1];

                    $model = $url_parts[2];                

                    $action = $url_parts[3];                
                
                 }
                     

                $controller_name = $this->generateControllerName($model);
                

                if($this->controllerExist($controller_name,$module)){        


                    if($this->controllerHasAction($controller_name,$action,$module)){
                      
                        $params = array();

                        if(count($url_parts)>3){
                            $total_params = count($url_parts)-4;
                            $params = array_slice($url_parts,4,$total_params);
                        }
                        
                        
                        $condition = function($route){                                    

                           return $this->processConditionedRoute($route);
                            
                            
                        };          
                    
                        
                    
                        $route_path = $module.'/'.$model.'/'.$action;
                      
                        
                        $the_route = array('url'=>$route_path,
                                        'module'=>$module,
                                       'model'=>$model,
                                      'action'=>$action,
                                      'params'=>$params,                                                        'validate_context_if'=>array('condition'=>$condition,'param'=>$route_path),
                          'context'=>$contexto);
                 
                        
                        if($this->block_connection($route_path)){
                            
                              $the_route['block_connection']=true;                            
                            
                        }

                        return $the_route;
                                                
                            
                    }else{
                        
                        //Action not found
                        return false;
                    }
                    
                    
                }else{
                    //Controller not found
                    return false;
                }
                
                
                
            }else{
                
                //Contexts disabled
                
                $contexto = false;

                $module = false;

                $model = false;                

                $action = false;               
                
                
                
                if(count($url_parts)<2){
                    return false;   
                }
                

                if(count($url_parts)>=3){
                    

                    $module = $url_parts[0];

                    $model = $url_parts[1];

                    $action = $url_parts[2]; 
                    
                 // module/controller/index
                 }else if(count($url_parts)==2){

                    $module = $url_parts[0];

                    $model = $url_parts[1];                

                    $action = "index"; 
                    
                                     
                 }
                

                $controller_name = $this->generateControllerName($model);

                if($this->controllerExist($controller_name,$module)){        
                    

                    if($this->controllerHasAction($controller_name,$action,$module)){
                      
                        $params = array();

                        if(count($url_parts)>3){
                            $total_params = count($url_parts)-3;
                            $params = array_slice($url_parts,3,$total_params);
                        }

                        
                    
                        $route_path = $module.'/'.$model.'/'.$action;
                      
                        
                        $the_route = array('url'=>$route_path,
                                        'module'=>$module,
                                       'model'=>$model,
                                      'action'=>$action,
                                      'params'=>$params);
                 
                        
                        if($this->block_connection($route_path)){
                            
                              $the_route['block_connection']=true;                            
                            
                        }
                        

                        return $the_route;
                                                
                            
                    }else{
                        
                        //ACTION NOT FOUND
                        return false;
                    }
                    
                    
                }else{

                    //CONTROLLER NOT FOUND
                    return false;
                }
                
                
                
                
            }
    }
   
    public function route($array) {

        self::loadModuleModels($array['module']);
        self::loadAppModels();

        $model=$array['model'];
        $action=$array['action'];
        $module=$array['module'];
        $params_array=$array['params'];
        
        if(!isset($array['access_level'])){ $array['access_level']=0; }
        $access_level = $array['access_level'];

        
        AppInstance::setCurrentHmvcPath($module,$model, $action);
        AppInstance::$route_module=$module;
        AppInstance::$route_model=$model;
        AppInstance::$route_action=$action;            

        AppInstance::$route_params=$params_array;

        $this->invokeControllerAction($model, $action,$params_array,$module,$access_level);       
                
    }


    public function blockModuleAccess($name){
         $bundle= $this->getAccessManager()->getBundle();
         $bundle->updateArrayValue(self::module_access_session_variable_name, FALSE, $name);
    }
    
    public function allowModuleAccess($name){
        $bundle= $this->getAccessManager()->getBundle();
        $bundle->updateArrayValue(self::module_access_session_variable_name, $name, TRUE);
    }
    
    
     
    public function isModuleAccessible($name){
        $bundle= $this->getAccessManager()->getBundle();
        $value=$bundle->getArrayValue(self::module_access_session_variable_name, $name);

        if(is_null($value)){            
            return TRUE;
        }
   
        return $value;       
    }

}
