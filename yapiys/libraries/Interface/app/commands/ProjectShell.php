<?php

    class ProjectShell extends  AppExtendedShellCommand{
                
        
            public function online(){
                
                $file = APPLICATION_DIR.'config/app.json';
                $content = file_get_contents($file);
                $config = json_decode($content);
                $config_array=json_to_array($config);
                $config_array['development']=false;
                $json_config = json_encode($config_array);
                
                file_put_contents($file,stripslashes($json_config));
                echo "\nYour application is online now!\n";  
                echo "Base URL in use : ".$config_array['web_base_url']."\n\n";
              
            }
        
        
            public function offline(){
                
                $file = APPLICATION_DIR.'config/app.json';
                $content = file_get_contents($file);
                $config = json_decode($content);
                $config_array=json_to_array($config);
                $config_array['development']=true;
                $json_config = json_encode($config_array);
                
                file_put_contents($file,stripslashes($json_config));
                echo "\nYour application is under development now!\n";
                echo "Base URL in use : ".$config_array['local_base_url']."\n\n";                
                
                
            }    
        
        
        
            public function status(){
                
                $file = APPLICATION_DIR.'config/app.json';
                $content = file_get_contents($file);
                $config = json_decode($content);
                $config_array=json_to_array($config);
    
                $mode = 'online';
                $base_url = $config_array['web_base_url'];
                
             
                
                if($config_array['development']==1){
                    
                    $mode = 'development';
                    $base_url = $config_array['local_base_url'];
                    
                }
                
                echo "\nApplication is in mode : $mode\n";
                echo "Base URL in use : ".$base_url."\n\n";
                
            } 
        
        
        
    }
?>