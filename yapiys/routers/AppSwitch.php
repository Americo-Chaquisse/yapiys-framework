<?php

class AppSwitch{
    private static $routers = array();
    public static $contextable=false; //Contexts disabled by default
    private static $url = false;
    public static $current_url = false;
    private static $requestRouter = false;


    public static function getRequestRouter(){

        return self::$requestRouter;

    }
    
    public static function setContextable($value){
            self::$contextable=$value;
    }    
    
    public static function registerRouter($router){
       self::$routers[]=$router;        
    }


    public static function getRouter($class_name){

        foreach(self::$routers as $router){

            if(is_a($router,$class_name)){

                return $router;

            }

        }

        return false;

    }


    public static function defaultRoute(){
        $defaultController = getDefaultController();
        self::newRequest($defaultController);
    }
    
      
    public static function newRequest($url){
        
        try{
        
        if($url===''){
            self::defaultRoute();
            return;
        }
        
        self::$url=$url;
        
        //Aplicacao com contexto
        if(self::$contextable){
        
        //Coloca o contexto na URL
        $url = str_replace('@context',AppInstance::getContext(),$url);

        self::$current_url = $url;

        $route_found=false;
        foreach(self::$routers as $router){
            
            $route_params = $router->routeExists(self::$contextable,$url);
            
        
            //Parametros de roteamento validos
            if($route_params){
                $route_found=true;
                
                AppInstance::$request = $route_params;  
    
                //Nao bloqueou a conexao
                if(!isset($route_params['block_connection'])){
                    

                        //Inicializa as conexoes
                        AppData::initialize();
                
                    
                }else{
                  
                    //Nao inicializa as conexoes
                    
                }
                
                
                //Roteador exige validacao de contexto
                if(isset($route_params['validate_context'])){
                    
                    //Pega o contexto
                    $context = $route_params['context'];
                    
                    self::proceedRouting($router,$route_params,$context);
                    
             
                //Roteador pediu validar contexto se uma condicao for verdadeira    
                }else if(isset($route_params['validate_context_if'])){
                    
                     
                    
                     $context = $route_params['context'];
                        
                     //Pega os dados da condicao de roteamento
                     $route_condition_data = $route_params['validate_context_if'];  
                     
                     //Condicao para verificar contexto
                     $condition = $route_condition_data['condition'];                  
                    
                     //parametro da condicao
                     $params = $route_condition_data['param'];
                    
                     
                     //Verificacao
                     $condition_satsfied = call_user_func_array($condition,array($params));
                
                        
                     //var_dump($condition_satsfied);
                     //exit();
                    
                    
                     //A condicao foi satsfeita
                     if($condition_satsfied){
                         
                        //Validar contexto
                          self::proceedRouting($router,$route_params,$context);
                         
                                                  
                     }else{//A condicao nao foi satsfeita
                         
                         
                         //Nao validar contexto
                         self::proceedRouting($router,$route_params);
                         
                     }
                          
                    
                }else{ 
                    
                      self::proceedRouting($router,$route_params);
                    
                }
             
                
                break;                
                exit();
            }
            
        }
        
        if(!$route_found){
            
           AppInstance::getAccessManager()->http_notFound(self::$url);
            
        }
            
        }else{//Aplicacao com contextos desligados
            
            self::$current_url = $url;

            $route_found=false;
            
            foreach(self::$routers as $router){
            
                $route_params = $router->routeExists(self::$contextable,$url);

                //Parametros de roteamento validos
                if($route_params){
                    $route_found=true;

                    //Nao bloqueou a conexao
                    if(!isset($route_params['block_connection'])){


                            //Inicializa as conexoes
                            AppData::initialize();


                    }else{

                        //Nao inicializa as conexoes

                    }



                    self::proceedRouting($router,$route_params);
                    break;                
                    exit();
                    
                }
            
            }   
        
            
            if(!$route_found){

               AppInstance::getAccessManager()->http_notFound(self::$url);

            }
            
            
        }
           
        
        //Foi lancada uma excepcao
        }catch(Exception $e){
            
           $router->onError($e);
           
           //Notifica o interceptador de erros
           ErrorHandler::exception_thrown($e,$url);
            
        }
        
    }
    
    
    //Valida o contexto
    public static function isValidContext($context){  
        
        return AppInstance::contextMatches($context);
        
    }
    
    public static function proceedRouting($router,$route_params,$context=false){

        self::$requestRouter = $router;

        if($context){//Validar contexto
            
            
            //Dispara o evento [beforeContextValidation]
            $url = $route_params['url'];
            ApplicationEvents::fireEvent('beforeContextValidation',$url);
            
            //Contexto valido
            if(self::isValidContext($context)){                        
                    
                    $module=false;
                    
                    if(isset($route_params['module'])){
                        $module=$route_params['module'];
                    }
                
                    $model=false;
                
                    if(isset($route_params['model'])){
                        $model=$route_params['model'];
                    }
                    
                    $action=false;
                    
                    if(isset($route_params['action'])){
                        $action=$route_params['action'];
                    }
            
                    //Rota HMVC ou MVC
                    if($model&&$action){
                        
                //Usuario tem acesso a esta rota
                if(AppUser::isAllowed($module,$model,$action)){
                             
                    $access_level = 
                    AppInstance::getAccessManager()->getUserAccessLevel($module,$model,$action);
                             
                             $route_params['access_level']=$access_level;
                             
                             
                            //Dispara o evento [antes de invocar a action]
                            $url = $route_params['url'];
                            ApplicationEvents::fireEvent('beforeAction',$url);
                             
                             //Roteador executa o roteamento
                             $router->route($route_params);
                             
                         }else{
							 
							 
                            //Usuario nao tem acesso
                            AppInstance::getAccessManager()->http_accessForbidden(self::$url);
                             
                         }
                        
                               
                    }else{
                        
          
                             
                        //Outro tipo de rota
                        if(isset($route_params['require_access_level'])){
                            
                             $route = $route_params['require_access_level'];
                             $access_level = AppInstance::getAccessManager()->getUserAccessLevel($route);
                             $route_params['access_level']=$access_level;
                        }
                        
                        //Dispara o evento [antes de invocar a action]
                        $url = $route_params['url'];
                        ApplicationEvents::fireEvent('beforeAction',$url);
                        $router->route($route_params);
                        
                    }
                     
            }else{ 
                    //Contexto invalido                        
                    AppInstance::getAccessManager()->http_accessForbidden(self::$url); 
                        
            }
               
            
            
        }else{
            //Nao validar contexto
            
             if(isset($route_params['url'])){
                
                //Dispara o evento [antes de invocar a action]
                $url = $route_params['url'];
                ApplicationEvents::fireEvent('beforeAction',$url);
                 
                 
             }
            
        
              
                    $module=false;
                    
                    if(isset($route_params['module'])){
                        $module=$route_params['module'];
                    }
                
                    $model=false;
                
                    if(isset($route_params['model'])){
                        $model=$route_params['model'];
                    }
                    
                    $action=false;
                    
                    if(isset($route_params['action'])){
                        $action=$route_params['action'];
                    }
            
                    //Rota HMVC ou MVC
                    if($model&&$action){
                    
                        $url = $route_params['url'];
                        ApplicationEvents::fireEvent('beforeCheckAccess',$url);

                        //Usuario tem acesso a esta rota
                        if(AppUser::isAllowed($module,$model,$action)){
                      
                            ApplicationEvents::fireEvent('beforeReadAccessLevel',$url);

                            $access_level =
                            AppInstance::getAccessManager()->getUserAccessLevel($module,$model,$action);

                            $route_params['access_level']=$access_level;


                            //Dispara o evento [antes de invocar a action]

                            ApplicationEvents::fireEvent('beforeAction',$url);
                             
                            //Roteador executa o roteamento
                            $router->route($route_params);
                             
                        }else{
                             
                            //Usuario nao tem acesso
                            AppInstance::getAccessManager()->http_accessForbidden(self::$url);
                             
                        }
                        
                               
                    }else{
                        
                        
                        //Outro tipo de rota
                        if(isset($route_params['require_access_level'])){
                            
                             $route = $route_params['require_access_level'];
                             ApplicationEvents::fireEvent('beforeReadAccessLevel',$route);
                             $access_level = AppInstance::getAccessManager()->getUserAccessLevel($route);
                             $route_params['access_level']=$access_level;
                        }
                        
                        //Dispara o evento [antes de invocar a action]
                     
                        //ApplicationEvents::fireEvent('beforeAction',$url);
                        $router->route($route_params);
                        
                    }
                     
            
            
            
        }
        
    
           
    }
    
    
    
    public static function isLoaderRoute($url){
        
        $route = getDefaultController();    
        return $route===$url;    
        
    }
    
    public static function pageNotFound(){
        
        $filename = APPLICATION_DIR.'views/errors/404.html';
        
        if(file_exists($filename)){
            
            header('Content-Type:text/html');
            readfile($filename);
            
        }else{
            
            echo '<h1>Page not Found</h1>';
            
        }
        
    }
    

    
}