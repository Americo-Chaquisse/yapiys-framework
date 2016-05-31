<?php


abstract class Router {
    private $accessManager=null;
    
        public function onError($exception){

            
            
        }
        
        public function block_connection($route){
        
         //Carrega ficheiro de configuracao de rotas
            $rconfig = loadConfiguration('routing');
                        
                            
                //Existem configuracoes de rotas sem ligacao de dados
                if(property_exists($rconfig,'no_data')){
                                
                                
                    //Rotas que nao precisam de ligacao de dados
                    $no_data_routes = $rconfig->no_data;
                                
                                
                        //A presente rota nao precisa de ligacao de dados
                        if(in_array($route,$no_data_routes)){
                                    
                                
                            //Nao ligue 
                            return true;
                                    
                        }
                          
                                
                        //Ligue a conexao
                        return false;
                                
                                
                        }else{//Ficheiro de configuracoes de rotas
                                
                            //Ligue a conexao
                            return false;
                                
                        }
                        
        
    }
      
    
    
    /**
     * 
     * @param AccessManager $accessMngr
     */
    public function __construct($accessMngr) {
        $this->accessManager=$accessMngr;
    }

    
    /**
     * 
     * @return AccessManager
     */
    public function getAccessManager(){
        return $this->accessManager;
    }
    
    
    public static function loadAppModels(){

        //Do not load each models - PHP AUTOLOAD WILL HANDLE IT
        return ;

        $module_models_dir=APPLICATION_DIR.MODELS_DIR.'/';

        if(file_exists($module_models_dir)){

        $files=scandir($module_models_dir);
        
            
        foreach ($files as $filename) {
            if($filename==='.'||$filename==='..'){
                continue;
            }
            
            $file_path=$module_models_dir.$filename;
            require_once $file_path;
        }

        }
    }
        
    
    public static function loadModel($model_name,$module_name=false){


        //Models can te auto_loaded
        if(AppInstance::$autoloadModels){



        }
       
        $model_file = false;
        
        
        if($module_name){
            
            $model_file=APPLICATION_DIR.MODULES_DIR.'/'.$module_name.'/models/'.$model_name.'.php';
        
        }else{
            
            $model_file=APPLICATION_DIR.'models/'.$model_name.'.php';
            
        }
        
        
        if(file_exists($model_file)){
        
            require_once $model_file;
            
        }
           
        
    }
    
    
    public static function loadModuleModels($module_name=false){
        
        if(!$module_name){
            
            self::loadAppModels();
            
            return;
            
        }

        //Never auto-load models EVEN FROM A SPECIFIC MODULE
        if(!AppInstance::$autoloadModels){

            return false;

        }
        
        $module_models_dir=APPLICATION_DIR.MODULES_DIR.'/'.$module_name.'/models/';

        if(file_exists($module_models_dir)){

            $files=scandir($module_models_dir);
            
            foreach ($files as $filename) {
                if($filename==='.'||$filename==='..'){
                    continue;
                }
                
                $file_path=$module_models_dir.$filename;
                require_once $file_path;
            }

        }
    }
    

    public abstract function routeExists($contextable,$url);
    
    
    public function getRouteContext($url){
        $array = explode("/",$url);
        return $array[0];
    }
        

    public abstract function route($array);
    
    /**
     * Obtem o array de parametros separados por / em uma string.
     * @param type $string
     * @return type
     */
    public function fetchParams($string){
        return explode('/', $string);
    }
    
        
    public function adaptMimeRL($params){
        return $params;
    }
     
    public function isMIMERequest(){
         
        if(isset($_GET['resource_path'])){
            
                $fpath=$_GET['resource_path']; 
                $module_name=$_GET['module'];
                $model=$_GET['model'];
                $action_name=$_GET['action'];
               
                $fileExtension=  pathinfo($fpath,PATHINFO_EXTENSION);
                $filename=  pathinfo($fpath, PATHINFO_BASENAME);
                $diretory=  pathinfo($fpath,PATHINFO_DIRNAME);         
            
                $data=array('module'=>$module_name,'model'=>$model,'action'=>$action_name,
                    'filepath'=>$fpath,'extension'=>$fileExtension,
                    'filename'=>$filename,'directory'=>$diretory);
                    return new Bundle($data);
       }
             
        return FALSE;
    }

    
}

