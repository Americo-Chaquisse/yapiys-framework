<?php 
class AssetsRouter extends Router {
    
    var $directories=array(
      'uidir'=>"UI" 
    );
    
    
    public function routeExists($contextable,$url){
        
        //if($contextable){  
            
            $assets_detector = "webroot/";
            
            $url_parts = explode($assets_detector,$url); 
            
            if(count($url_parts)>1){
                
                //Trata-se de um asset
                $asset_filename= $url_parts[1];
                
                //Tamanho do detector
                $detector_length = strlen($assets_detector);
                
                $detector_position = strpos($url,$assets_detector);
                
                $pre_asset_filename = substr($url,$detector_position,
                                             strlen($url)-$detector_position);
                
                $asset_filename = str_replace($assets_detector,'',$pre_asset_filename);        
                //URL do asset
                //$filename=APPLICATION_DIR.TEMPLATES_DIR.'main/assets'.$asset_filename;
                
                $filename=APPLICATION_DIR.TEMPLATES_DIR.$asset_filename;
                
                return array('filename'=>$filename,'block_connection'=>true);                
                
                
            }else{
                return false;   
            }
                
            
        //}
    }
    
    
    public function readyToRoute() {
        return isset($_GET['dir'])&& isset($_GET['url']);
    }

    public function route($params) {
             
        ob_start();
        
        //Asset filename
        $filename=$params['filename'];
        
        //Erro 404 se o ficheiro nao existir.
        if(!file_exists($filename)){
            http_response_code(404);
            return;
        }
        
        //Obtem o tipo MIME
        $MIMEType = mime_content_type_($filename); //Obtem o mime Type do ficheiro
        
        ob_clean();//Limpa o buffer de saida     


        //Fires the before assets output event : for caching headers and other stuff
        ApplicationEvents::fireEvent('before_asset_output',array('file'=>$filename,'type'=>$MIMEType));

        header('Content-Type:'.$MIMEType); //Manda o Mime-Type do ficheiro

        $content = file_get_contents($filename); //Faz leitura do ficheiro


        if(AppInstance::isOnline()) {

            switch ($content) {

                case 'text/css ':

                    Framework::loadLibrary('minify', true);
                    $cssmin = new CSSmin();
                    $minOutput = trim($cssmin->run($content));
                    echo $minOutput;

                    break;

                case 'text/javascript' :

                    Framework::loadLibrary('minify', true);
                    $minOutput = JSMin::minify($content);
                    echo $minOutput;

                    break;

                default :

                    echo $content;
                    break;


            }
        }else{

            echo $content;

        }
        
		if(trim($content)===""){
			
			echo "Nothing";
			
		}
        ob_end_flush(); //Envia todo conteudo do buffer de saida
    }



    public function isUserAllowedToAccessRoute($array) {
        return FALSE;
    }




}