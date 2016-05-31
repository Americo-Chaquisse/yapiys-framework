<?php


/**
 * Description of SystemEventsFeed
 *
 * @author Mario Junior
 */
class SystemEventsFeed {
    
      private static $feed_participants = array();
    
      public static function subscribe($eventName,$receiver){
                   
          //Receptor de feeds valido
          if(is_a($receiver, 'SystemEventFeedReceiver')){
              
              
               //Ja existem outros participantes do feed
               if(isset(self::$feed_participants[$eventName])){
              
                    self::$feed_participants[$eventName][]=$receiver;
              
                }else{
                    
                    //Primeiro aprticipante do feed
                    self::$feed_participants[$eventName] = array();
                    
                    self::$feed_participants[$eventName][]=$receiver;
                    
                }
              
              
          }      
         
          
      }
      
      public static function getSubscribers($eventName){
                
          if(isset(self::$feed_participants[$eventName])){
              
              return self::$feed_participants[$eventName]; 
              
          }
          
          return false;        
          
      }
      
      
      public static function unsubscribe($eventName,$receiver){
                    
          if(isset(self::$feed_participants[$eventName])){
              
            $index = array_search($receiver, self::$feed_participants[$eventName]);
            if($index){
                unset(self::$feed_participants[$eventName][$index]);
            }

          }
          
      }
    
    
}