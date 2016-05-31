<?php

/**
 * Class MVCRouter
 *
 * This router is responsible for handling Model-View-Controller routes : controller/action
 */
class MVCRouter extends ControllersRouter {
    
    
    public function route($array) {
        self::loadAppModels();
        

        $model=$array['model'];
        $action=$array['action'];
        $params=$array['params'];
        
        $access_level=0;
        
        if(isset($array['access_level'])){
            
            $access_level = $array['access_level'];
            
        }
        
        
        
        AppInstance::setCurrentHmvcPath(false,$model,$action);
        AppInstance::$route_module=false;
        AppInstance::$route_model=$model;
        AppInstance::$route_action=$action; 
        AppInstance::$route_params=$params;
        

        $this->invokeControllerAction($model, $action, $params,false,$access_level);
                
    }
    

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

                $model = false;

                $action = false;
                
                if(count($url_parts)<2){
                    return false;   
                }

                //Two parts URL
                if(count($url_parts)==2){

                    //Context in url
                    $contexto = $url_parts[0];



                    //It is not an context. It is an API request
                    if(strtolower($contexto)==='api'){

                        return false;

                    }

                    $model = $url_parts[1];

                    $action = "index";
                    
                }else{ //More than two parts in url
                
                //Context in URL
                $contexto = $url_parts[0];

                $model = $url_parts[1];

                $action = $url_parts[2];
                
                }
                
                
                //It is the App Model. THIS ONE CANT BE ROUTED.
                if(strtolower($model)==='app'){
                    
                    return false;
                    
                }
                

                $controller_name = $this->generateControllerName($model);
                

                if($this->controllerExist($controller_name)){
                    

                    if($this->controllerHasAction($controller_name,$action)){
                        
                                          
                        $params = array();
                        
                        //Foram especificados parametros
                        if(count($url_parts)>3){

                            $total_params = count($url_parts)-3;
                            $params = array_slice($url_parts,3,$total_params);

                        }

                        
                        $condition = function($route){                                    
            
                           //Processa a rota condicionada                    
                          return $this->processConditionedRoute($route);
                            
                            
                        };          
                    
                        
                        $path = $model.'/'.$action;
                        
                        $the_route =  array(
                                     'url'=>$path,
                                      'model'=>$model,
                                      'action'=>$action,
                                      'params'=>$params,
                                      'validate_context_if'=>array('condition'=>$condition,'param'=>$path),
                                      'context'=>$contexto);
                        
                        
                        if($this->block_connection($path)){
                            
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
                
                
                
            }else{//Contexts disabled


                $contexto = false;

                $model = false;

                $action = false;
                
                if(count($url_parts)<1){
                    return false;   
                }
                
                if(count($url_parts)==1){

                    $model = $url_parts[0];

                    $action = "index";
                    
                }else if(count($url_parts)>=2){

                    $model = $url_parts[0];

                    $action = $url_parts[1];
                
                }
                
                
                //deny TO ROUTE THE APP MODEL
                if(strtolower($model)==='app'){
                    
                    return false;
                    
                }

                $controller_name = $this->generateControllerName($model);
                
                

                if($this->controllerExist($controller_name)){
                    
                    
                    //Accao existe
                    if($this->controllerHasAction($controller_name,$action)){
                        
                                          
                        $params = array();
                        

                        if(count($url_parts)>2){
                            $total_params = count($url_parts)-2;
                            $params = array_slice($url_parts,2,$total_params);
                        }

                    
                        
                        $path = $model.'/'.$action;
                        
                        $the_route =  array(
                                     'url'=>$path,
                                      'model'=>$model,
                                      'action'=>$action,
                                      'params'=>$params);
                        
                        
                        if($this->block_connection($path)){
                            
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
                
                
                
            }
    }
    

    
    
}