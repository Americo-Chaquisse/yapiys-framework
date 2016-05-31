<?php

class AppData{
        
public static $connections = array(); 
private static $pdo_connections=array();
private static $currentDataConnectionName=false;
    
private static $connect_once_config=false;
private static $ready = false;   
    
    
public static function getConnections(){

    return self::$connections;
        
}   

/*
 * Mapeador de dados activo
 */
public static $active_mapper_name = 'php-activerecord';

public static $active_mapper_instance = false;

/*
 * Inicializa o mapeador de dados
 */
public static function initializeDataMapper(){
    
   
    //Obtem a classe do mapeador pelo nome
    $mapper_class = AbstractDataMapper::getMapperClass(self::$active_mapper_name);
    
    //Classe de mapeador valida
    if($mapper_class){
        
        
        //A classe do mapeador eh filha de AsbtractDataMapper
        if(is_a($mapper_class, 'AbstractDataMapper', true)){
            
                
            $instance = new $mapper_class();
            self::$active_mapper_instance = $instance;
            
            //Inicializa o mapeador de dados
            $instance->initialize(self::$connections);
            
            
        }else{
            
            //A classe de mapeador de dados nao extende AsbtractDataMapper
            
            
        }
        
        
        
    }
    
    
}

public static function available(){
    
    return self::$ready;
    
}
    

public static function getCurrentDataConnectionName(){
    
    return self::$currentDataConnectionName;
    
}
    
    

//Nenhum dado da conexao fica na cache
public static function initialize_once($config){
    
    self::$connect_once_config = $config;
    self::initialize();
    
}



/**
 * Carregas as configuracoes de conexoes
 */
public static function initialize($cli=false){
    
        $config_file =APPLICATION_DIR.APPLICATION_CONFIG_DIR.'data.json';

        $configs=false;
        $config_string=false;

                
        $overriden_config = false; //Configuracoes de base de dados que estao na sessao

        $app_config = loadConfiguration('app');

        //Se o debug estiver ligado, faz override de configuracoes de base de dadps
        $override_configuration = !$app_config->development;

        //Application is online
        if(true){


            //Data configurations should not be overriden
            if(!property_exists($app_config,'override_data_configs')){

                $override_configuration = false;

            }else{

                //The developer configured override
                $override_configuration = $app_config->override_data_configs;

            }

        }
        

        //Override de conexao de base de dados ligado
        if($override_configuration&&self::$connect_once_config==false){
    
                //Obtem os detalhes da conexao de base de dados 
                $overriden_config= AppInstance::getConfigData();
            
                //Dados invalidos
                if(!$overriden_config){
                    
                    //Mostrar erro na consola
                    if($cli){
                       
                        echo "error: overriden data configurations are not valid!\n";
                    }
                 
                    
                }
         
                //Define como detalhes de conexao a serem usados
                $config_string = $overriden_config;
         
            
        }else if(self::$connect_once_config){//Configurar conexao apenas desta vez
            
            $config_string = self::$connect_once_config;
           
            
        }else{ //Usar ficheiro padrao com detalhes de conexao a base de dados  


            if(file_exists($config_file)){

                //Configuracoes de base de dados nao cacheadas
                $config_string=file_get_contents($config_file);

            }

                
        }
    
        
        //String de conexao de base de dados invalida
        if(!$config_string){
            return false;            
        }
        
        $config_json = json_decode($config_string);        
        $configs=  Functions::json_to_array($config_json);
        $connections_array=$configs['connections'];
        
        //Nao foi definida a conexao main
        if(!isset($connections_array['main'])){
            
            //Obtem definicoes de todas conexoes
            $connections_settings_array=$connections_array['settings'];
            
            //Pega os nomes
            $all_connection_names= array_keys($connections_settings_array);
            
            //Pega o nome da primeira conexao
            $first_connection_name=$all_connection_names[0];
            
            //Decide que esta sera a conexao principal
            $connections_array['main']=$first_connection_name;
            
        }
        
        
        $configs=$connections_array;

    
    self::$connections=$configs;
    self::$ready = true;
     
    
    
    //Inicializa o mapeador de dados
    self::initializeDataMapper();
   
    
  }


    /**
     * Gets a connection by the supplied name from the Data Mapper
     * @return bool
     */
public static function needConnection($name='main'){

    if(self::$active_mapper_instance){

        return self::$active_mapper_instance->getConnection($name);


    }

    return false;

}


}