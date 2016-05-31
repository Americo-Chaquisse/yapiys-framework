nt<?php


/**
 * Description of Module
 *
 * @author Mario Junior
 */
class Module {
    
    
    public function __construct() {
        
    }

      
    /**
     * Chama um controlador do Modulo e renderiza a View eh um light View
     * @param type $name
     * @param type $params
     */
    
    public function callControllerAction($controller_name,$action_name,$params){
        
                
    }
    
    
    //Retorna todos os provedores deste tipo de dados
    public function getDataProviders($data_type){
                
        return false;
        
    }
    
    
    //Retorna conteudo que este modulo fornece a outros modules 
    public function provideDataToModules(){
        
        return false;
        
    }
        
    
    
}