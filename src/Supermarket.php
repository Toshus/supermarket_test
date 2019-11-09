<?php

namespace Supermarket;

class Supermarket
{
    /** @var array начальные настройки магазина */
    private $_settings;
    
    public function __construct($settings = null)
    {
        $this->_settings = $settings;
        $this->init();
    }
    
    /**
     * Инициализирует начальное состояние магазина
     */
    private function init()
    {
    
    }
    
    public function calculateModel()
    {
        $res = 'Hello World!';
        
        return $res;
    }
}