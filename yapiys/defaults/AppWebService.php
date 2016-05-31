<?php

/**
 * Description of AppWebService
 *
 * @author Mario Junior
 */
class AppWebService extends WebService {
    //put your code here

    public function response($data){

        echo json_encode($data);

    }
    
    

    
}
