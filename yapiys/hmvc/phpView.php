<?php
$uiBundle=NULL;
//$template_options=NULL;
$viewParams=false;
$viewHTML=false;
$view_filename =false;


function initView($filename,$UIBundle,$template_options_,$viewParams_){
    global $viewHTML,$template_options,$uiBundle,$viewParams,$view_filename;
    
   
    $content = false;    
    //$content = file_get_contents($filename);
    $view_filename=$filename;
    
 

    
    if($content){

        $viewHTML=$content;
        
    }
    

    $uiBundle=$UIBundle;
    
    //Nao foi definido o nome do template
    if(!$template_options_){
                
        //Template definido
        $definedTemplate = AppInstance::getTemplate();

        if(!$definedTemplate){
            $definedTemplate='main';
        }
        
        $template_options_ = $definedTemplate;
        
    }
    
    $template_options=$template_options_;
    $viewParams=$viewParams_;
    
    
    
}

/**
 * Retorna os dados passados para a View
 * @global Bundle $uiBundle
 * @return null
 */
function getUIBundle(){
    global $uiBundle;
    return $uiBundle;
}


function render(){
    global $viewHTML,$template_options,$uiBundle,$viewParams,$view_filename;

    $vcl = new VCL();

    $vcl->prepareToRender($view_filename, $uiBundle, $template_options,$viewParams);
}


function _continue(){
    render();
}

function proceed(){
    render();
}

/**
 * Define um callback para um evento. 
 * 
 * A URL da view serve de mecanismo de autenticacao de um callback.
 * Se o callback for enviado apartir de uma URL que nao corresponde a URL da view actual
 * Este sera descartado.
 * 
 * @param string $eventsNamespace espaco de nomes de eventos
 * @param string $eventSource nome do elemento ou componente que vai originar o evento
 * @param string $eventName Nome do evento
 * @param string $callbackName nome da funcao que servira de callback.
 */
function setEventCallback($eventsNamespace,$eventSource,$eventName,$callbackName){
    
    //Obtem o path do ficheiro php da view actual
    $phpFilePath = AppInstance::$current_view_php_file;
    
    //Parametros validos
    if($eventsNamespace&&$eventSource&&$eventName&&$callbackName&&$phpFilePath){
        
        //Gera um nome identificador do callback
        $callback_identifier_name=$eventSource.'::'.$eventName;
        
        //Gera um hash indentificador do callback
        $callback_hash_identifier= md5(uniqid($callback_identifier_name));
    
        //Previnir duplicacao de callback
        if(callback_prevent_duplications($callback_identifier_name)){
            
            return;
        }
        
        //Regista o callback pelo seu nome indentificador
        AppInstance::getUserSessionBundle()->updateArrayValue('callback_namespace_'.$eventsNamespace,$callbackName,$callback_identifier_name);

        //Regista a identificador hash do callback no mapa de callbacks
        AppInstance::getUserSessionBundle()->updateArrayValue('callbacks_map', $callback_identifier_name,$callback_hash_identifier);
                
        //Regista a funcao de callback e seu repectivo ficheiro php no mapa de funcoes
        AppInstance::getUserSessionBundle()->updateArrayValue('functions_map', $phpFilePath,$callbackName);
            
    }        
            
}

/**
 * Define callback para o evento de um elemento
 * @param q $element Elemento pQuery
 * @param type $eventName nome do evento
 * @param type $callback_name nome do callback
 */
function setElementEventCallback($element,$eventName,$callback_name){
    
    if($element&&$eventName&&$callback_name){
        if($element->hasAttr('id')){
            $element_id = $element->attr('id');
            setEventCallback('elements', $element_id, $eventName, $callback_name);
        }
    }
    
}

   /**
     * Remove callbacks com o mesmo nome identificador
     * @param type $cb_identifier_name
     */
   function callback_prevent_duplications($cb_identifier_name){
       
       //Obtem o mapa de callbacks
        $callbaks_map= AppInstance::getUserSessionBundle()->get('callbacks_map');  
        
        //Mapa de callbacks  eh nulo
        if(is_null($callbaks_map)||$callbaks_map==false){
            
            return FALSE;
        }
        
        //MAPA DE CALLBACKS VALIDO        
        
        //Ja existe um callback com mesmo nome identificador
         if(count($callbaks_map)>0){
             
            $found = array_search($cb_identifier_name, $callbaks_map);
            
         }else{
             return FALSE;
         }

          
        return $found;
        
    }
    
/**
 * Coloca o callback offline ate que ele seja activado por outra visualizacao de pagina.
 * Esta funcao deve ser usada para bloquear re-submissao de formularios
 */    
function put_callback_offline(){
    if(AppInstance::$callback_in_execution){
        AppInstance::$callback_in_execution=false;
        CallbacksRouter::disableCallback(AppInstance::$callback_in_execution_namespace, AppInstance::$callback_in_execution_id);
    }
}