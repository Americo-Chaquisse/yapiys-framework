<?php


/**
 * Description of ActiveRecordMapper
 *
 * @author Mario Junior
 */
class ActiveRecordMapper extends AbstractDataMapper {
    
    static $connections = false;
    
    public function getConnection($name = 'main') {
        
        //Gets the connection
        return ActiveRecord\Connection::instance($name);
                
    }

    
    public function initialize($settings) {
        
       
        
        //Pega as conexoes
        $connections = $settings['settings'];
               
        //Strings de conexao
        $connections_array = array();
        
        foreach($connections as $connection_name => $connection_details){
            
            $driver =   "mysql";
            $user =     $connection_details['dbuser'];
            $pass =     $connection_details['dbpass'];
            $database = $connection_details['dbname'];
            $host=      $connection_details['host'];
            
            
            if(isset($connection_details['driver'])){
                
                $driver = $connection_details['driver'];
                
                
            }
            
            
            
            $connection_string = "$driver://$user:$pass@$host/$database";            
            $connections_array[$connection_name]= $connection_string;

            self::$connections=$connections_array;
              
            
        }
        
        
        //Inicializa o php-ActiveRecord
        ActiveRecord\Config::initialize(function($cfg)
        {
            $connection_strings = self::$connections;
             
            $cfg->set_model_directory('.');
            $cfg->set_connections($connection_strings);
            $cfg->set_default_connection('main');
            
            
        });
        
       
        
        
    }

    
}