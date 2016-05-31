<?php 
class YapiysRouter extends Router {
    
       
    public function routeExists($contextable,$url){
                 
            $url_parts = explode("yp",$url); 
            
            if(count($url_parts)>1){
                
                //Trata-se de um asset
                $asset_filename= $url_parts[1];
                
                //URL do asset
                $filename=FRAMEWORK_ROOT_DIR.'js'.$asset_filename;
                
                if($asset_filename==='/yapiys.js'){

                    return array('dynamic'=>true,'file'=>$filename,'block_connection'=>true); 


                //Ficheiro Yapiys de uma view especifica
                }else if(startsWith($asset_filename,'/yapiys/')){


                    $route = explode('/yapiys/',$asset_filename);
                    $url = $route[1];
                    $extension = pathinfo($url,PATHINFO_EXTENSION);
                    $parts = explode('.'.$extension,$url);

                    $path = $parts[0];

                    //Access granted to this route
                    if(AppUser::isAllowed($path)) {

                        return array('block_connection' => true, 'file' => $url, 'static' => $url);

                    }

                    //Access denied
                    return false;


                }else{

                     if(startsWith($asset_filename,'/plugins')){

                         return array('filename'=>$asset_filename,'plugin'=>true,'block_connection'=>true);

                     }

                }
                      
                return array('filename'=>$filename,'block_connection'=>true);                
                
                
            }else{
                return false;   
            }
             
    }
    
    
    
    
    //Obtem a lista de accoes do controlador em questao
    public static function getControllerActions($classname){
       
                
        $abstract_controller_reflection = new ReflectionClass('ViewController');

        
        $abstract_controller_methods = $abstract_controller_reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        
        $abstract_controller_methods_names = array();
        
        
        foreach($abstract_controller_methods as $method){
            
            $abstract_controller_methods_names[] = $method->name;
            
            
        }
        
        $controller_reflection = new ReflectionClass($classname);
        
        //Metodos da controller
        $controller_methods = $controller_reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        $controller_methods_names = array();
        
        foreach($controller_methods as $method){
            
            $controller_methods_names[] = $method->name;
            
        }
        
        
        $methods = array();
        
        foreach($controller_methods_names as $method){
            
            if(!in_array($method,$abstract_controller_methods_names)){
         
               $methods[] = $method;
                
            }
               
        }
    
        
        return $methods;
        
    }
    

    

    
    public function getAllPartials(){
        
        $app_modules_directory = APPLICATION_DIR.MODULES_DIR;
        $modules_directories = scandir($app_modules_directory);
        $partials_content="";
        
        foreach($modules_directories as $module_directory){
            
            if($module_directory!=='.'&&$module_directory!=='..'){
                $module_directory_path = $app_modules_directory = APPLICATION_DIR.MODULES_DIR.'/'.$module_directory;
                $module_partials_directory = $module_directory_path.'/partials/';
                
                
                //Existem os controladores de partials
                if(file_exists($module_partials_directory)){


                    //Lista de partials
                    $partials_list = scandir($module_partials_directory);
                    
                    foreach($partials_list as $partial){


                        
                        if($partial!=='.'&&$partial!=='..'){


                            $partial_path=$module_partials_directory.$partial;
                            $partial_controller_path =  $partial_path.'/controller.js';


                            if(file_exists($partial_controller_path)){

                                $partials_content=$partials_content."\nYapiys.Internal.setNextPartialInformation('$module_directory','$partial');";

                                $partial_content=file_get_contents($partial_controller_path);
                                $partials_content=$partials_content."\n". $partial_content;   
                            }
                            
                        }
                        
                    }
                    
                    
                    
                    
                }
                
                
                
            }
            
        }
        
        return $partials_content;
      
    }


    public function getRootPartials(){

        $partials_content="";

                $module_directory_path =  APPLICATION_DIR.PARTIALS_DIRECTORY;
                $module_partials_directory = $module_directory_path.'/';


                //Existem os controladores de partials
                if(file_exists($module_partials_directory)){



                    //Lista de partials
                    $partials_list = scandir($module_partials_directory);

                    foreach($partials_list as $partial){


                        if($partial!=='.'&&$partial!=='..'){

                            $partials_content=$partials_content."\nYapiys.Internal.setNextPartialInformation(false,'$partial');";

                            $partial_path=$module_partials_directory.$partial;
                            $partial_controller_path =  $partial_path.'/controller.js';


                            if(file_exists($partial_controller_path)){
                                $partial_content=file_get_contents($partial_controller_path);
                                $partials_content=$partials_content."\n". $partial_content;
                            }

                        }

                    }





        }

        return $partials_content;

    }
    
    
    public function getAllAngular(){


        $angular_content="";
        
        $root_angular_file = APPLICATION_DIR.'angular.js';

        //Ficheiro angular da raiz
        if(file_exists($root_angular_file)){

            $angular_content = file_get_contents($root_angular_file)."\n";
            
        }


        $app_modules_directory = APPLICATION_DIR.MODULES_DIR;

        if(!file_exists($app_modules_directory)){

            return $angular_content;

        }


        $modules_directories = scandir($app_modules_directory);

        foreach($modules_directories as $module_directory){
            
            if($module_directory!=='.'&&$module_directory!=='..'){
                $module_directory_path = $app_modules_directory = APPLICATION_DIR.MODULES_DIR.'/'.$module_directory;
                $module_angular_file = $module_directory_path.'/angular.js';
                
                
                //Existe o ficheiro angular
                if(file_exists($module_angular_file)){                    
                    
                    $angular_content=$angular_content.file_get_contents($module_angular_file)."\n";
                                       
                }
                
                
                
            }
            
        }
        
        return $angular_content;
      
    }


    public function getAllBorders(){



        $app_modules_directory = APPLICATION_DIR.MODULES_DIR;

        if(!file_exists($app_modules_directory)){

            return "";

        }


        $modules_directories = scandir($app_modules_directory);
        $border_content="";



        foreach($modules_directories as $module_directory){

            if($module_directory!=='.'&&$module_directory!=='..'){
                $module_directory_path = $app_modules_directory = APPLICATION_DIR.MODULES_DIR.'/'.$module_directory;
                $module_border_file = $module_directory_path.'/border/border.js';


                //Border script exists
                if(file_exists($module_border_file)){


                    $border_content = $border_content."\nYapiys.module.setNextBorderName('$module_directory');\n";
                    $border_content=$border_content.file_get_contents($module_border_file)."\n";


                }



            }

        }

        return $border_content;

    }
    
    
    
    public function getTemplateController(){

        $template = AppInstance::getTemplate();

        if(!$template){

            $template = DEFAULT_TEMPLATE_NAME;

        }

        $filename = APPLICATION_DIR.TEMPLATES_DIR.$template.'/controller.js';

        if(file_exists($filename)){
            return "\n\n".file_get_contents($filename)."\n\n Yapiys.UI.fireMasterLoadEvent(); ";       
        }else{
            return "";   
        }
    }
    
    public function getTemplatesMasterController(){
        $filename = APPLICATION_DIR.TEMPLATES_DIR.'master.js';
        if(file_exists($filename)){
            return "\n\n".file_get_contents($filename);       
        }else{
            return "";   
        }
    }
    

    public function getSpecificClosure($url){

        $set_closure_function="\nYapiys.Internal.setNextClosureInformation";

        $parts = explode('/',$url);
        $view_controller = $parts[count($parts)-1];
        $action = pathinfo($view_controller, PATHINFO_FILENAME);


        //HMVC
        if(count($parts)==3){

            $module = $parts[0];

            $controller = $parts[1];

            $file = APPLICATION_DIR.MODULES_DIR.'/'.$module.'/views/'.$controller.'/'.$view_controller;

            if(file_exists($file)){

                $content = "$set_closure_function('$module','$controller','$action');";
                $content = $content."\n\n".$content.file_get_contents($file);

                return $content;

            }




        //MVC
        }else if(count($parts)==2){

            $module = false;

            $controller = $parts[0];

            $file = APPLICATION_DIR.ROOT_VIEWS_DIR.'/'.$controller.'/'.$view_controller;

            if(file_exists($file)){


                $content = "$set_closure_function(false,'$controller','$action');";
                $content = $content."\n\n".file_get_contents($file);

                return $content;


            }


        }

        


    }
    
    public function getAllClosures(){
        
        $app_modules_directory = APPLICATION_DIR.MODULES_DIR;
        
        if(!file_exists($app_modules_directory)){
            
            return "";
            
        }
        
        
        $modules_directories = scandir($app_modules_directory);
        $set_closure_function="\nYapiys.Internal.setNextClosureInformation";
        $closures_content="";
        
        foreach($modules_directories as $module_directory){
            
            if($module_directory!=='.'&&$module_directory!=='..'){
                $module_directory_path = $app_modules_directory = APPLICATION_DIR.MODULES_DIR.'/'.$module_directory;
                $module_views_directory = $module_directory_path.'/views/';
                
                
                //Existem views
                if(file_exists($module_views_directory)){
                    
                   
                           
                    //Lista de Models
                    $models_list = scandir($module_views_directory);
                    
                    foreach($models_list as $model){
                        
        
                        
                        if($model!=='.'&&$model!=='..'){
                            
                            //Path do directorio de models
                            $model_directory_path=$module_views_directory.$model;   
                            
                            
                            if(!file_exists($model_directory_path)){
                                
                                continue;
                                
                            }
                            
                            //Ficheiros do directorio de models
                            $views_directory_files = scandir($model_directory_path);
                            
                            
                            //Percorre a lista de ficheiros de directorios 
                            foreach($views_directory_files as $view_directory_filename){
                                
                                $view_directory_filename_path = $model_directory_path.'/'.$view_directory_filename;
                                
                                $path_info = pathinfo($view_directory_filename_path);
                                
                                $file_extension = $path_info['extension'];
                                
                                $base_name= pathinfo($view_directory_filename_path, PATHINFO_FILENAME);
                                
                                //Extensao do ficheiro eh javascript 
                                if($file_extension==='js'){    
                                    
                                    //Explode o nome fo ficheiro
                                    $base_name_parts = explode('_',$base_name);
                                    
                                    //Basename possui mais de um pedaxo
                                    if(count($base_name_parts)>1){
                                        
                                        $view_filename =  $model_directory_path.'/'.$base_name_parts[0].'.html';
                                        
                                        //Ficheiro de view existe
                                        if(file_exists($view_filename)){
                                        
                                        //Nome da view    
                                        $view_name = $base_name_parts[0];

                                        $route =$module_directory.'/'.$model.'/'.$view_name;

                                        //User has no access to this controller action
                                        if(!AppUser::isAllowed($route)){

                                            continue;

                                        }
                                            
$closure_information= "$set_closure_function('$module_directory','$model','$view_name');\n";  
$closures_content= $closures_content.$closure_information.file_get_contents($view_directory_filename_path);
                                            
                                            
                                        }
                                        
                                        
                                    }else{


                                        $route =$module_directory.'/'.$model.'/'.$base_name;

                                        //User has no access to this controller action
                                        if(!AppUser::isAllowed($route)){

                                            continue;

                                        }
                                    
                                    //Basename possui apenas um pedaxo                                                       
$closure_information= "$set_closure_function('$module_directory','$model','$base_name');\n";  
$closures_content= $closures_content.$closure_information.file_get_contents($view_directory_filename_path);
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                    
                    
                    
                }
                
                
                
            }
            
        }
        
        return $closures_content;
        
    }
    
    
     public function getRootClosures(){
        
        $app_modules_directory = APPLICATION_DIR.MODULES_DIR;
        $modules_directories = scandir($app_modules_directory);
        $closures_content="";
        
                $module_directory_path = $app_modules_directory = APPLICATION_DIR.MODELS_DIR.'/';
                $module_views_directory =  APPLICATION_DIR.VIEWS_DIR.'/';
                
                
                //Existem views
                if(file_exists($module_views_directory)){
                           
                    //Lista de Models
                    $models_list = scandir($module_views_directory);
                    
                    foreach($models_list as $model){
                        
                        if($model!=='.'&&$model!=='..'){
                            
                            //Path do directorio de models
                            $model_directory_path=$module_views_directory.$model;   
                            
                            //Ficheiros do directorio de models
                            $views_directory_files = scandir($model_directory_path);
                            
                            
                            //Percorre a lista de ficheiros de directorios 
                            foreach($views_directory_files as $view_directory_filename){

                                if($view_directory_filename=='.'||$view_directory_filename=='..'||$model==='errors'){

                                    continue;

                                }
                                
                                $view_directory_filename_path = $model_directory_path.'/'.$view_directory_filename;
                                
                                $path_info = pathinfo($view_directory_filename_path);
                                
                                $file_extension = $path_info['extension'];

                                if($file_extension!=='js'){

                                    continue;

                                }
                                
                                $base_name= pathinfo($view_directory_filename_path, PATHINFO_FILENAME);
                                $actionName = $this->getActionName($base_name);

                                $path = $model.'/'.$actionName;


                                //User has no access to the controller
                                if(!AppUser::isAllowed($path)){

                                    continue;

                                }

                                //Extensao do ficheiro eh javascript 
                                if($file_extension==='js'){     
                                    
$closure_information= "\nYapiys.Internal.setNextClosureInformation(false,'$model','$actionName');\n";
$closures_content= $closures_content.$closure_information.file_get_contents($view_directory_filename_path);
                                    
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                    
                    
                    
                }
                
                
          
        return $closures_content;
        
    }

    private function getActionName($suspect){

        $parts = explode('_',$suspect);

        return $parts[0];

    }
    
    
    public function readyToRoute() {
        return isset($_GET['dir'])&& isset($_GET['url']);
    }
    
    

    
    
    public function route($params) {
             
        ob_start();
        
        
        if(isset($params['dynamic'])) {

            $filename = $params['file'];

            ob_clean();//Limpa o buffer de saida     

            header('Content-Type: application/javascript');

            //Fires the yapiys script output event : for caching headers and other stuff
            ApplicationEvents::fireEvent('before_yapiys_js_output');


            $content = file_get_contents($filename); //Faz leitura do ficheiro

            $content = $content. "\n\n" . VCL::getAppData(true, true);

            $content = $content. "\n" . $this->getTemplatesMasterController() . "\n";

            $content = $content. "\n" . $this->getAllAngular();

            $content = $content. "\n" . $this->getAllBorders();

            $content = $content. $this->getRootClosures();

            $content = $content. $this->getRootPartials();

            //echo $this->getAllPartials();
            $content = $content. $this->getAllPartials();

            $content = $content. $this->getAllClosures();

            $content = $content. $this->getTemplateController();

            //Production mode
            if(AppInstance::isOnline()){

                Framework::loadLibrary('minify',true);
                $minOutput = JSMin::minify($content);
                echo $minOutput;


            }else{

                //Development mode
                echo $content;

            }



            ob_end_flush();//Envia todo conteudo do buffer de saida


        }else if(isset($params['static'])){


            ob_clean();//Limpa o buffer de saida     

            header('Content-Type: application/javascript');

			 //Fires the yapiys script output event : for caching headers and other stuff
            ApplicationEvents::fireEvent('before_yapiys_js_output');
			

            $content = file_get_contents(FRAMEWORK_ROOT_DIR.'js/yapiys.js'); //Faz leitura do ficheiro

            $content = $content. "\n\n" . VCL::getAppData(true, true);

            $content = $content. "\n" . $this->getTemplatesMasterController() . "\n";

            $content = $content. $this->getSpecificClosure($params['file']);


            //Production mode
            if(AppInstance::isOnline()){


                Framework::loadLibrary('minify',true);
                $minOutput = JSMin::minify($content);
                echo $minOutput;


            }else{

                //Development mode
                echo $content;

            }

            ob_end_flush();//Envia todo conteudo do buffer de saida



        }else if(isset($params['plugin'])&&isset($params['filename'])){



            $parts = explode('/',$params['filename']);

            $shadow_filename = '';

            if(count($parts)==3){

                $shadow_filename = 'js/'.$parts[2];

            }


            $filename = false;

            $filename_real_path = VirtualYapiys::file_exists($shadow_filename);

            if($filename_real_path){

                $filename = $filename_real_path;


            }else{

                exit();

            }


            if(file_exists($filename)){


                ob_clean();//Limpa o buffer de saida

                header('Content-Type: application/javascript');
				
				//Fires yapiys plugin output event : handles caching and headers
				ApplicationEvents::fireEvent('before_yapiys_plugin_output');
				

                $content = file_get_contents($filename); //Faz leitura do ficheiro

                //Production mode
                if(AppInstance::isOnline()){

                    Framework::loadLibrary('minify',true);
                    $minOutput = JSMin::minify($content);
                    echo $minOutput;


                }else{


                    //Development mode
                    echo $content;

                }


            }




        }else{

                 //Asset filename
            $filename=$params['filename'];

            //Erro 404 se o ficheiro nao existir.
            if(!file_exists($filename)){
                http_response_code(404);
                return;
            }

            //Obtem o tipo MIME
            $MIMEType = $this->mime_content_type_($filename); //Obtem o mime Type do ficheiro

            ob_clean();//Limpa o buffer de saida     

            header('Content-Type: '.$MIMEType); //Manda o Mime-Type do ficheiro
            $content = file_get_contents($filename); //Faz leitura do ficheiro

            //Production mode
            if(AppInstance::isOnline()){

                Framework::loadLibrary('minify',true);
                $minOutput = JSMin::minify($content);
                echo $minOutput;


            }else{


                //Development mode
                echo $content;

            }

            ob_end_flush();//Envia todo conteudo do buffer de saida
            
            
        }
        
       
    }

    private function mime_content_type_($filename) {
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        
        $exploded=explode('.',$filename);
        $pop=array_pop($exploded);

        $ext = strtolower($pop);
        
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }

    public function isUserAllowedToAccessRoute($array) {
        return FALSE;
    }

}