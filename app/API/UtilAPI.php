<?php

/**
 * Created by Americo Chaquisse
 */
class UtilAPI extends AppExtendedWebService
{

    /**
     * Simple api example
     */
    public function check_time()
    {
        $this->response(date('Y-m-d G:i:s'));
    }
    
    
    public function dlist_data($params)
    {
        DList::fetchData(Contact, array("id","name","surname","phone", "email"));
    }

}