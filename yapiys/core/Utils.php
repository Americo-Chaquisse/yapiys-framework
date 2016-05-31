<?php
/**
 * @author: Americo Chaquise
 * All Functions needed to process a route and confirm user identity
 */

/**
 * Gets the application instance ID.
 * @return bool
 */
function getAppInstanceID(){

    if(isset($_SESSION[APP_INSTANCE_ID_KEY])){

        return $_SESSION[APP_INSTANCE_ID_KEY];

    }

    return false;

}

/**
 * Deletes the application instance ID
 */
function unsetAppInstanceID(){

    if(isset($_SESSION[APP_INSTANCE_ID_KEY])){

        unset($_SESSION[APP_INSTANCE_ID_KEY]);

    }

}


//This Redirect calls to the Yapiys Error Handler
function error_handler($level,$message,$file,$line,$context){

    ErrorHandler::error_occured($level,$message,$file,$line,$context);

}


function getDefaultController(){

    $overriden = AppInstance::defaultRouteOverwriten();

    if($overriden){


        return $overriden;

    }

    $filename = APPLICATION_DIR.APPLICATION_CONFIG_DIR.'routing.json';

    if(!file_exists($filename)){

        throw new Exception('Configuration file routing.json is missing.');

    }

    $json_content = file_get_contents($filename);


    $json_object = json_decode($json_content);
    $default_controller=false;


    if($json_object){


        if(property_exists($json_object,'default_route')){
            $default_controller=$json_object->default_route;
        }else{

            throw new Exception('Default route configuration not found in routing.json');


        }
    }

    return $default_controller;
}

function getLastRequestTime(){

    if(isset($_SESSION[UserSessionBundle::user_data_session_variable_name][LAST_REQUEST_TIME])){

        return $_SESSION[UserSessionBundle::user_data_session_variable_name][LAST_REQUEST_TIME];

    }

    return false;

}