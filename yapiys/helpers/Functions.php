<?php


/**
 * Description of Functions
 *
 * @author Mario Junior
 */
class Functions {
   
    public static function json_to_array($json_object){
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
    
    
    public static function isAjaxRequest(){
        if(VCL::$emulateEntrance){

            return false;

        }

        return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';    
    }




    
    
    
}



function json_to_array($json_object){
       $array=array();
       $reflectJSON = new ReflectionObject($json_object);
       $properties=$reflectJSON->getProperties();
                         
        foreach ($properties as $property) {
            $name=$property->getName();
            $value=$property->getValue($json_object);
            
            if(is_object($value)){
                
                $array[$name]=json_to_array($value);
                
            }else if(is_array($value)){
                $new_array=convert_json_objects_in_array_to_arrays($value);
                $array[$name]=$new_array;
                
            }            
            else{                
                $array[$name]=$value;
            }
            
            
        }
        
        return $array;
    }
    
    
    function convert_json_objects_in_array_to_arrays($array){
        $new_array=array();
        foreach ($array as $key => $value) {
            
            if(is_object($value)){
                
               $new_value=json_to_array($value);
               $new_array[$key]=$new_value;
                
            }else if(is_array($value)){
                
                $new_value=convert_json_objects_in_array_to_arrays($value);
                 $new_array[$key]=$new_value;
                 
            }else{
                
                $new_array[$key]=$value;
            }
            
            
        }
        
        
        return $new_array;
    }



    


function commaGlue($values,$prefix='',$index=0,$outter_left_char="",$outter_right_char=""){
    $commaString="";
    $allValuesString="";
        
    
    //Valores e indice validos
    if($values&&$index>-1&&$index<2){
        
        //Primeiro valor 
        $firstValue=true;
        
        
        //Para cada item
        foreach ($values as $key => $value) {
            
            if($firstValue===false){
                
                $allValuesString=$allValuesString.',';
                
            }else{
                
                $firstValue=false;
            }
            
            
            if($index==0){
                
                $allValuesString=$allValuesString.$outter_left_char.$prefix.$key.$outter_right_char;
                
            }else if($index==1){
                
                $allValuesString=$allValuesString.$outter_left_char.$prefix.$value.$outter_right_char;
            }
            
        }
           
     
        $commaString=$allValuesString;        
        return $commaString;
        
    }else{
        
        return "";
    }
}


function pdoParams($pdo_params){
    $params = array();
    
    foreach($pdo_params as $pdo_param_name => $pdo_param_value){
         $params[":".$pdo_param_name]=$pdo_param_value;   
    }
    
    return $params;
}

function pdoColumns($values){
    return commaGlue($values);
}   

function loadLib($lib_name,$module_name=false){
    $module=false;
    
    
    
    if($module_name){
      
        $module=$module_name;   
        
    }
    

    $lib_filename1 = false;
    
    $lib_filename2 = false;
    
    $lib_filename3 = false;
    
     
    if($module){
    
        $lib_filename1 = APPLICATION_DIR.'modules/'.strtolower($module).'/'.LIBRARIES_DIR.strtolower($lib_name);
        $lib_filename2 = $lib_filename1.'/'.ucfirst($lib_name).'.php';
        $lib_filename3 = $lib_filename1.'/loader.php';


        if(file_exists($lib_filename2)){

            require_once($lib_filename2);
            return;

        }

        if(file_exists($lib_filename3)){


            require_once($lib_filename3);
            return;

        }
        
        
    }else{


        //Biblioteca da raiz
        $lib_filename1 = APPLICATION_DIR.LIBRARIES_DIR.strtolower($lib_name);
        $lib_filename2 = $lib_filename1.'/'.ucfirst($lib_name).'.php';
        $lib_filename3 = $lib_filename1.'/loader.php';



        $f_ = VirtualApp::file_exists(LIBRARIES_DIR.strtolower($lib_name));


        if($f_){


            $f1 = $f_.'/'.ucfirst($lib_name).'.php';
            $f2 = $f_.'/loader.php';

            if(file_exists($f1)){

                require_once $f1;

            }else if(file_exists($f2)){

                require_once $f2;

            }






        } else {

            print_r('LIB_'.$lib_name.'_NOT_FOUND');

            exit();

        }



        return;
        
    }
    

    
    
}


function isPostRequest(){
    
    return $_SERVER['REQUEST_METHOD'] === 'POST';
    
}


function startsWith($haystack, $needle) {
    
    return substr($haystack, 0, strlen($needle)) === $needle;
    
}

function loadModels($module=false){
    if($module){
        
        Router::loadModuleModels($module);  
        
    }else{
        
        Router::loadAppModels();
        
    }
}

function loadModel($name,$module=false){
    
    Router::loadModel($name,$module);

}


function getSession(){
    
    return AppInstance::getSession();
    
}

function pdoValues($values){
    $pdo_values=false;   
    $pdo_values = commaGlue($values,":");
    return $pdo_values;
}

function loadConfiguration($name){


     //Caminho de ficheiro de configuracao
    $file = APPLICATION_DIR.APPLICATION_CONFIG_DIR.$name.'.json';



    //Ficheiro existe
    if(file_exists($file)){

        $caching_name = 'config_'.$name;

        //Pega o ficheiro de configuracao da cache
        $config_file_content = CacheInSession::get($caching_name);

        if(!isset($_SESSION["configs_server_name"])){

            $_SESSION["configs_server_name"] = $_SERVER["SERVER_NAME"];
            $config_file_content = false;

        }

        $server_name_from_session =  $_SESSION["configs_server_name"];
        $server_name_from_request =  $_SERVER["SERVER_NAME"];

        if($server_name_from_request!==$server_name_from_session){

            $config_file_content = false;

        }


        //Ficheiro de configuracao nao esta na cache
        if(!$config_file_content){


            $config_file_content = file_get_contents($file);
            $_SESSION["configs_server_name"] = $_SERVER["SERVER_NAME"];

            //Coloca a configuracao na cache
            CacheInSession::push($caching_name,$config_file_content);

        }



        return json_decode($config_file_content);

    } else {
        //die('Configuration file not found. You need to create the <b>'.$name.'.json</b> file and fill it accordingly');
    }

}



function redirect($model,$action=false,$params=false,$module=false,$context=false){
    global $appSwitch;

    if(!$action){

            //Bind url do GET array
            $_GET['route_url']=$model;

            //VCL::setCommand('$url',$model);
            $appSwitch->newRequest($model);

        exit();

    }


    
    $params_string ='';
    $context_string = '';
    $module_string='';
    
    if(!$context){
        
        $context = AppInstance::getContext();
        
    }
    
    
    
    if($context){
        
        $context_string=$context.'/';   
        
    }
    
    if($module){
        
        $module_string=$module.'/';
        
    }
    
    if(is_array($params)){
        
        $params_string='/'.implode('/',$params);
        
    }
    
    
    $url =$context_string.$module_string.$model.'/'.$action.$params_string;
    
    //Bind url do GET array
    $_GET['route_url']=$url;


    //VCL::setCommand('$url',$url);
    
    
    //Bind params to GET array
    if(is_array($params)){
        
        foreach($params as $key=>$value){
            
            $_GET[$key]=$value;            
            
        }        
        
    }

    
    $appSwitch->newRequest($url);
        
    
}


function mime_content_type_($filename) {
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


function viaCLI(){
    
    if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name])){
        
        return $_SESSION[UserSessionBundle::user_data_session_variable_name]['cli'];
        
    } 
    
}

function debug($data,$message='Debug',$level=0){
    
    DebugChannel::message($data,$message,$level);
    
}


/**
 * Loads a Module Border
 * @param $name module name.
 * @param $caller who is calling the module.
 */
function requireModule($name,$caller=false){

    if(ModuleBorder::isModuleAvailable($name)){

        return ModuleBorder::getModule($name,$caller);

    }

    return false;

}


