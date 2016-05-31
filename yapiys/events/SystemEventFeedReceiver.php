<?php


/**
 * Description of SystemEventFeedReceiver
 *
 * @author Mario Junior
 */
interface SystemEventFeedReceiver {
   
    public function onReceived($name,$params);
    
    public function getName();
    
    public function getDescription();    
    
}