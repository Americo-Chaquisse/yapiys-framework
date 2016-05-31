<?php

/**
 * Description of AbstractDataMapper
 *
 * @author Mario Junior
 */
abstract class AbstractDataMapper {
    
    public static  $DATA_MAPPER_SQL=0;
    public static  $DATA_MAPPER_NoSQL=1;
    
    
    //Mapeadores de dados
    private static $mappers = array();
    
    
    /*
     * Obtem o tipo de mapeador
     */
    public function getType(){
        return self::$DATA_MAPPER_SQL;
    }


    /*
     * Regista um mapeador de dados 
     * Nome do mapeador, classe do mapedor e tipo de mapeador
     */
    public static function registerDataMapper($name,$classname){
                
        self::$mappers[$name] = $classname; 
        
    }
    
    public static function getMapperClass($name){
        
        //Existe o mapper
        if(isset(self::$mappers[$name])){
            
            return self::$mappers[$name];
            
        }
        
        return false;
        
    }
    
    
    /*
     * Obtem o nome do mapeador padrao
     */
    public static function getDefaultMapper(){
        
        if(count(self::$mappers)>0){
            
            return array_keys(self::$mappers)[0];
            
        }
        
    }
    
    
    /*
     * Retorna uma conexao pelo nome
     */
    public abstract function getConnection($name=false);  
    
    
    /*
     * O mapeador recebe as conexoes
     */
    public abstract function initialize($connections);
    
    
    
}