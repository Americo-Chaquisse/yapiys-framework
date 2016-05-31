<?php

/**
 * Description of ViewController
 *
 * @author Mario Junior
 */
abstract class AppController {
    private $viewName=null; 
    private $moduleName=null;
    private $modelName=null;
    private $actionName=null;
    private $templateBundle=null;
    private $viewParams=null;
    public $accessLevel=-1;
    public $jsEvents =false;
    public $data =  false;
    private $currentDir=null;


    function __construct() {

        $this->data = new Bundle();
    }



    public function setCurrentDirectory($dir){
        $this->currentDir=$dir;
    }

    public function getCurrentDirectory(){
        return $this->currentDir;
    }

    /**
     * Rotina que deve ser executada antes de cada accao
     */
    public function beforeAction(){

    }


    /*
     * Rotina que deve ser executada depois de cada accao
     */
    public function afterAction(){

    }
           

    public function throwHttp404(){

        AppInstance::getAccessManager()->http_notFound(AppSwitch::$current_url);
        exit();

    }


    public function setViewParams($params){
        $this->viewParams=$params;
    }
    
    public function setModelName($model){
        $this->modelName=$model;
    }
    /**
     * Define o nome da view que deve ser futuramente chamada
     * @param type $name Nome da View
     */
    public function setViewName($name){
        $this->viewName=$name;   
    }
  
    
    public function setModuleName($mod){        
        $this->moduleName=$mod;
    }
    
    public function setCurrentAction($action){
        $this->actionName=$action;
    }
    
    /**
     *
     * @param type $viewContentProvider Provedor de conteudo da View.
     * @param type $options Objecto JSON que deve ser passado para o Controlador do Template.
     */
    public function callView($viewContentProvider=false,$template_options=false){
        
        
        if(!$viewContentProvider){
            $viewContentProvider=  $this->data;
        }
        
        $htmlViewFilename="";
        $viewContentProvider->set('$access_level',  $this->accessLevel);        
       
        $viewDirectory=VCL::generateViewURL($this->moduleName, $this->modelName, $this->actionName);
        $viewHTML=$viewDirectory.'.html';
        
               
        
        
        
        //se tiver sido criada uma pasta para a view
        if(file_exists($viewHTML)){

            $htmlViewFilename=$viewHTML;
            
        }else{
            //Nao existe pasta para a view
            $htmlViewFilename=  $viewDirectory.'.html'; //Ficheiro de view HTML
            
        }
        
        
         
  
                
        //Ambos os ficheiros de View devem existir
        if(file_exists($htmlViewFilename)){


            require_once 'phpView.php';            
            //Passa o UIBundle/ViewContentProvider e as opcoes para o ficheiro de view PHP
                       
            initView($htmlViewFilename, $viewContentProvider, $template_options,$this->viewParams);
  
            //Caso o ficheiro de view php nao exista Renderiza a view
            render();
                   
        }else{

            throw new Exception('View file not found : '.$htmlViewFilename);
            //Um ou ambos ficheiros de views nao existe
            return;
        }    
        
    }
    
    
    
    public function showView(){
               
        $htmlViewFilename="";
        //$phpViewFilename=$this->generateViewFilename($this->getViewName().'/index.php'); //Fiheiro de view PHP
        
        $viewDirectory=VCL::generateViewURL($this->moduleName, $this->modelName, $this->actionName);
        $viewHTML=$viewDirectory.'.html';
        
        $phpViewFilename=$viewDirectory.'/index.php'; //Fiheiro de view PHP
              
        //se tiver sido criada uma pasta para a view
        if(file_exists($viewHTML)){
            $htmlViewFilename=$viewHTML;
        }else{
            //Nao existe pasta para a view
            $htmlViewFilename=  $viewDirectory.'.html'; //Ficheiro de view HTML
        }
        
                
        //Ambos os ficheiros de View devem existir
        if(file_exists($htmlViewFilename)){
                  
                readfile($htmlViewFilename);               
        
            
        }else{

            throw new Exception('View file not found : '.$htmlViewFilename);
            die('Erro: '.$htmlViewFilename);
            //Um ou ambos ficheiros de views nao existe
            return;
        }    
        
    }
    
    
    public function loadLib($name){        
        return loadLib($name,$this->moduleName);
    }
    
    public function setTemplateData($bundle){
        $this->templateBundle=$bundle;
    }
    
    public function defineTemplateData($bundle){
        $this->templateBundle=$bundle;
    }
    
    private function getViewName(){
        return $this->actionName;
    }
    
    private function generateViewFilename($name=null){
        $app_dir_path_is_present_in_url=false;        
        $app_dir_name_legth=strlen(APPLICATION_DIR);
        $current_directory=  $this->getCurrentDirectory();
        
        if(strlen($current_directory)>$app_dir_name_legth){
             $current_directory_chuck= substr($current_directory, 0,$app_dir_name_legth);
             $app_dir_path_is_present_in_url=(APPLICATION_DIR===$current_directory_chuck);
        }
       
        
        if(!$app_dir_path_is_present_in_url){
            $dir=APPLICATION_DIR.$this->getCurrentDirectory().'views/';
        }else{
            $dir=$this->getCurrentDirectory().'views/'; 
        }        
    
        
        if($name){
            return $dir.$name;
        }
        
        return $dir.$this->actionName;
    }


    function __get($key){

        if(ucfirst($key)==='Module'){

            return $this->getModule();

        }

        return false;

    }


    public function getModule(){

        return requireModule($this->moduleName);

    }

    
}