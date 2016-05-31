<?php
/**
 * Main framework file
 * Here we receive the route and call the request manager
 * THIS IS THE YAPIYS REQUESTS BOOTSTRAP FILE
 * This script is responsible for setting up everything
 * Every time a request is fired.
 *
 * BE CAREFUL.
 */

date_default_timezone_set('Africa/Maputo');

require_once 'session_manager.php';
require_once 'core/basic.php';

//Include composer to the framework
if(file_exists('../vendor/autoload.php')){
    require '../vendor/autoload.php';
}

if(isset($_SERVER['HTTP_USER_AGENT'])){

	//Checks if the browser still the same
	if(isset($_SESSION[USER_BROWSER])){

            $agent = $_SERVER['HTTP_USER_AGENT'];

			//There is no match
			if($agent!==$_SESSION[USER_BROWSER]){

				//Deletes the session and creates a new one
				session_regenerate_id(true);

			}
	}else{
			  //Defines the browser
			$agent = $_SERVER['HTTP_USER_AGENT'];

			$_SESSION[USER_BROWSER] = $agent;
			
	}


}


if(isset($_SERVER['REMOTE_ADDR'])){
	
		//Checks the IP
	if(isset($_SESSION[USER_IP])){

		$inet = $_SERVER['REMOTE_ADDR'];

		//The IP does not match
		if($inet!==$_SESSION[USER_IP]){

			session_regenerate_id(true);

		}

	}else{

		$inet = $_SERVER['REMOTE_ADDR'];

		//Defines the user IP
		$_SESSION[USER_IP] = $inet;


	}
	
	
}

//Inactivity timeout
if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name])){

    //Inactivity timeout is set
    if(isset($_SESSION[UserSessionBundle::user_data_session_variable_name][INACTIVITY_SESSION_TIMEOUT])){

        $inactivity_timeout = $_SESSION[UserSessionBundle::user_data_session_variable_name][INACTIVITY_SESSION_TIMEOUT];

        $now = time();
        $last_request_time = getLastRequestTime();

        //Last request time found
        if($last_request_time){

            //Inactivity timeout
            if(($now-$last_request_time)>=$inactivity_timeout){

                //Deletes the session information
                //session_regenerate_id(true);

                //Throws session inactivity timeout events
                ApplicationEvents::fireEvent('session_inactivity_timeout');

            }


        }


    }
}


//General timeout
if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name])){

    //Inactivity timeout is set
    if(isset($_SESSION[UserSessionBundle::user_data_session_variable_name][GENERAL_SESSION_TIMEOUT])){

        $inactivity_timeout = $_SESSION[UserSessionBundle::user_data_session_variable_name][GENERAL_SESSION_TIMEOUT];

        $now = time();

        //Session start time was found
        if(isset($_SESSION[AcessManagerBundle::access_manager_session_variable_name]['sstm'])){

            //Time that the session started
            $session_start_time = $_SESSION[AcessManagerBundle::access_manager_session_variable_name]['sstm'];

            //General timeout
            if(($now-$session_start_time)>=$inactivity_timeout){

                //Deletes the session information
                //session_regenerate_id(true);

                //Throws session inactivity timeout events
                ApplicationEvents::fireEvent('session_general_timeout');


            }


        }

    }
}

if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name]['cli'])){
    $_SESSION[UserSessionBundle::user_data_session_variable_name]['cli']= false;
}


if(!isset($_SESSION[AcessManagerBundle::access_manager_session_variable_name]['sstm'])){
    //Time that the session started
    $_SESSION[AcessManagerBundle::access_manager_session_variable_name]['sstm']= time();
}

if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name]['cvtemplate'])){
    $_SESSION[UserSessionBundle::user_data_session_variable_name]['cvtemplate']=  'main';
}

//Saves this time as time of the last REQUEST
if(!isset($_SESSION[UserSessionBundle::user_data_session_variable_name][LAST_REQUEST_TIME])){
    $_SESSION[UserSessionBundle::user_data_session_variable_name][LAST_REQUEST_TIME]=  time();
}



//This application instance has no ID yet
if(!isset($_SESSION[APP_INSTANCE_ID_KEY])){

    //Do not regenerate the CSRF_TOKEN if it is an Ajax request

    //Generate a define a ID
    $id = uniqid().md5(base64_encode(uniqid())).md5('0xdddfx23./;;;;;[\'\"=\'=&^%'.uniqid());

    $_SESSION[APP_INSTANCE_ID_KEY]=$id;


}


//setup the Yapiys Error Handler
set_error_handler("error_handler");

//Initializes the classes responsible for access checks.
$accessManager = new AccessManager();


//Initlizes the user session bundle
$user_session_bundle = new UserSessionBundle();


//Initializes the Application Events Channel
ApplicationEvents::init();

//Fire the application initialization event
ApplicationEvents::fireEvent('init');


//Load the base classes in app/classes
Framework::loadBaseClasses();

//Sets the access manager and user session bundle.
AppInstance::setAccessManager($accessManager);
AppInstance::setUserSessionBundle($user_session_bundle);

//Sets access manager as initialized
ApplicationEvents::fireEvent('access_manager_initialized');


//Sets up the framework routers
require_once 'setup_routers.php';

//Load application configuration
$app_configuration = loadConfiguration('app');

//Development property not set in configurarion
if(!property_exists($app_configuration,'development')){

    throw new Exception('Application development status configuration not found');

}

$development = $app_configuration->development;


//Cleans up the Cache in session in application is under development
if($development){

    CacheInSession::flushCache();

}


CacheInSession::initialize();



//Autoload Models configuration
if(property_exists($app_configuration,'autoload_models')){

    //Do not autoload models
    if(!$app_configuration->autoload_models){

        AppInstance::$autoloadModels = false;

    }

}


//App under development
if($development){

    ApplicationEvents::fireEvent('offline');

}else{

    ApplicationEvents::fireEvent('online');
}


//Initilizes the Debug Channel
DebugChannel::init();


//No route specified
if(!isset($_GET['route_url'])){


    $default_controller = getDefaultController();

    //Defaul controller set
    if($default_controller){


        //Emulates a request to such route
        $appSwitch->newRequest($default_controller);


    }else{

        //There is no default route

        $appSwitch::pageNotFound();

    }

    //exit();

}else{

    //URL specified
    $route_url = $_GET['route_url'];


    //Trigger the Application Switch to handle the request
    $appSwitch->newRequest($route_url);



}
/*
// stop profiler
$xhprof_data = xhprof_disable();

$XHPROF_ROOT = realpath(dirname(__FILE__) .'/..');
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

// implementation of iXHProfRuns.
$xhprof_runs = new XHProfRuns_Default();

// save the run under a namespace "xhprof_foo"
$run_id = $xhprof_runs->save_run($xhprof_data, "360_rc");
*/
