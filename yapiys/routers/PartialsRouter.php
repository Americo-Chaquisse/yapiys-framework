<?php

/**
 * Class PartialsRouter
 * This router is responsible for responding to urls in format '$partials/module/partial_name'
 *
 * Partials are components that belong to a module. Each partial has a javascript controller. The javascript
 * is included in Yapiys.js. The views come from this router.
 *
 * There is no access check in this router. Every partial is available to everyone. Thus, is important
 * to develop the partials with precaution.
 *
 * Each module has a directory [partials] that contains the partials html files and also contains another directory  that
 * contains the partials javascript controllers.
 *
 */
class PartialsRouter extends Router {
    
    var $directories=array(
      'uidir'=>"UI" 
    );
    
    
    public function routeExists($contextable,$url){

        if(true){

            $url_parts = explode('$partials',$url);


            //Retrieving Partial HTML
            if(count($url_parts)>1) {

                $url_parts = explode('/', $url_parts[1]);


                if(count($url_parts)<2){

                    return false;

                }

                if(count($url_parts)<5&&count($url_parts)>2) {

                    $module_name = $url_parts[1];

                    $partial_name = $url_parts[2];

                    $filename = APPLICATION_DIR . MODULES_DIR . '/' . $module_name . '/partials/' . $partial_name . '/view.html';


                    if (file_exists($filename)) {

                        return array('filename' => $filename, 'block_connection' => true);


                    } else {

                        return false;
                    }


                //Root partial
                }else if(count($url_parts)==2){

                    $partial_name = $url_parts[1];

                    $filename = APPLICATION_DIR . PARTIALS_DIRECTORY. '/' . $partial_name .'/view.html';

                    if (file_exists($filename)) {

                        return array('filename' => $filename, 'block_connection' => true);


                    } else {

                        return false;
                    }


                    //Calling an API function
                }else if(count($url_parts)==6||count($url_parts)==5&&$url_parts[1]==='ws'){


                    $module_name = false;

                    $partial_name = false;

                    $APIName = false;

                    $api_filename = false;

                    $APIAction = false;

                    $APIClassName = false;


                    if(count($url_parts)==6) {

                        $module_name = $url_parts[2];

                        $partial_name = $url_parts[3];

                        $APIName = $url_parts[4];

                        $APIAction = $url_parts[5];

                    }else{

                        $partial_name = $url_parts[2];

                        $APIName = $url_parts[3];

                        $APIAction = $url_parts[4];

                    }


                    $APIClassName = ucfirst($APIName).'API';


                    if(count($url_parts)==6){

                        $api_filename = APPLICATION_DIR . MODULES_DIR . '/' . $module_name . '/partials/' . $partial_name.'/API/'.$APIClassName.'.php';

                    }else{

                        $api_filename = APPLICATION_DIR .PARTIALS_DIRECTORY .'/'.$partial_name.'/API/'.$APIClassName.'.php';


                    }


                   //API file exists
                    if(file_exists($api_filename)){

                        require_once $api_filename;

                        $apiRouter = AppInstance::$switch->getRouter('APIRouter');

                        $params = $apiRouter->getWebserviceParams();

                        $api_params = array(
                            'module'=>$module_name,'partial_name'=>$partial_name,
                            'loaded'=>true,'class'=>$APIClassName,
                            'partial'=>true,'params'=>$params,'action'=>$APIAction
                        );

                        AppData::initialize();
                        $apiRouter->route($api_params);


                    }


                }



                        
            }else{

                return false;

            }
                
            
        }
    }
    
    
    public function readyToRoute() {
        return isset($_GET['dir'])&& isset($_GET['url']);
    }

    public function route($params) {
             
        ob_start();

        //sleep(2);

        $filename=$params['filename'];

        if(!file_exists($filename)){
            http_response_code(404);
            return;
        }

        $MIMEType = $this->mime_content_type($filename);
        
        ob_clean();
        

        ApplicationEvents::fireEvent('before_partial_view_output');
        header('Content-Type: text/html');

        readfile($filename);
        
        ob_end_flush();
    }


    private function mime_content_type($filename) {
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


}