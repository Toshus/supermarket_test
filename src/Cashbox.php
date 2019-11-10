<?php

namespace Supermarket;

/**
 * Class Cashbox
 * Реализует функциональность кассы в супермаркете
 * @package Supermarket
 */
class Cashbox
{
    /** @var int Касса закрыта */
    const STATE_CLOSED = 1;
    /** @var int Касса открыта и работает */
    const STATE_OPENED = 2;
    /** @var int Касса открыти и ждет покупателей */
    const STATE_STANDBY = 3;
    
    /** @var integer состояние кассы */
    private $_state = self::STATE_CLOSED;
    
    /** @var int номер кассы */
    private $_id;
    
    /** @var array очередь покупателей */
    private $_customers = [];
    
    /** @var array настройки кассы */
    private $_settings;
    
    /** @var int время начала ожидания покупателей */
    private $_standby_start_time;
    
    /**
     * Cashbox constructor.
     * @param int $id номер кассы
     * @param array $settings настройки кассы
     */
    public function __construct(int $id, array $settings)
    {
        $this->_id = $id;
        $this->_settings = $settings;
    }
    
    /**
     * @return int
     */
    public function getState()
    {
        return $this->_state;
    }
    
    /**
     * @return int|int
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * проверяет, свободна ли касса для обслуживания
     * @return bool
     */
    public function isFree()
    {
        /** @var Customer $customer */
        foreach ($this->_customers as $customer) {
            if ($customer->getState() === Customer::STATE_BUYS) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @return int
     */
    public function getQueueLength()
    {
        return count($this->_customers);
    }
    
    /**
     * Открывает закрытую кассу
     */
    public function open()
    {
        if ($this->_state === self::STATE_CLOSED) {
            $this->_state = self::STATE_OPENED;
        }
    }
    
    /**
     * Закрывает кассу
     */
    private function close($tm)
    {
        if (
            $this->_state === self::STATE_STANDBY &&
            $tm - $this->_standby_start_time >= $this->_settings['off_time']
        ) {
            $this->_state = self::STATE_CLOSED;
            return true;
        }
        
        return false;
    }
    
    /**
     * Добавляет покупателя в очередь
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer)
    {
        if ($customer !== null) {
            array_push($this->_customers, $customer);
            // снимаем кассу с ожидания
            if ($this->_state === self::STATE_STANDBY) {
                $this->_standby_start_time = null;
            }
        }
    }
    
    /**
     * Отпускает покупателя с кассы
     */
    private function freeCustomer($key, $tm)
    {
        unset($this->_customers[$key]);
        
        if ($this->getQueueLength() === 0) {
            // переводим в режим ожидания
            $this->_state = self::STATE_STANDBY;
            $this->_standby_start_time = $tm;
        } else {
            // либо берем следующего покупателя
            reset($this->_customers);
            /** @var Customer $customer */
            $customer = current($this->_customers);
            $customer->startServe($tm);
        }
    }
    
    public function doWork($tm)
    {
        // 0. Если закрыты, то ничего не делаем
        if ($this->getState() === self::STATE_CLOSED) {
            return;
        }
        
        // 1. Пробуем закрыть кассу, если она в ожидании
        if ($this->close($tm)) {
            return;
        }
        
        // 2. Обслуживаем покупателя
        /**
         * @var int $key
         * @var Customer $customer
         */
        foreach ($this->_customers as $key => $customer) {
            if ($customer->getState() === Customer::STATE_BUYS) {
                if (
                    ($tm - $customer->getBuyStartTime()) >=
                    ($customer->getGoodsCount() * $this->_settings['check_time'] + $this->_settings['pay_time'])
                ) {
                    $this->freeCustomer($key, $tm);
                }
                return;
            }
        }
    }
    
    /**
     * возвращает строковое представление о своем состоянии в формате:
     * [номер кассы]:[кол-во покупателей в очереди]|X (закрыта)
     */
    public function printState()
    {
        $res = $this->_id . ':';
        if ($this->_state == self::STATE_CLOSED) {
            $res .= 'X';
        } else {
            $res .= count($this->_customers);
        }
        return $res;
    }
}