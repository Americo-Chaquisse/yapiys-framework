<?php
/**
 * Description of ApplicationInstance
 *
 * @author Mario Junior
 */
class AppInstance {
    
    private static $access_manager=null;
    private static $routers=array();
    private static $context=null;
    private static $usesContext=true;
    private static $user_session_bundle=null;
    private static $current_hmvc_path=false;
    
    public static $current_view_php_file=false;
    public static $current_view_url=false;
    public static $callback_in_execution=false;
    public static $callback_in_execution_namespace=false;
    public static $callback_in_execution_id=false;
    public static $entranceRoute=false;
    
    public static $route_module=false;
    public static $route_model=false;
    public static $route_action=false;
    public static $route_params=false;
    
    public static $output_view = true;
    
    public static $configDataOverride=false;
    
    public static $switch = false;
    
    public static $console_context = 'debug';
    
    public static $context_controller_activation_token = 'auth';
    
    public static $defaultRoute = false;

    public static $autoloadModels = true;

    public static $request = false;
    
    public static $apiHooks = array(

        'before'=>array(),
        'after'=>array()

    );



    
    
    /**
     * Define um hook que deve ser chamada
     * @param $when
     * @param $callable
     */
    public static function setAPIHook($when,$callable){

          if($when==='before'||$when==='after'){

              self::$apiHooks[$when][] = $callable;

          }


    }

    public static function languageExists($name){

        $name = $name.'.json';

        $dir = APPLICATION_DIR.APPLICATION_LANGUAGES_DIR;


        if(file_exists($dir)){


            $languages_dir_content = scandir($dir);

            //This file exists
            if(in_array($name,$languages_dir_content)){

                return $dir.$name;

            }

        }

        return false;

    }

    public static function getLanguage(){


        if(self::getSession()->contains('user_lang')){

            return self::getSession()->get('user_lang');

        }

        return false;

    }

    public static function setLanguage($name=false){

        $langFilePath = false;

        //It is not de default language
        if($name){

            $langExists = self::languageExists($name);


            if($langExists){

                self::getSession()->set('user_lang', $name);

                $langFilePath = $langExists;

            }else{

                return false;

            }


        }else{

            //Set the default Language
            self::getSession()->remove('user_lang');


        }

        /**
         * Setting language from Ajax Request
         * SEND BACK A COMMAND TO THE CLIENT SIDE, TO REDIRECT THE USER TO THIS URL
         **/
        if(Functions::isAjaxRequest()){

            VCL::setCommand('$reload',$_GET['route_url']);

        }


    }


    /**
     * Reads the dictionary of the defined language
     * @return bool|string If the dictionary exists, the JSON content is returned, otherwise returns FALSE.
     */
    public static function readLanguageDictionary(){

        //What language is defined currently
        $name = self::getLanguage();

        if(!$name){

            return false;

        }else{

            $languageDictionaryPath = self::languageExists($name);

            if($languageDictionaryPath){

                //Read the language dictionary
                $languageDictionaryContent = file_get_contents($languageDictionaryPath);

                return $languageDictionaryContent;

            }


        }

        return false;

    }

    
    public static function setDefaultRoute($route){


        self::getUserSessionBundle()->set('app_default_route', $route);
        self::$defaultRoute = $route;
        
        
    }
    
   
    public static function defaultRouteOverwriten(){
        
        if(!self::$defaultRoute){
            
            if(self::getUserSessionBundle()->contains('app_default_route')){
                
                     
                return self::getUserSessionBundle()->get('app_default_route');
                
            }
            
        }        
        
        return self::$defaultRoute;       
        
        
    }
    
        
    public static function readTemplateData(){
        
        //Existe o controlador do template
        if(class_exists('TemplateController',false)){
            
            $instance = new TemplateController();
            
            //Existe funcao que retorna dados
            if(method_exists($instance, 'onLoad')){
                
                $data = $instance->onLoad();
                
                
                //Foi retornado um array
                if(is_array($data)){
                    
                    return $data;
                    
                }
                
            }
            
        } else {

            $file = VCL::getTemplateControllerFile();

            if(file_exists($file)){

                require_once($file);

                $instance = new TemplateController();

                //Existe funcao que retorna dados
                if(method_exists($instance, 'onLoad')){

                    $data = $instance->onLoad();


                    //Foi retornado um array
                    if(is_array($data)){

                        return $data;

                    }

                }

            }
        }


        return array();
        
    }
    
       
    public static function getCurrentHmvcPath(){
        return self::$current_hmvc_path;
    }
    
    public static function setCurrentHmvcPath($module,$model,$action){
        if($module){
            
            self::$current_hmvc_path= $module.'/'.$model.'/'.$action.'/';
            
        }else{
            
            self::$current_hmvc_path= $model.'/'.$action.'/';
            
        }
    }
 
    
    public static function userIsViewing($phpFile,$url=false){
        self::$current_view_php_file=$phpFile;
        self::$current_view_url=$url;
    }
    
    public static function getApplicationURL($custom_context=false){
       
        $app = loadConfiguration('app');

        //At least one baseURL is required
        if(property_exists($app,'development_base_url')||property_exists($app,'production_base_url')){


            //Application under devopment
            if($app->development){

                if(!property_exists($app,'development_base_url')){

                    throw new Exception('Property development_base_url not defined in app.json');

                }

                return $app->development_base_url;

            }else{


                if(!property_exists($app,'production_base_url')){

                    throw new Exception('Property production_base_url not defined in app.json');

                }

                //Debug desligado
                return $app->production_base_url;

            }

        }else{

            throw new Exception('Base URLS not correctly configured. Define production_base_url or development_base_url in app.json');

        }


    
    }
    
    public static function location(){
        $toExplode='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];        
        return $toExplode;
    }
    
    public static function locationFrom($where){
        $location = self::location();
        $exploded = explode($where,$location);
        
        if(count($exploded)>1){            
           return $exploded[0];
            
        }
        
        return false;
    }
    
    
    /**
     * http://server.name/middleURL/context/
     */
    public static function getMiddleURL(){
        $app_protocol="http://";
        $app_base_url=self::getApplicationURL();
        $toke_to_explode=$app_protocol.$_SERVER['SERVER_NAME'];
        $exploded=  explode($toke_to_explode,$app_base_url);
        return $exploded[1];
    }
    
    /**
     * 
     * @param UserSessionBundle $usb
     */
    public static function setUserSessionBundle($usb){
        self::$user_session_bundle=$usb;
    }
    
    
    /**
     * 
     * @return UserSessionBundle
     */
    public static function getUserSessionBundle(){
        return self::$user_session_bundle;
    }
    
    
    
    public static function getSession(){
        return self::getUserSessionBundle();    
    }
    
  
    public static function usesContext(){
        return self::$usesContext;
    }
    
      
    public static function setContext($ctx){
        self::getUserSessionBundle()->set('app_user_context', $ctx);
        self::$console_context=$ctx;
        self::$context=$ctx;
    }


    /**
     * Sets the template to be loaded. This function shall be called before any operation in controllers.
     * When the template is set during an AJAX request the user is fully redirected to the same URL
     * before any other operation.
     * @param $tmpl String name
     * @return bool If this functions returns FALSE The controller function shall call the view immediatelly.
     */
    public static function setTemplate($tmpl){


        $activeTemplate = self::getTemplate();
        //Same template
        if($activeTemplate===$tmpl){

            return false;

        }

        self::getUserSessionBundle()->set('user_views_template', $tmpl);

        //Setting template in a ajax request
        if(Functions::isAjaxRequest()){


            //Sets the command to RELOAD this URL in front-end
            $url = $_GET['route_url'];
            VCL::setCommand('$reload',$url);

            return false;


        }

        return true;

    }
    
   
    public static function getTemplate(){
        if(self::getUserSessionBundle()->contains('user_views_template')){
            return self::getUserSessionBundle()->get('user_views_template');
        }      
        return FALSE;
    }
    
    public static function getContext(){

        return AppUser::getContext();

    }

    /**
     * @return bool
     * @description: Verifica se o contexto da aplicação está activo
     */
    public static function isContextEnabled(){

        $appConfig = loadConfiguration('app');

        if($appConfig->contexts && $appConfig->contexts == true){

            return true;

        }

        return false;

    }
        
    /**
     * 
     * @param type $name
     * @return Router
     */
    public static function getRouter($name){
        
        if(isset(self::$routers[$name])){
            return self::$routers[$name];
        }
        
    }
    
    
    public static function setConfigData($cfg){    
        self::$access_manager->setConfigData($cfg);    
    }
    
    public static function getConfigData(){
        return self::$access_manager->getConfigData();        
    }
    
    public static function setAccessManager($accessManager){
        self::$access_manager=$accessManager;
    }
    
    /**
     * 
     * @return AccessManager
     */
    public static function getAccessManager(){
        return self::$access_manager;
    }
    
    public static function registerRouter($router){
        $routerName=get_class($router);
        self::$routers[$routerName]=$router;
    }    
    
    /**
     * 
     * @return array
     */
    public static function getAllRouters(){
        return self::$routers;
    }
    
    public static function contextMatches($context){
        return ($context===self::getContext());
    }
    

    public static function isOnline(){
        
        return !loadConfiguration('app')->development;
           
    }
    
    public static function isOffline(){
        $app = loadConfiguration('app');
        
        if(!$app){
            
            throw new Exception('Application configuration is not a valid JSON');
            
        }
        
        
        if(!property_exists($app,'development')){
            
            throw new Exception('Incomplete application configuration. Please add the <b>development</b> attribute');
        
        }
        
        return $app->development;
           
    }
    
    
}