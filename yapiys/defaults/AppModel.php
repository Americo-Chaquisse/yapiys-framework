<?php

/**
 * Description of AppModel
 *
 * @author Mario Junior
 */
class AppModel extends Model {

    public static $connection = 'main';


    public static function to_array_batch($all){

        $result = array();

        foreach($all as $item){

            $result[] = $item->to_array();

        }

        return $result;

    }
  
    
    

}
