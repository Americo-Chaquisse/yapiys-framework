<?php

/**
 * Description of View
 *
 * @author Mario Junior
 */
abstract class View {
   
   public abstract function onCall($UIBundle);
   
   /**
    * Preenche dados em uma View
    * @param type $moduleName nome do modulo
    * @param type $viewName nome da view
    * @param type $UIBundle dados que devem ser preenchidos
    */
   
   public static function fillData($moduleName,$viewName,$UIBundle){
       
   }
}