<?php
/**
 * AR Config
 */

require_once('../yapiys/activerecord/ActiveRecord.php');

ActiveRecord\Config::initialize(function($cfg){

    $cfg->set_connections(array(
            'development' => 'mysql://root:root@localhost/test')
    );
});