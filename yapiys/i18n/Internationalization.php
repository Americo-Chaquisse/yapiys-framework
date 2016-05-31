<?php


/**
 * Description of LanguageSupport
 *
 * @author Mario Junior
 */
class Internationalization {
    
    
    
    /**
     * Define o pacote de idioma activo
     * @param type $name
     */
     public static function setLang($name){
         
         $sbundle = AppInstance::getUserSessionBundle();  
         $sbundle->set('lang_name',$name);
         
     }
     
     
     public static function getLang(){
         
         
         $sbundle = AppInstance::getUserSessionBundle(); 
         
         if($sbundle->contains('lang_name')){
             
             return $sbundle->get('lang_name');
             
         }
         
         return false;
         
     }
     
     
     
     /**
      * Obtem o dicionario do pacote de idioma
      * @param type $name
      */
     public function getLangDictionary($name=false){
         
         
     }
     
     
     /**
      * Obtem a lista de redireccionamento activos neste pacote de idiomas
      * @param type $name
      */
     public function getLangWebrootRedirects($name=false){
         
                  
     }
     
     
     
     /**
      * Verifica se um pacote de linguas redirecciona um ficheiro
      * Se verdade, redirecciona a URL resultante do
      * redireccionamento.
      * 
      * @param type $url
      */
     public static function languageRedirects($filename_url,$name=false){
         
         
     }
    
    
}
