<?php

/**
 * Created by Americo Chaquisse.
 * Date: 3/10/16
 * Time: 2:26 PM
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

}