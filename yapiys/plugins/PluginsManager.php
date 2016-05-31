<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PluginsManager
 *
 * @author Mario Junior
 */
class PluginsManager {
    
    
    
    /*
     * Carrega os plugins
     */
    public static function loadAll(){
        
        $plugins_directory = APPLICATION_DIR.PLUGINS_DIRECTORY;   
       
        if(!file_exists($plugins_directory)){
            
            return;
        }
        
        $all_plugins= scandir($plugins_directory);
        
        foreach ($all_plugins as $plugin_name) {
            
            if($plugin_name!='.'&&$plugin_name!='..'){
                
                $plugin_directory = APPLICATION_DIR.PLUGINS_DIRECTORY.'/'.$plugin_name;
                $plugin_app_directory = APPLICATION_DIR.PLUGINS_DIRECTORY.'/'.$plugin_name.'/app';
                $plugin_yapiys_directory = APPLICATION_DIR.PLUGINS_DIRECTORY.'/'.$plugin_name.'/yapiys/js';

                if(file_exists($plugin_app_directory)){

                    //Virtualiza o directorio do plugin
                    VirtualApp::virtualizeAll($plugin_app_directory);

                }



                if(file_exists($plugin_yapiys_directory)){



                    //Virtualiza o directorio do plugin
                    VirtualYapiys::virtualize('js',$plugin_yapiys_directory);


                }
                

            }
            
            
        }        
        
    }
    
    
    
    
    
    
    
}