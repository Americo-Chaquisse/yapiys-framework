<?php

class APIRouter extends Router {
    
    public static function generateWebserviceClassName($webservice){
        return ucfirst($webservice).'API';
    }
    
    public function routeExists($contextable,$url){
              
        $url_parts = explode('API/',$url);

            //Contexto esta ligado
            //if($contextable){
                
                
                if(count($url_parts)<2){

                    return false;   
                }
                
                 
            
                $url_parts=explode('/',$url_parts[1]);
        
                         
                //Obtem o nome do modulo
                $module = false;
                
                //Obtem o nome do model
                $webservice = false;   
                
                //Funcao do webservice
                $webservice_action= false;
                
                if(count($url_parts)>1){

                    $module=$url_parts[0];
                    $webservice=$url_parts[1];
                    
                    if(count($url_parts)>2){
                        $webservice_action = $url_parts[2];
                    }
                    
                }else if(count($url_parts)==1){
                    $webservice=$url_parts[0];
                }
                
                $webserviceName=  $this->generateWebserviceClassName($webservice);

                $params= $this->getWebserviceParams();
                $filename=$this->generateWebserviceURL($webserviceName,$module);
        
                            
                $get_params = $this->getGETParams();                
                $post_params = $this->getPOSTParams();
        
                
                if(file_exists($filename)){
                    
                    
                    if($module){
                        
                        
                        $the_route = array('module'=>$module,'class'=>$webserviceName,'params'=>$params,
                                    'post'=>$post_params,
                                    'get'=>$get_params,
                                    'action'=>$webservice_action
                        );  
                        
                        
                        if($this->block_connection($url)){
                            
                            $the_route['block_connection']=true;
                            
                        }
                        
                        
                        return $the_route;
                        
                    }else{
                        
                        
                        $the_route = array('class'=>$webserviceName,
                                     'params'=>$params,
                                     'get'=>$get_params,
                                     'post'=>$post_params
                                    );  
                        
                        
                        if($this->block_connection($url)){
                            
                            $the_route['block_connection']=true;
                            
                        }
                        
                        
                        return $the_route;
                        
                    }
                    
                }else{
                                        
                    
                    //$filename_root = $this->generateWebserviceRootURL($webservice);

                    if(count($url_parts)==2){

                            $filename = APPLICATION_DIR.'API/'.ucfirst($url_parts[0]).'API.php';

                            if(file_exists($filename)){

                                $params= $this->getWebserviceParams();                                  
                                $get_params = $this->getGETParams();                
                                $post_params = $this->getPOSTParams();

                                    return array('class'=>ucfirst($url_parts[0]).'API',
                                     'mvc'=>true,
                                     'params'=>$params,
                                     'post'=>$post_params,
                                     'get'=>$get_params,
                                     'action'=>$url_parts[1]); 
                 

                            }
                         
                                
                    }

                    return false;


            }


                
                  
    }
    
    public function collectRouteInformation() {
        $webserviceName=$_GET['webservice_name'];
        $webserviceName=  $this->generateWebserviceClassName($webserviceName);
        $params=  $this->getWebserviceParams();
        return array($webserviceName,$params);
        
    }
    
    public function getWebserviceParams(){
         $params= $_REQUEST;
        
         if(isset($params['callback'])){
             
             unset($params['callback']);
             
         }
        
        
        if(isset($params['_'])){
            
            unset($params['_']);
            
        }
        
        
         return $params;
    }
    
    
    private function getPOSTParams(){
         $params= $_POST;
         return  new Bundle($params);
    }
    
    
    private function getGETParams(){
         $params= $_GET;
         unset($params['route_url']);
         return  new Bundle($params);
    }


    public function readyToRoute() {
        return isset($_GET['webservice_name'])&&isset($_GET['mode']);
    }

    public function onError($e){

        echo json_encode(array('error'=>500,'type'=>'Unhandled Webservice exception','exception'=>$e->getMessage()));
        exit();

    }    

    public function getRequestParams($array){
        
        //Se for uma requisicao post
        if(isPostRequest()){
            
            return $array['post'];
            
        }else{
            
            return $array['get'];
            
        }
        
    }
    
    public function route($array) {

        $class = $array['class'];
        $params=$array['params'];
        $module=false;
        
    
        $this->loadAppModels();

        //The API Class was not loaded yet
        if(!isset($array['loaded'])) {

            if (isset($array['module']) && !isset($array['root'])) {
                $module = $array['module'];
                $filename = $this->generateWebserviceURL($class, $module);

                //Se existir o webservice no module
                if (file_exists($filename)) {

                    $this->loadModuleModels($module);

                    require_once $filename;
                }


            } else if (isset($array['mvc'])) {

                $filename = APPLICATION_DIR . 'API/' . $array['class'] . '.php';

                if (file_exists($filename)) {

                    $this->loadAppModels();
                    require_once $filename;

                }


            } else {

                if (isset($array['root'])) {

                    $module = $array['module'];
                    $this->loadModuleModels($module);

                    $filename = $this->generateWebserviceRootURL($module);

                    //Se existir o webservice no module
                    if (file_exists($filename)) {
                        require_once $filename;
                    }


                } else {

                    $filename = $this->generateWebserviceURL($class);

                    //Se existir o webservice no module
                    if (file_exists($filename)) {
                        require_once $filename;
                    }

                }


            }
        }


        //This request is coming from the partial router
        if(isset($array['partial'])){


            //It is not a partial webservice
            if(!is_a($class,'PartialWebService',true)){


                echo json_encode(array('error'=>array('message'=>'Invalid API parent. Use PartialWebService!','code'=>403)));
                exit();


            }

        }

        
        if(class_exists($class)&&  is_a($class, 'WebService',true)){

            $has_on_post = method_exists($class,'onPost');

            $has_on_get = method_exists($class,'onGet');

            $has_on_request = method_exists($class,'onRequest');

            $instance = new $class();

            if(isset($array['partial'])){

                $instance->module = $array['module'];
                $instance->partial = $array['partial_name'];

            }


            //Tem verificacao de CSRF activa
			
			//CSRF Begins here
			/*
            if(is_a($class,'CSRFCheck',true)){

                if(isset($_REQUEST[CSRF_TOKEN])){

                    $token = $_REQUEST[CSRF_TOKEN];

                    //Token incorrecto
                    if($token!==getAppInstanceID()){

                        http_response_code(403);
                        echo json_encode(array('error'=>array('message'=>'Access denied','code'=>403)));
                        exit();


                    }else{

                        //Token correcto : verificar o referer

                        if(isset($_SERVER['HTTP_REFERER'])){


                            $referer = $_SERVER['HTTP_REFERER'];

                            //Request vindo de um site diferente
                            if(!startsWith($referer,AppInstance::getApplicationURL())){

                                http_response_code(403);
                                echo json_encode(array('error'=>array('message'=>'Cross Site Request Forgery','code'=>403)));
                                exit();

                            }

                        }


                    }

                }else{


                    //Token nao especificado
                    http_response_code(403);
                    echo json_encode(array('error'=>array('message'=>'Access Denied','code'=>403)));
                    exit();

                }


            }*/
			
			//CSRF Check ends here

            
            //Webservice_extendido
            if(is_a($class, 'ExtendedWebService',true)){

                $has_on_post = method_exists($class,'onPost');

                $has_on_get = method_exists($class,'onGet');

                $has_on_request = method_exists($class,'onRequest');

                //$instance = new $class();

                $continue = $this->executeHooks('before',$instance);


                if(!$continue){

                    exit();

                }
                
                //Accao do webservice
                $action_name = $array['action'];
                
                //Webservice tem a accao especificada
                if(method_exists($instance,$action_name)){
                    
                    
                    //Metodo publico ou privado?
                    $reflectionMethod = new ReflectionMethod($class,$action_name);
                    
                    //Funcao privada
                    if($reflectionMethod->isPrivate()){
                        http_response_code(500);
                        echo json_encode(array('error'=>array('message'=>'API end point unreachable!','code'=>403)));   
                        exit();
                    }
                

                    //Executar a accao
                    $instance->$action_name($this->getRequestParams($array));
                    exit();
                    
                }
                
                
                   
                //Accao nao encontrada na API
		http_response_code(500);
                echo json_encode(array('error'=>array('message'=>'API end point unreachable!','code'=>403)));   
                exit();
                
            }
            
            //On Request
            if($has_on_request){
                
                 $instance->onRequest($this->getRequestParams($array));
                
            }
            
            
            //Trata-se de um post
            if(isPostRequest()){
                
                
                if($has_on_post){
                    
                    $instance->onPost($this->getRequestParams($array));
                    
                }
                
                
            }else{
            
                //Trata-se de um get
                
                
                if($has_on_get){
                    
                    
                    $instance->onGet($this->getRequestParams($array));
                    
                }
                
                
            }
            
        
        }


        
    }

    private function generateWebserviceURL($classname,$module=false){
        $url=false;
      
        if(!$module){
                    
            $url = APPLICATION_DIR.WEBSERVICES_DIR.$classname.'.php';
        }else{
            $url = APPLICATION_DIR.MODULES_DIR.'/'.$module.'/'.WEBSERVICES_DIR.$classname.'.php';
        }
        return $url;
    }
    
    
    private function generateWebserviceRootURL($module=false){
        
        $url=false;
      
        if(!$module){
                    
            $url = APPLICATION_DIR.WEBSERVICES_DIR.$classname.'.php';
        }else{
            $url = APPLICATION_DIR.MODULES_DIR.'/'.$module.'/'.WEBSERVICES_DIR.'APIRoot.php';
        }
        
        return $url;
        
    }
    
    
    public function isUserAllowedToAccessRoute($array) {
        
        var_dump($array);
        
        //Nome da classe do webservice
        $class = $array[0];
        
        $wsFile=false;
        
        if(count($array)==1){
            
        //Ficheiro php do webservice
        $wsFile = $this->generateWebserviceURL($array[0]);
            
        }else{
             
             //Ficheiro php do webservice
            $wsFile = $this->generateWebserviceURL($array[0],$array[1]);    
            
        }
        
        
        //Ficheiro deve existir
        if(file_exists($wsFile)){
            
            //Inclui ficheiro
            require_once $wsFile;
        }else{
            var_dump($wsFile);   
        }
        
        
        //Classe de webservice deve exitir e deve  filha de Webservice
        if(class_exists($class)&&  is_a($class, 'WebService',true)){            
            
            
            //Se existir a propriedade que informa se o webservice eh publico ou nao.
            if(property_exists($class, 'public')){                
                                
                $isPublic=$class::$public;
                
                if($isPublic){
                    
                    return true;
                    
                }else{
                    
                    return AppInstance::contextMatches($array['context']);
                    
                }
                
                
                                
            }else{
                
                return true;
            }
            
        }else{
            return false;
        }
    }
    
   public function executeHooks($when,$instance){

        $hooks_false = array();
        $hooks_true = array();

        //Existem hooks para este momento
        if(isset(AppInstance::$apiHooks[$when])){



            $bundle = new Bundle($_REQUEST);
            $callables = AppInstance::$apiHooks[$when];

            foreach($callables as $callable){


                if(!is_callable($callable)){

                    continue;

                }

                $hook_response = call_user_func_array($callable,array($bundle,$instance));



                if($hook_response){

                    $hooks_true[]  = $hook_response;

                }else{


                    $hooks_false[] = $hook_response;

                }

            }

            return count($hooks_false)==0;

        }


        return false;

    } 

}
