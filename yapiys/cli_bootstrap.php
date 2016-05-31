<?php
session_start();
require_once 'basic.php';
require_once 'constants.php';
require_once 'CLIDispatcher.php';


//There is no maximum execution time
set_time_limit(0);

if(!isset($_SESSION[AcessManagerBundle::access_manager_session_variable_name])){
    $_SESSION[AcessManagerBundle::access_manager_session_variable_name]['sstm']= time();
}

if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name])){
    $_SESSION[UserSessionBundle::user_data_session_variable_name]['cvtemplate']=  'main';
}


if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name])){
    $_SESSION[UserSessionBundle::user_data_session_variable_name]['cli']= true;
}    

    
    //Creates the access manager instance
    $accessManager = new AccessManager();

    //Cleans up the cache in session
    CacheInSession::flushCache();
    CacheInSession::initialize();


    AppInstance::setAccessManager($accessManager);
    
    //Creates a new user session Bundle
    $user_session_bundle = new UserSessionBundle();

    AppInstance::setUserSessionBundle($user_session_bundle);

    //Setup the routers
    require_once 'setup_routers.php';

    //counts the passed arguments
    $totalArgs = $_SERVER['argc'];

    //Gets all the arguments passed
    $allArgs = $_SERVER['argv'];

    //Activates the Dispatcher
    CLIDispatcher::dispatch($allArgs);

/**
 * Prepares a command and sends it to the CLIDispatcher
 * @param string $command command string
 */
    function dispatchCommand($command){
        
        $command_args = array('empty');
        $command_description = explode(" ",$command);
        
        $dispatch_array = array_merge($command_args,$command_description);
        CLIDispatcher::dispatch($dispatch_array);
        
    }