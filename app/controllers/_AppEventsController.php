<?php

/**
 * Class _AppEventsController
 * Events of the app
 */
class _AppEventsController{

    /**
     * When loading the app
     */
    public function init(){}

    public static function beforeTemplateDataRead(){}

    public function access_manager_initialized(){}


    /**
     * Before validate context, if multi context mode enabled
     * @param $url
     */
    public function beforeContextValidation($url){}

    /**
     * Before read any action
     * @param $url
     */
    public function beforeAction($url){}


    /**
     * Before check user access
     * @param $url
     */
    public function beforeAccessCheck($url){
        //Allow all routes by default
        AppUser::allow($url);
    }

    /**
     * On 404
     * @param $url
     */
    public function pageNotFound($url){}


    /**
     * On 403
     * @param $url
     */
    public function accessForbidden($url){}

    /**
     * On 500
     * @param $url
     */
    public function internalServerError($url){}

    public function offline(){
        //What the app should do when is on development mode
    }

    public function online(){
        //What the app should do when is on production mode
    }

    public function before_asset_output(){
        //Cache assents on app production mode
        if(AppInstance::isOnline()){
            header("Pragma:");
            //Cache the asset for one day
            header("Cache-Control: public, max-age=86400");
        }
    }

    public function before_view_dump_output(){}

    public function before_partial_view_output(){}


}




