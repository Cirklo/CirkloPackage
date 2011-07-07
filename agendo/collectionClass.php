<?php

/******************************************************************************************* 
* 
* 
* Version    : 1.0 
* @author  Hasin Hayder 
* @date10th March, 2005 
* @license LGPL
* @abstract: This class implements Collection object in PHP. 
******************************************************************************************** 
* 
* 
* 
*/ 
class phpCollection 
{ 
    var $length; 
    var $elements = array(); 
    var $__cnt=0; 
    var $__temp; 
     
    function add($key, $item) 
    {
            if (!(array_key_exists($key, $this->elements)))
            { 
                $this->elements[$key] = $item; 
                $this->length = count($this->elements); 
                $this->__temp = array_values($this->elements); 
                return true; 
            } 
        return false; 
    } 
     
    function remove($key) 
    { 
        $result = false; 
        if (array_key_exists($key,$this->elements)) 
        { 
            unset($this->elements[$key]); 
            $result = true; 
        } 

        $this->length = count($this->elements); 
        $this->__temp = array_values($this->elements); 
        return $result; 
    } 
     
    function key_exists($k) 
    { 
        return array_key_exists($k, $this->elements); 
    } 
     
    function has_next() 
    { 
        if(($this->__cnt)<($this->length-1)) 
            { 
            $this->__cnt++; 
            return true;  
                 
            } 
            else
            {
            return false;
            }
    } 
     
    function rewind() 
    { 
            //just set the iterator position to first 
            $this->__cnt = 0; 
    } 
     
    function current() 
    { 
         
         return  $this->__temp[$this->__cnt]; 
             
    } 
     
    function itemat($pos) 
    { 
        return $this->__temp[$pos]; 
    } 
     
    function removeat($pos) 
    { 
            $keys = array_keys($this->elements); 
            $curkey = $keys[$pos]; 
            return $this->remove($curkey); 
    } 
     
    function item($key) 
    { 
            return $this->elements[$key]; 
    } 
     
    function itemarray() 
    { 
            return $this->__temp; 
    } 
     
    function clear() 
    { 
            $this->elements = array(); 
            $this->__temp =  array(); 
            $this->__cnt = 0; 
            $this->length = 0; 
    } 
} 
?> 