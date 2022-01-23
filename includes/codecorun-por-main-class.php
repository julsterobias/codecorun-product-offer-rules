<?php
/**
 * 
 * codecorun_por_main_class
 * @since 1.0.0
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
     * static
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