<?php

/**
 * Description of AppContextValidator
 *
 * @author Mario Junior
 */
class AppUser {
    
    /**
     * Consider this user logged in
     * @param type $context The context to login the user. Should be false if contexts are disabled
     * @param type $variables variables to be set after loging in the user.
     * @param int $inactivity_timeout Expire a session if current request is X seconds later than the last request.
     * @param int $general_timeout Expire a session if current session has been active for a certain amount of time, even if active.
     */
    public static function login($context=false,$variables,$inactivity_timeout=false,$general_timeout=false){

        //Context is true
        if($context) {
            AppInstance::setContext($context);
        }


        $session_variables = array();
        
        if(is_array($variables)){
            
            $session_variables = $variables;
            
            
        }else if(is_a('Bundle',$session_variables)){
            
            $session_variables = $variables->getInternalArray();   
            
        }
        
        
        if(is_array($session_variables)){
            
            foreach ($session_variables as $key => $value) {
                self::getSession()->set($key, $value);
            }

        }

        $login_time=date('H:i:s');
        $login_date=date('d-m-Y');
        $login_ID=self::generateLoginID();



        self::getSession()->set('app_login_id',$login_ID);
        self::getSession()->set('app_login_time',$login_time );
        self::getSession()->set('app_login_date', $login_date);
        self::getSession()->set('app_login_status',true);


        //Inactivity timeout is set
        if($inactivity_timeout){

            self::getSession()->set(INACTIVITY_SESSION_TIMEOUT,$inactivity_timeout);

        }


        //General timeout is set
        if($general_timeout){

            self::getSession()->set(GENERAL_SESSION_TIMEOUT,$general_timeout);

        }

    }


    /**
     * Returns the time necessary to expire the user session for inactivity or FALSE if timeout is not set.
     * @return int
     */
    public static function getInactivityTimeout(){

        if(self::getSession()->contains(INACTIVITY_SESSION_TIMEOUT)){

            return self::getSession()->get(INACTIVITY_SESSION_TIMEOUT);

        }

        return false;

    }


    /**
     * Returns the time necessary to expire the user session no matter what. If timeout is not set it returns FALSE.
     * @return int
     */
    public static function getGeneralTimeout(){

        if(self::getSession()->contains(GENERAL_SESSION_TIMEOUT)){

            return self::getSession()->get(GENERAL_SESSION_TIMEOUT);

        }

        return false;


    }

    /**
     * Returns the bundle for the user session
     * @return UserSessionBundle
     */
    public static function getSession(){

        return AppInstance::getUserSessionBundle();

    }
    
    private static function generateLoginID(){
        $frag1= AppInstance::getContext();
        $frag2= uniqid($frag1);
        $frag3=  uniqid();
        $loginID=md5(md5($frag1).md5($frag2).$frag3);
        return $loginID;        
    }
    
    
    /**
     * Logs out the user
     * @return type
     */
    public static function logout(){
        if(self::isLoggedIn()){

            AppInstance::getUserSessionBundle()->remove('app_login_time');
            AppInstance::getUserSessionBundle()->remove('app_login_date');
            AppInstance::getUserSessionBundle()->remove('app_login_id');                
            AppInstance::getUserSessionBundle()->remove('app_userID');
            AppInstance::getUserSessionBundle()->remove('app_login_status');                
            AppInstance::getUserSessionBundle()->remove('app_user_context');

            //Limopa todas as permissoes do usuario
            AppInstance::getAccessManager()->cleanPermissions();

            //Elimina a ID da instancia da aplicacao
            unsetAppInstanceID();

            //unset($_SESSION);
            //session_destroy();
            
        }
    }
    
    /**
     * Returns true if the user has been logged-in and returns false if not.
     * @return boolean
     */
    public static function isLoggedIn(){
        
        if(AppInstance::getUserSessionBundle()->contains('app_login_status')){
            return AppInstance::getUserSessionBundle()->get('app_login_status');
        }
        
        return FALSE;
    }


    /**
     * Returns the context where the user has been logged-in.
     * @return bool
     */
    public static function getContext(){
        
        if(AppInstance::getUserSessionBundle()->contains('app_user_context')){
            return AppInstance::getUserSessionBundle()->get('app_user_context');
        } 
        
        return FALSE;
    }

    /**
     * Returns TRUE if the user is logged-in in such context. Otherwise it returns FALSE.
     * @param $ctx context to verify.
     * @return bool
     */
    public static function loggedIn($ctx){            
        return AppInstance::contextMatches($ctx);
    }


    /**
     * Gives the user access to the specified
     * @param $route_or_module
     * @param bool $controller_or_access_level
     * @param bool $action
     * @param int $route_access_level
     */
    public static function allow($route_or_module,
                          $controller_or_access_level=false,
                          $action=false,$route_access_level=0){
        
        
        AppInstance::getAccessManager()->allow($route_or_module,$controller_or_access_level,
                                             $action.$route_access_level);        
        
    }
    
    
    
     
    public static function deny($route_or_module,
                          $controller_or_access_level=false,
                          $action=false){
        
        
        self::block($route_or_module, $controller_or_access_level, $action);
        
    }
    
    
    
    public static function block($route_or_module,
                          $controller_or_access_level=false,
                          $action=false){
        
        
        AppInstance::getAccessManager()->block($route_or_module,
                          $controller_or_access_level,
                          $action);
        
    }
    
    
    public static function isAllowed($route_or_module,
                          $controller_or_access_level=false,
                          $action=false){
        
        return AppInstance::getAccessManager()->userHasAccessTo($route_or_module,$controller_or_access_level,$action);
                
    }
    
}