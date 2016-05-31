<?php
/**
 * Description of ViewsControllersLayer
 *
 * @author Mario Junior
 */
class VCL{
    
    private $viewHTML=false, $UIBundle=null,$document=null,$template_options=null,$view_params,$view_filename;    
    private $componentsControlers=array();
    private $elementsMap=array();
    private $documentForms=array();
    private $data = false;
    public  $directhtml = false;
    public  $direct = false;
    public  static $append = array();
    public  static $preppend = array();
    public  static $commands = array();
    public  static $emulateEntrance = false;
    public  static $ignitionScript = "";


    public static function setCommand($name,$value){
        self::$commands[$name]=$value;
    }

    
    public function setUIBundle($bundle){
        $this->UIBundle=$bundle;
    }
       
    
    public function setDocument($dom){
        $this->document=$dom;
    }
    
    public function prepareToRender($filename,$uiBundle=null,$template_options=null,$view_params){


        //Bundle already has commands
        if($uiBundle->contains('$commands')){

            $bundle_commands = $uiBundle->get('$commands');

            $result_commands = array_merge($bundle_commands,self::$commands);

            $uiBundle->set('$commands',$result_commands);

        }else{

            //Bundle has no command
            $uiBundle->set('$commands',self::$commands);


        }

        $this->UIBundle=$uiBundle;


        $this->view_filename=$filename;
        $this->template_options=$template_options;
        $this->view_params=$view_params;

        $this->proceed();
    }
    
    
    private function getTemplate(){
        $domElement= new DOMDocument();
        
        $defaultTemplateFilename=APPLICATION_DIR.TEMPLATES_DIR.DEFAULT_TEMPLATE_DIR.'index.html';
        $templateFilename="";
        
        
        //Opcoes de template validas
        if($this->template_options){
            
            //Nome de template 
            if(isset($this->template_options['name'])){
                $templateName= $this->template_options['name'];
                $templateFilename=APPLICATION_DIR.TEMPLATES_DIR.$templateName.'/index.html';
                return $templateFilename;
            }
            
        }
        
        return $defaultTemplateFilename;
       
    }
    
    public static function getTemplateControllerFile(){
        
        $template_controller_file = APPLICATION_DIR.ROOT_CONTROLLERS_DIR.'/TemplateController.php';
            
        return $template_controller_file;
        
        
    }
    
    
    
    private function getTemplateController(){
        
        
        $template_name = 'main';
        
        
        if($this->template_options){
     
       
            $template_name = ucfirst($this->template_options);
            
                        
        }
        
        $template_controller_name = ucfirst($template_name).'TemplateController';
        
        return $template_controller_name;
        
        
    }
    
    private function proceed(){

        //Trata-se de uma requisicao ajax
        $is_ajax_request=  Functions::isAjaxRequest();
    
        if(!$is_ajax_request){

            $entranceRoute= array();
            $entranceRoute['module']= AppInstance::$route_module;
            $entranceRoute['model']=  AppInstance::$route_model;
            $entranceRoute['action']= AppInstance::$route_action;
            $entranceRoute['params']= AppInstance::$route_params;
            AppInstance::$entranceRoute=$entranceRoute;
            
        }
        
        //Nao renderiza o template se a requisicao for ajax
        $renderTemplate= !$is_ajax_request;
       
        //Controlador do template
        $template_controller_name = $this->getTemplateController();
        
        //Ficheiro de controlador de template
        //$template_controller_filename = $this->getTemplateControllerFile();

        
    
    
        //Existe controlador
        /*
        if(file_exists($template_controller_filename)){
                            
            //Leh o controlador
            include($template_controller_filename);
            
            //Classe de controlador existe
            if(class_exists($template_controller_name,false)){
                
                          
                //Cria nova instancia de controlador
                $template_controller_instance = new $template_controller_name(); 
                
                //Pega os dados a serem enviados
                $data_to_send = $template_controller_instance->onRequest();
    
    
                //Dados a serem enviados validos
                if(is_array($data_to_send)){
                               
                    //Coloca os dados no bundle
                    $this->UIBundle->defineIn($data_to_send,'$app_template_data');
                    
                }else if(is_a($data_to_send ,'Bundle')){                    
                    
                    $this->UIBundle->defineIn($data_to_send->getInternalArray(),'$app_template_data');
                    
                }

                
            }
           
         
        }else{
            
            
        }*/
        
        
        if($this->UIBundle){ 

            $data=  $this->UIBundle->getInternalArray();   
            
        } 
        
    
        
        //Template foi carregado
        if(true){

            $tempateData['app_data']=self::getAppData();
            $tempateData['views_data']=  $this->getViewsData();
            
            
            if(AppInstance::$entranceRoute){
                $tempateData['entrance_route']=AppInstance::$entranceRoute;
                AppInstance::$entranceRoute=false;
            }
            
            //Renderizar template
            if($renderTemplate){
                
                //Obtem as variaveis da aplicacao
                $app = self::getAppData(false);

                $entrace_route =self::putInScript("entraceRoute",AppInstance::$entranceRoute);   
                $app_script = self::putInScript("App",$app);

                
                //===========================
                //THIS VIEW PATH
                //===========================
                $module_name = AppInstance::$route_module;
                $model = AppInstance::$route_model;
                $action = AppInstance::$route_action;

                $language_dictionary = AppInstance::readLanguageDictionary();
                $set_lang = '';


                if($language_dictionary){

                    $set_lang = "Yapiys.setLanguage($language_dictionary)";


                }

                $set_lang_script = self::putStringInScript($set_lang);
                
                
                if($module_name){
                    
                    $set_view_path_snipet= "Yapiys.Internal.setNextViewPath('$module_name','$model','$action')";
                    
                }else{
                    
                    $set_view_path_snipet= "Yapiys.Internal.setNextViewPath(false,'$model','$action')";
                    
                }
                
                
                $set_view_path_script= self::putStringInScript($set_view_path_snipet);                
                
                //-----------------------------



                $initialize_status_script = "";

                $initialize_status_snipet = "Yapiys.run()";
                $initialize_status_script = self::putStringInScript($initialize_status_snipet);


                
                
                   
                $extended_view=false;
                $parent_view=false;
                $parent_view_name=false;
                $vhtml = false;
                
                
                
                //Output baseado em ficheiro de uma view
                if(!$this->directhtml){
                    
                    $file_handler = fopen($this->view_filename,'r');                
                    $first_line = strtolower(fgets($file_handler));                
                    fclose($file_handler);


                    if(startsWith($first_line,'<!--@extends:')){


                        $this->viewHTML = "";                   
                        $child_view = "";

                        $total_chars = strlen($first_line);                    
                        $string_chars = $total_chars-21;

                        $extend_expression_after = substr($first_line,13);

                        $extend_expression_after_parts = explode('-->',$extend_expression_after);

                        $parent_view_name = $extend_expression_after_parts[0];

                        $extended_view = true;

                        //Existe a parent view
                        if(VCL::existView($model,$parent_view_name,$module_name)){

                            //$parent_view = VCL::getView($model,$parent_view_name,$module_name);

                        }else{

                            $extended_view=false;

                        }


                    }else{

                        //Nao trata-se de uma view que faz extend.

                        //Carrega o ficheiro da view
                        $this->viewHTML = file_get_contents($this->view_filename);                    
                        $child_view = $this->viewHTML;


                    }
                
                }else{
                    
                    //Output de conteudo directo
                    $this->viewHTML = $this->view_filename;   
                    
                    
                }
                
                
                //Obtem o HTML da view
                $vhtml=$this->viewHTML;
                
                
                $toAppend = implode('<br>',self::$append);
                $toPreppend = implode('<br>',self::$preppend);
                $vhtml = $toPreppend.$vhtml.$toAppend;
                
                
                $toLoadArray = array('html'=>$vhtml);
                
                //View extendida
                if($extended_view){
                                    
                    $toLoadArray['extend']='true';
                    $toLoadArray['parent_html']=$parent_view;
                    $toLoadArray['parent_view_name']=$parent_view_name;
                    $toLoadArray['route']=self::encode_route($module_name,$model,$action);
                    
                                        
                }
                
                
                //Nao cachear conteudo directo
                if($this->direct){
                
                    $toLoadArray['cache']='false';
                
                }
                
                
                $toLoadJSON = json_encode($toLoadArray);
                $toLoadHtml = self::putStringInScript("var viewToLoad =$toLoadJSON",true);
                $view_html="<div id='app-view' ng-controller=\"view_controller\"></div>";
                
                
                $view_data = $this->UIBundle->getInternalArray();                
                ApplicationEvents::fireEvent('beforeTemplateDataRead');
                $template_data = AppInstance::readTemplateData();                
                $total_data = $view_data;
                $total_data['$root']=$template_data;
                $encoded_data = $this->utf8_converter($total_data);
                                
                $context_vars = self::putInScript("context_vars",$encoded_data,'context-vars',true);

                $ignition = self::getAcumullatedScript(YAPIYS_PREPARE_CONTROLLER.';');

                $scripts=$app_script."\n". $context_vars."\n".$toLoadHtml."\n".$ignition;
                $view_html=$scripts."\n".$view_html;

                //Obtem o HTML do template
                $template=$this->getTemplateHTML();

                $loader_script_content = "";
                $loader_script = FRAMEWORK_ROOT_DIR.'js/loader.js';

                $lang = AppInstance::getLanguage();

                if(!$lang){

                    $lang='"default"';

                }else{

                    $lang = '"'.$lang.'"';

                }

                $loader_script_footer = '

                    var AppLang = '.$lang.';

                    if(typeof loadApp=="function"){


                        if (window.addEventListener)
                            window.addEventListener("load", loadApp, false);
                        else if (window.attachEvent)
                            window.attachEvent("onload",loadApp);
                        else window.onload = loadApp;


                    }else{

                        if(typeof $ignition=="function"){

                            //Boot UP the Yapiys Application
                            angular.element(document).ready(function() {
                                    angular.bootstrap(document, ["yapiys"]);
                            });

                            $ignition();

                        }

                    }


                ';


                $template=str_replace("</body>","<script>$loader_script_footer</script></body>",$template);



                if(file_exists($loader_script)){

                    $loader_script_content = file_get_contents($loader_script);

                }

                $template=str_replace("</head>","</head><script>$loader_script_content</script>",$template);



                //Configura o Yapiys 
                $template = str_replace("{{setup-yapiys}}",
                                       "<script src='yp/angular.min.js'></script>
                                        <script src='yp/jquery.min.js'></script>
                                        <script src='yp/yapiys.js'></script>",$template);
                
                //Controlador do template
                $template=str_replace("enable-template-context","ng-controller='yapiys-template'",$template);
            
                
                //Preenche a view no template
                $document_html=str_replace("{{view_content}}",$view_html,$template);
                
                //Escreve o html no buffer de saida
                $output = $document_html;

                Framework::loadLibrary('minify',true);


                //TODO: Revision and make it work again
                /*
                $minOutput = Minify_HTML::minify($output, array(
                    'cssMinifier' => array('Minify_CSS', 'minify')
                ,'jsMinifier' => array('JSMin', 'minify')
                ));*/

                $minOutput = $output;


                echo $minOutput;

              
            
            }else{
                    
                if($this->directhtml){

                    //Output de conteudo directo
                    $this->viewHTML = $this->view_filename;  

                }else{


                    //Output viewgetAppData
                    if(AppInstance::$output_view){                   
                
                        
                        //Carrega o ficheiro da view
                        $this->viewHTML=file_get_contents($this->view_filename);
                        
                    }


                }

 
                
                //Obtem o HTML da view
                $vhtml=$this->viewHTML;
                
                
                $toAppend = implode('<br>',self::$append);
                $toPreppend = implode('<br>',self::$preppend);
                $vhtml = $toPreppend.$vhtml.$toAppend;
                                      
                //Obtem as variaveis da aplicacao
                $app = self::getAppData(false);
                
                $app_script = self::putInScript("App",$app);
                
                
                //Output viewgetAppData
                if(AppInstance::$output_view){                   
            
                    
                    //Carrega o ficheiro da view
                    $this->viewHTML=file_get_contents($this->view_filename);
                    
                }
                
                //Obtem o HTML da view
                //$vhtml=$this->viewHTML;
                $view_html="<div ng-controller=\"view_controller\">$vhtml</div>";
                $view_html=$app_script."\n".$view_html;

                //Obtem o HTML do template
                $template=$this->getTemplateHTML();
                //Preenche a view no template
                $document_html=$view_html;
                
                $view_output = array();
                $view_output['markup']=$document_html;
                $view_output['app']=$app; 
                
                                
                //Codifica o conteudo para UTF-8
                $view_output['context']=  $this->utf8_converter($this->UIBundle->getInternalArray());
                
                //Escreve o html no buffer de saida
                echo json_encode($view_output);
                
                
            }
     
        
    }
        
    }
    
    
    
     private function utf8_converter($array)
	{
		
		$internal = $array;
		
		if(is_string($internal)){
			return $internal;			
		}
	
		array_walk_recursive($internal, function(&$item, $key){
			
			//Trata-se de uma entity
			if(is_object($item)){
                            
                                if(is_a($item,'Entity')){
                                    
                                    //Converte o objecto para array		
                                    $item_array = $item->toArray();	                               

                                    //Converte o array utf8
                                    $item = $this->utf8_converter($item_array);

                                
                                }else{
                                    
                                        
                                    //Converte o objecto para array		
                                    $item_array = json_to_array($item);                              

                                    //Converte o array utf8
                                    $item = $this->utf8_converter($item_array);                                    
                                    
                                    
                                }
				
				return;
						
			}else if(is_array($item)){
			
				//Trata-se de um array
				
				//Converte o array para utf8
				$item = $this->utf8_converter($item);
					
				return;
					
			}
			
			if(!mb_detect_encoding($item, 'utf-8', true)){
			
					
				$item = utf8_encode($item);
						
				
			}
			
		});
	 
		return $internal;
	}


    private static function accumulate_script($script){

        if(trim(self::$ignitionScript)==''){

            self::$ignitionScript = $script;


        }else{

            self::$ignitionScript = self::$ignitionScript.";".$script;

        }



    }

    private static function getAcumullatedScript($script_to_accumulate=false){


        if($script_to_accumulate) {

            self::accumulate_script($script_to_accumulate);

        }

        $script = self::$ignitionScript;
        return "<script class='yapiys'>".'function $ignition(){'.$script.'}'."</script>";

    }

    private function putStringInScript($content,$no_actumulation=false){
    
        $the_script ="<script class='yapiys'>$content;</script>";

        if(!$no_actumulation){

            self::accumulate_script($content);

        }

        return $the_script;
    }
        
    private function putInScript($varname,$array,$id=0){
      
        $content="var $varname = ".json_encode($array);
   
        $the_script=false;
        
        if($id){
              $the_script ="<script class='yapiys' id=\"$id\">$content;</script>"; 
        }else{
             $the_script ="<script class='yapiys'>$content</script>";   
        }   
        
        return $the_script;
    }
    
    
    
        public function getTemplateHTML($name="main"){
                
                $filename=APPLICATION_DIR.TEMPLATES_DIR.$name.'/index.html';; 
            
                if($this->template_options){
                    
                    $filename=APPLICATION_DIR.TEMPLATES_DIR.$this->template_options.'/index.html';
                    
                }
          
                
                if(file_exists($filename)){
                    
                    $template_content= file_get_contents($filename);
                    
                    return $template_content;
                    
                }
        
            return '<html><body><div id="view_content">{{view_content}}</div></body></html>';
        }
    
    
   
    public static function generateViewURL($module,$model,$action){
        
        if($module){
            
            return                  APPLICATION_DIR.MODULES_DIR.'/'.strtolower($module).'/views/'.strtolower($model).'/'.strtolower($action);
            
        }else{
            
            return APPLICATION_DIR.VIEWS_DIR.'/'.strtolower($model).'/'.strtolower($action);
            
        }
        
    }
    
    
    public static function generateViewName($module,$model,$action){
        return strtolower($module).'/'.strtolower($model).'_'.strtolower($action);
    }
    
    public static function getAppData($json=true,$capitalize=false){
        
        $route =self::encode_route(AppInstance::$route_module,AppInstance::$route_model,AppInstance::$route_action);
        $app_data=array();
        $app_data['base_url']=  AppInstance::getApplicationURL();
        $app_data['hmvc']=  AppInstance::getCurrentHmvcPath();
        $app_data['midleURL']= "";
        $app_data['entrance_route']=$route;
        $app_data['instanceID']=getAppInstanceID();
        $app_data['under_development']=  AppInstance::isOffline();
        $app_data['csrf_param_name'] = CSRF_TOKEN;
        $app_data['csrf_token'] = getAppInstanceID();

        if(AppInstance::isContextEnabled()){
            $app_data['context']=  AppInstance::getContext();
        } else {
            $app_data['context']= '';
        }

        if($json){
            
            $variable = "app";
            
            if($capitalize){
                
                $variable="App";
                
            }
            return 'var '.$variable.'='.json_encode($app_data).';';            
        }else{
            return $app_data;
        }
    }
    
    
    private function generateComponentControllerName($component){
        return ucfirst($component).'Controller';
    }
    
    private function getViewsData($json=true){
  
        $controllersMap=array(); 
        $line_break="\n";
        
            
        $controllersMapScript = "";   
        
        foreach ($this->componentsControlers as $name =>  $controller_content) {
            $controllersMapScript=$controllersMapScript.$line_break.$controller_content;
            $controllersMap[$name]= $this->generateComponentControllerName($name);
        }
        
        if($json){
            $controllersMapVar="var controllersMap=".json_encode($controllersMap).';';
            $elementsMapVar="var elementsMap=".json_encode($this->elementsMap).';';        
            $formsMapVar = "var formsMap=".json_encode($this->documentForms).';';
            $view_informationVar="var view_info=".json_encode(
                array("params"=>$this->view_params)).';';
            $viewsData=$controllersMapScript.$line_break.$controllersMapVar.$line_break.$view_informationVar.$line_break.$elementsMapVar.$line_break.$formsMapVar;

            return $viewsData;
        
        }else{
            
            $viewsData['controllersMap']=$controllersMap;
            $viewsData['formsMap']=  $this->documentForms;
            $viewsData['view_info']=array("params"=>$this->view_params->getInternalArray());
            return $viewsData;
        }
      
    }
    
    
   
       
    public static function encode_route($module,$model,$action){
        $route = array();
        
        if($module){
            $route['module']=$module;            
        }
        
        $route['controller']=$model;
        $route['action']=$action;
        
        return $route;
        
    }
    

    private static function json_to_array($json_object){
       $array=array();
       $reflectJSON = new ReflectionObject($json_object);
       $properties=$reflectJSON->getProperties();
                         
        foreach ($properties as $property) {
            $name=$property->getName();
            $value=$property->getValue($json_object);
            
            if(is_object($value)){
                
                $array[$name]=self::json_to_array($value);
                
            }else if(is_array($value)){
                $new_array=self::convert_json_objects_in_array_to_arrays($value);
                $array[$name]=$new_array;
                
            }            
            else{                
                $array[$name]=$value;
            }
            
            
        }
        
        return $array;
    }
    
    private static function convert_json_objects_in_array_to_arrays($array){
        $new_array=array();
        foreach ($array as $key => $value) {
            
            if(is_object($value)){
                
               $new_value=self::json_to_array($value);
               $new_array[$key]=$new_value;
                
            }else if(is_array($value)){
                
                $new_value=self::convert_json_objects_in_array_to_arrays($value);
                 $new_array[$key]=$new_value;
                 
            }else{
                
                $new_array[$key]=$value;
            }
            
            
        }
        
        
        return $new_array;
    }
    
    
    public static function getView($model,$action,$module=false){
        
        
         $view = self::viewPath($model,$action,$module);        
                
        
        //Se a view existir
        if(file_exists($view)){
            
            //Retorna o seu conteudo
            return file_get_contents($view);  
            
            
        }               
             
        
    }
    
    
    
    public static function existView($model,$action,$module=false){
        
               
         $view = self::viewPath($model,$action,$module);             
    
            
        //Se a view existir
        return file_exists($view);       
             
        
    }
    
    
    public static function viewPath($model,$action,$module=false){
        
        $view = false;
        
      
        if($module){

             $view = APPLICATION_DIR.MODULES_DIR.'/'.strtolower($module).'/views/'.strtolower($model).'/'.strtolower($action).'.html';   //HMVC View
                    
        }else{
        
             $view = APPLICATION_DIR.'views/'.strtolower($model).'/'.strtolower($action).'.html';  //MVC View 
            
                        
        }
        
        
        return $view;
    }
    
    
    public static function direct_output($filename_or_html,$directhtml=false,$notemplate=false,$data=array()){
    
            $filename = $filename_or_html;
            $html = $filename_or_html;
        
            //Sem template
            if($notemplate){
                
                //HTML directo
                if($directhtml){

                    echo $directhtml;
                    
                }else{
                
                    //Ficheiro
                    readfile($filename);
                    
                    
                }
                
            }else{
                
                //Com template
        
                $bundle = new Bundle();
                $views_params = $data;
                $template_options = false; 

                $vcl = new VCL();
                $vcl->direct = true; //Conteudo directo
                
                //Informa ao VCL que nao se trata de um ficheiro e sim de html directo
                if($directhtml){
                    
                    $vcl->directhtml = true;
                
                }
                
                $vcl->prepareToRender($filename,$bundle,$template_options,$views_params);
                
            }
                
    
    }
    
    
    public static function appendToNext($string){
    
        self::$append[] = $string;
    
    }
    
    
     public static function preppendToNext($string){
    
        self::$preppend[] = $string;
    
    }
    
    
}