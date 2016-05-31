<?php


/**
 * Description of AppExtendedWebService
 *
 * @author Mario Junior
 */
class AppExtendedWebService extends ExtendedWebService {
    //put your code here
    
     public function response($data){

        echo json_encode($data);

    }
    
}
