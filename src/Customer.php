<?php

namespace Supermarket;

/**
 * Class Customer
 * Представляет покупателя в магазине
 * @package Supermarket
 */
class Customer
{
    /** @var int Делает покупки */
    const STATE_SHOPPING = 0;
    /** @var int Находится в очереди */
    const STATE_QUEUE = 1;
    /** @var int Обслуживается кассиром */
    const STATE_BUYS = 2;
    
    /** @var int количество товаров у покупателя */
    private $_goods;
    
    /** @var int текущее состояние покупателя */
    private $_state;
    
    /** @var Cashbox касса, в которую стоит покупатель */
    private $_cashbox;
    
    /** @var int время начала обслуживания покупателя */
    private $_buy_start_time;
    
    /**
     * Customer constructor.
     * @param $goods
     */
    public function __construct($goods)
    {
        $this->_goods = $goods;
        $this->_state = self::STATE_SHOPPING;
    }
    
    /**
     * Ставим покупателя в очередь на кассу
     * @param Cashbox $cashbox
     * @return bool
     * @throws CustomerException
     */
    public function assignToCashbox(Cashbox $cashbox)
    {
        if ($cashbox->getState() === Cashbox::STATE_CLOSED) {
            throw new CustomerException('Невозможно поставить покупателя в очередь. Касса ' . $cashbox->getId() . ' закрыта.');
        }
        
        $this->_state = self::STATE_QUEUE;
        $this->_cashbox = $cashbox;
        
        $cashbox->addCustomer($this);
        
        return true;
    }
    
    /**
     * @param int $tm
     */
    public function startServe(int $tm)
    {
        if ($this->_cashbox->isFree()) {
            $this->_state = self::STATE_BUYS;
            $this->_buy_start_time = $tm;
        }
    }
    
    public function getBuyStartTime()
    {
        return $this->_buy_start_time;
    }
    
    public function getGoodsCount()
    {
        return $this->_goods;
    }
    
    /**
     * @return int
     */
    public function getState()
    {
        return $this->_state;
    }
    
    /**
     * Генерирует нового покупателя с использованием переданных настроек покупателя
     * @param array $settings
     * @return Customer
     * @throws CustomerException
     */
    public static function getNewCustomer(array $settings)
    {
        if (!isset($settings['max_goods'])) {
            throw new CustomerException('Ошибка при создании покупателя. Ожидается параметр max_goods');
        }
        if ($settings['max_goods'] < 1) {
            throw new CustomerException('Ошибка при создании покупателя. Параметр max_goods должен быть больше 0');
        }
        
        $goods = mt_rand(1, $settings['max_goods']);
        
        return new self($goods);
    }
}