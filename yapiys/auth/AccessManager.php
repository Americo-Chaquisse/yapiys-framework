<?php


/**
 * Description of AcessManager
 *
 * @author Mario Junior
 */
class AccessManager {
    private $acm_bundle=null;
    
    public function __construct() {
        
        //Bundle do access manager
        $this->acm_bundle=new AcessManagerBundle();
         
    }
 
    
    /**
     * Obtem o bundle do gestor de acessos
     * @return AcessManagerBundle
     */
    public function getBundle(){
        return $this->acm_bundle;
    }
    
    
    //Permote acesso uma rota
    public function allow($route_or_module,
                          $controller_or_access_level=false,
                          $action=false,$route_access_level=0){
        
        
        
        $route_to_authorize = false;
        $access_level = 0;
        
        
        //URL HMVC ou HMVC especificada
        if(!$action){
            
            //Rota
            $route_to_authorize=$route_or_module;
            
            //Nivel de acesso
            if($controller_or_access_level){
                $access_level = $controller_or_access_level;
            }
            
            
        }else if($action&&$controller_or_access_level){
            
            //Nivel de acesso
            $access_level=$route_access_level;
            
            $route_array = array();
            
            //Foi especificado um modulo
            if($route_or_module){
                $route_array[] = $route_or_module;    
            }
            
            //Controlador e accao sao obrigatorios
            $route_array[] = $controller_or_access_level;
            $route_array[] = $action;
            
            //Rota
            $route_to_authorize= implode('/',$route_array);
            
        }
        
        
        
        if($route_to_authorize){            
            
            $this->acm_bundle->updateArrayValue('access_map',$access_level,$route_to_authorize);
            
        }  
        
        
    }


    public function cleanPermissions(){

        $this->acm_bundle->set('access_map',array());

    }
    
    
    
    
    /*
     * Obtem a rota wildcard
     */
    private function getActionWildcardRoute($route_string){
        
        $route_parts = explode('/', $route_string);
        $total_parts = count($route_parts);
        $last_part_index = $total_parts-1;
        $route_parts[$last_part_index]='*';
        return implode('/', $route_parts); 
        
    }
    
    
      /*
     * Obtem a rota wildcard
     */
    private function getModuleWildcardRoute($hmvc_route_string){
        
        $route_parts = explode('/', $hmvc_route_string);
        $total_parts = count($route_parts);
        $module_part_index = $total_parts-2;
        $route_parts[$module_part_index]='*';
        unset($route_parts[$total_parts-1]);        
        return implode('/', $route_parts); 
        
    }
    
    

    private function isHMVC($route_string){
        
        $route_parts = explode('/', $route_string);
        return count($route_parts)==3;
    }
    
     private function isMVC($route_string){
        
        $route_parts = explode('/', $route_string);
        return count($route_parts)==2;
        
    }

    private function allowBySettings($url){
        //Carrega ficheiro de configuracao de rotas
        $rconfig = loadConfiguration('routing');


        //Existem configuracoes de rotas sem contexto
        if(property_exists($rconfig,'no_context')){

            //Rotas que nao precisam de contexto
            $no_context_routes = $rconfig->no_context;

            //A presente rota nao precisa de contexto
            if(in_array($url,$no_context_routes)){


                //Acesso permitido
                AppUser::allow($url,0);


            }


        }

    }
    
    public function userHasAccessTo($route_or_module,
                          $controller_or_access_level=false,
                          $action=false){
     
        
         $route_to_check = false;
     


        //URL HMVC ou HMVC especificada
        if((!$action)&&(!$controller_or_access_level)){
            
            //Rota
            $route_to_check=$route_or_module;          
            
            
        }else if($action&&$controller_or_access_level){
         
            $route_array = array();
            
            //Foi especificado um modulo
            if($route_or_module){
                $route_array[] = $route_or_module;    
            }
            
            //Controlador e accao sao obrigatorios
            $route_array[] = $controller_or_access_level;
            $route_array[] = $action;
            
            //Rota
            $route_to_check= implode('/',$route_array);
            
        }


        if($route_to_check){

            $this->allowBySettings($route_to_check);
                     
            //Dispara o evento [antes da verificacao de acesso]
            ApplicationEvents::fireEvent('beforeAccessCheck',$route_to_check);   
            
            
            $rota_wildcard_modulo = false;
            $rota_wildcard_accao = false;
            
            //Trata-se de uma rota HMVC
            if($this->isHMVC($route_to_check)){
                
                  $rota_wildcard_modulo = $this->getModuleWildcardRoute($route_to_check);                  
                  $rota_wildcard_accao = $this->getActionWildcardRoute($route_to_check); 
                
                
            }else if($this->isMVC($route_to_check)){
                
                //Trata-se uma rota MVC
                 $rota_wildcard_accao = $this->getActionWildcardRoute($route_to_check); 
                
                
                
            }


            //ROTAS HMVC
            if($rota_wildcard_accao&&$rota_wildcard_modulo){
               
                //Nivel de acesso do wildcard do modulo
                $module_wilcard_access_level = $this->getWilcardAccessLevel($rota_wildcard_modulo);
                
                //Nao foi definido um nivel de acesso via wilcard do modulo
                if($module_wilcard_access_level==-2){
                     
                    
                    //Verifica nivel de aceso usando wilcard da accao
                    $action_wildcard_access_level = $this->getWilcardAccessLevel($rota_wildcard_accao);
                    
                    
                    //Nao foi definido um nivel de acesso para o wildcard da accao
                    if($action_wildcard_access_level==-2){
                                                
                        
                    }else{
							
						//Foi definido nivel de acesso para o wildcard da accao
                        return $action_wildcard_access_level>=0;
                        
                    }
                    
                    
                    
                }else{
                    
                    //Foi definido nivel de acesso para o wildcard do modulo
                    return $module_wilcard_access_level>=0;
                    
                    
                }
                
                
            }

                       
            //Obter o nivel de acesso usando a ROTA completa
            $access_level = $this->acm_bundle->getArrayValue('access_map',$route_to_check);



			//Nivel de acesso definido
            if(is_int($access_level)){
                
				
                return $access_level>=0;
                
            }
			
			//NIVEL DE ACESSO NAO DEFINIDO
			//ACESSO NEGADO

            /**
             * Trabalhar com o gestor de acessos
             */
            return true;
               
        }
        
    }
    
    
    
    public function getUserAccessLevel($route_or_module,
                          $controller_or_access_level=false,
                          $action=false){
     
        
         $route_to_check = false;
     
        
        //URL HMVC ou HMVC especificada
        if((!$action)&&(!$controller_or_access_level)){
            
            //Rota
            $route_to_check=$route_or_module;          
            
            
        }else if($action&&$controller_or_access_level){
         
            $route_array = array();
            
            //Foi especificado um modulo
            if($route_or_module){
                $route_array[] = $route_or_module;    
            }
            
            //Controlador e accao sao obrigatorios
            $route_array[] = $controller_or_access_level;
            $route_array[] = $action;
            
            //Rota
            $route_to_check= implode('/',$route_array);
            
        }

        if($route_to_check){
            
            
            $access_level = $this->acm_bundle->getArrayValue('access_map',$route_to_check);
                       
            if(is_int($access_level)){
                
                return $access_level;
                
            }
            
            return -1;
               
        }
		
		return -1;
        
    }
    
    
     public function getWilcardAccessLevel($route_or_module){
     
        
        $route_to_check=$route_or_module;          
                   

        if($route_to_check){
            
            $access_level = $this->acm_bundle->getArrayValue('access_map',$route_to_check);
            
            if(is_int($access_level)){
                
                return $access_level;
                
            }
            
            //Nao foi definido um nivel de acesso via wildcard
            return -2;
               
        }
        
    }
    
    //Bloqueia uma rota
    public function block($route_or_module,
                          $controller_or_access_level=false,
                          $action=false){
        
        
        
         
        $route_to_authorize = false;
     
        
        //URL HMVC ou HMVC especificada
        if((!$action)&&(!$controller_or_access_level)){
            
            
            //Rota
            $route_to_authorize=$route_or_module;          
            
            
        }else if($action&&$controller_or_access_level){
         
            $route_array = array();
            
            //Foi especificado um modulo
            if($route_or_module){
                $route_array[] = $route_or_module;    
            }
            
            //Controlador e accao sao obrigatorios
            $route_array[] = $controller_or_access_level;
            $route_array[] = $action;
            
            //Rota
            $route_to_authorize= implode('/',$route_array);
            
        }
                
        
        if($route_to_authorize){            
            
            $this->acm_bundle->updateArrayValue('access_map',-1,$route_to_authorize);
            
        }  
        
        
        
        
    }
    

    public function setConfigData($connection){
         $this->acm_bundle->setDbconfig($connection);         
    }
    
    
    public function getConfigData(){
         return $this->acm_bundle->getDbconfig();    
    }

    
    private function outputErrorHtml($number){
       
        $filename = APPLICATION_DIR."views/errors/$number.html";
        
        
        //Trata-se de uma requisicao AJAX
        if(Functions::isAjaxRequest($filename)){
            
            $filename = APPLICATION_DIR."views/errors/ajax_$number.html";
             
            $bundle = new Bundle();
            $views_params = array();
            $template_options = false; 

            $vcl = new VCL();
            $vcl->prepareToRender($filename,$bundle,$template_options,$views_params);
            
            
        }else{
            
            //Trata-se de uma requisicao de rota de entrada            
            readfile($filename);
            
        }
       
        
    }
    
    public function http_notFound($url=false){

        if(!$url){

            $url = $_GET['route_url'];

        }
       
        ApplicationEvents::fireEvent('pageNotFound',$url); 
        $this->outputErrorHtml(404);
        
    }
    
    
    public function http_internalError($url=false){

        if(!$url){

            $url = $_GET['route_url'];

        }

        ApplicationEvents::fireEvent('internalServerError',$url); 
        $this->outputErrorHtml(500);       
        
    }
    
    public function http_accessForbidden($url=false){

        if(!$url){

            $url = $_GET['route_url'];

        }
        
       ApplicationEvents::fireEvent('accessForbidden',$url);        
       $this->outputErrorHtml(403);
        
    }


    
        
   
}


//WebService com verificacao de csrf
interface CSRFCheck {



}