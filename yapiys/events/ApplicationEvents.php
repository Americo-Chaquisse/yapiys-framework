<?php


    class ApplicationEvents{
        
        static $ready=false;
        static $class_name = "_AppEventsController";
        static $instance = false;

        
        public static function init(){

            //Load de config data in /app/config/php folder

            $phpConfigPath = APPLICATION_DIR.APPLICATION_CONFIG_PHP_DIR;

            if(file_exists($phpConfigPath)){

                foreach (glob($phpConfigPath."*.php") as $filename)
                {
                    require_once($filename);
                }

            }


            $controller_filename = APPLICATION_DIR.ROOT_CONTROLLERS_DIR.'/'.self::$class_name.'.php';

            //Controlador da aplicacao existe
            if(file_exists($controller_filename)){
                
                //Inclui o controlador da aplicacao
                require_once $controller_filename;
                
                //Cria uma nova instancia do controlador da aplicacao
                self::$instance = new self::$class_name();
                
                //Considera o componente de eventos preparado
                self::$ready=true;
                        
                
            }
            
            
        }
        
        
        
        public static function handlesEvent($name){
            
            if(self::$ready){
                
                return method_exists(self::$class_name,$name);
                
            }
            
            return false;
            
        }
        

        private static function stopPropagation($response){

            if(is_bool($response)){


                return $response===FALSE;

            }

            return false;

        }

        private function updateParams($response){

            if(!is_null($response)&&!is_bool($response)){


                return true;

            }

            return false;

        }

        public static function fireEvent($name,$param=false){


            //O handler para o evento foi encontrado
            if(self::handlesEvent($name)){

                $returned = false;
                
                //Dispara o evento e passa os parametros
                if($param){
                    
                    $returned = self::$instance->$name($param);
                    
                }else{
                    
                    
                    //Dispara o evento sem parametros;
                   $returned = self::$instance->$name();

                    
                }

                //Stop event propagation
                if(ApplicationEvents::stopPropagation($returned)){

                    return;

                }
                
            }



            
            //Receptores de feed
            $feed_receivers = SystemEventsFeed::getSubscribers($name);
            
            
            //Existem receptores de feed
            if($feed_receivers){
                
                 
                //Informa cada um deles
                foreach ($feed_receivers as $feed_receiver){
                    
                    $returned = $feed_receiver->onReceived($name,$param);

                    //Stop event propagation
                    if(ApplicationEvents::stopPropagation($returned)){

                        return;

                    }
                    
                }
                
            }
            
            
        }
        
        
        
        
        
    }