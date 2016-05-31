<?php
/**
 * Created by PhpStorm.
 * User: achaq
 * Date: 1/13/2016
 * Time: 11:20 AM
 */

/**
 * @author: Americo Chaquisse
 * Class HomeController
 * All controllers must extend ViewController, or a class that extends it
 */
class HomeController extends AppController
{

    /**
     * This is an example of an action
     */
    public function start(){

        $this->callView();

    }

    /**
     * This is another one
     */
    public function index()
    {
        $this->data->setDate(date('d-M-y'));
        $this->callView();

    }


}