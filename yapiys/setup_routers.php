<?php

/**
 * THIS SCRIPT IS RESPONSIBLE FOR INITILIALIZING THE APPLICATION SWITCH AND ITS ROUTERS
 *
 */


    $appSwitch = new AppSwitch();    

    $mvcRouter = new MVCRouter($accessManager);

    $hmvcRouter= new HMVCRouter($accessManager);

    $apiRouter = new APIRouter($accessManager);

    $assetsRouter = new AssetsRouter($accessManager);   

    $ypRouter = new YapiysRouter($accessManager);

    $pRouter = new PartialsRouter($accessManager);

    $vdump = new ViewsDumpRouter($accessManager);

    AppInstance::setAccessManager($accessManager);
    AppInstance::setUserSessionBundle($user_session_bundle);
    AppInstance::registerRouter($mvcRouter);
    AppInstance::registerRouter($hmvcRouter);
    AppInstance::registerRouter($apiRouter);
    AppInstance::registerRouter($ypRouter);
    AppInstance::registerRouter($pRouter);
    AppInstance::registerRouter($vdump);

    AppInstance::$switch = $appSwitch;


    $appConfig = loadConfiguration('app');


    //Checks if contexts are enabled
    if(property_exists($appConfig,'contexts')){
        
        $context_enabled = $appConfig->contexts;
        if(is_bool($context_enabled)){
            
            $appSwitch::$contextable = $context_enabled;
            
            
        }
      
    }

    $appSwitch->registerRouter($vdump);
    $appSwitch->registerRouter($mvcRouter);
    $appSwitch->registerRouter($hmvcRouter);
    $appSwitch->registerRouter($assetsRouter);
    $appSwitch->registerRouter($apiRouter);
    $appSwitch->registerRouter($ypRouter);
    $appSwitch->registerRouter($pRouter);
