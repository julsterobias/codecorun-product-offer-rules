<?php
/**
 * 
 * codecorun_por_main_class
 * @since 1.0.0
 * @author codecorun
 * 
 */
namespace codecorun\por\main;

class codecorun_por_main_class
{
    private static $instance = null;

    /**
     * 
     * factory instance method
     * @since 1.0.0
     * @return class
     * 
     */
    public static function factory()
    {
        if(!self::$instance){
            self::$instance = new self;
        }
        return self::$instance;
    }

    
}
?>