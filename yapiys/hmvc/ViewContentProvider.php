<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ViewContentProvider
 *
 * @author Mario Junior
 */
class ViewContentProvider extends Bundle {
    
    public function provide($contentName,$contentCallback){
        if(is_callable($contentCallback)){
            $this->set($contentName, $contentCallback);
        }
    }
    
    
    public function provideAlways($contentCallback){
        if(is_callable($contentCallback)){
            $this->set('always', $contentCallback);
        }
    }
    
    
}