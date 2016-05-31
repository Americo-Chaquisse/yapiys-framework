<?php

/**
 * Description of JSONEncoder
 *
 * @author Mario Junior
 */
class ArrayEncoder {
    
    public static function array_encode_safe($array)
    {
		
		$internal = $array;
		
		if(is_string($internal)){
			return $internal;			
		}
	
		array_walk_recursive($internal, function(&$item, $key){
			
			//Trata-se de uma entity
			if(is_object($item)){
                            
                                if(is_a($item,'Entity')){
                                    
                                    //Converte o objecto para array		
                                    $item_array = $item->toArray();	                               

                                    //Converte o array utf8
                                    $item = json_encode_safe($item_array);

                                
                                }else{
                                    
                                        
                                    //Converte o objecto para array		
                                    $item_array = json_to_array($item);                              

                                    //Converte o array utf8
                                    $item = json_encode_safe($item_array);                                    
                                    
                                    
                                }
				
				return;
						
			}else if(is_array($item)){
			
				//Trata-se de um array
				
				//Converte o array para utf8
				$item = json_encode_safe($item);
					
				return;
					
			}
			
			if(!mb_detect_encoding($item, 'utf-8', true)){
			
					
				$item = utf8_encode($item);
						
				
			}
			
		});
	 
		return $internal;
	}

    
}
