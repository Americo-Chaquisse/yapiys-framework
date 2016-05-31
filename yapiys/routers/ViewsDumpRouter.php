<?php

/**
 * Class ViewsDumpRouter
 * This router is responsible for giving response to urls in format '@view_dump/module/controller/action'.
 *
 * Its Function is to supply the views html files according to user permissions. The user never dumps a view that
 * Has no access to it.
 *
 * Dump URLS are used to retrieve extended views parent HTML if not present in cache.
 *
 */
class ViewsDumpRouter extends Router {
    
    public function routeExists($contextable,$url){
                    
          $url_parts = explode('@view_dump',$url); 
    
            if(count($url_parts)>1){

                $view_url_parts = explode('/',$url_parts[1]);

                
                if(count($view_url_parts)==3){
                    
                    
                    $model = $view_url_parts[1];
                    
                    $view = $view_url_parts[2];
                    
                    
                    if($this->existView($model,$view)){                    


                        $path = $model.'/'.$view;

                        //User has access to this route
                        if(AppUser::isAllowed($path)) {

                            return array('model' => $model, 'view' => $view, 'block_connection' => true);

                        }

                        //User has no access to this route
                        return false;
                        
                    }
                    
                       
                }else if(count($view_url_parts)==4){
                    
                    
                    $model = $view_url_parts[2];
                    
                    $view = $view_url_parts[3];
                    
                    $module = $view_url_parts[1];
                    
                    
                    if($this->existView($model,$view,$module)){

                         $path = $module.'/'.$model.'/'.$view;

                         //User has access to this route
                         if(AppUser::isAllowed($path)) {

                             return array('model' => $model, 'view' => $view, 'module' => $module);
                         }

                        //User has no access to this route
                        return false;

                    }

                }


                
                return false;                
                
                
            }else{
                return false;   
            }
                
      
    }

    
    public function getView($model,$action,$module=false){
        
        
         $view = $this->viewPath($model,$action,$module);        
                

        if(file_exists($view)){


            return file_get_contents($view);  
            
            
        }               
             
        
    }
    
    
    
    public function existView($model,$action,$module=false){
        
               
         $view = $this->viewPath($model,$action,$module);             

        return file_exists($view);       
             
        
    }
    
    
    public function viewPath($model,$action,$module=false){
        
        $view = false;
        
      
        if($module){

             $view = APPLICATION_DIR.MODULES_DIR.'/'.strtolower($module).'/views/'.strtolower($model).'/'.strtolower($action).'.html';   //HMVC View
                    
        }else{
        
             $view = APPLICATION_DIR.'views/'.strtolower($model).'/'.strtolower($action).'.html';  //MVC View 
            
                        
        }
        
        
        return $view;
    }


    public function route($params) {
    
     
        $model = $params['model'];
        $view  = $params['view'];
        $module = false;
        
        
        if(isset($params['module'])){
            
            $module = $params['module'];
            
        }
        
        $view = $this->getView($model,$view,$module);        
        
        
        ob_start();
       
        ob_clean();
        


        ApplicationEvents::fireEvent('before_view_dump_output');
        header('Content-Type: text/html');

        if(AppInstance::isOnline()){


            Framework::loadLibrary('minify',true);

            $minOutput = Minify_HTML::minify($view, array(
                'cssMinifier' => array('Minify_CSS', 'minify')
            ,'jsMinifier' => array('JSMin', 'minify')
            ));

            echo $minOutput;


        }else{

            echo $view;

        }


            
        ob_end_flush();
       
    }


}