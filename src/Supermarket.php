<?php

namespace Supermarket;

class Supermarket
{
    const HOUR_SECONDS = 3600;
    
    /** @var array начальные настройки магазина */
    private $_settings;
    
    private $_cashboxes;
    
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
        $this->generateCashboxes();
    }
    
    /**
     * Генерирует кассы
     * генерация касс делается в супермаркете, так как логически это подчиненный объект и его характеристики
     * определяет супермаркет
     */
    private function generateCashboxes()
    {
        $this->_cashboxes = [];
        for ($i = 0; $i < $this->_settings['cashbox_quantity']; $i++) {
            array_push(
                $this->_cashboxes,
                new Cashbox($i, $this->_settings['cashbox_settings'])
            );
        }
    }
    
    /**
     * Просчитывает модель
     * @return string
     * @throws SupermarketException
     */
    public function calculateModel()
    {
        $res = '';
        
        if (!isset($this->_settings['work_start'])) {
            throw new SupermarketException('Ошибка: ожидается параметр work_start');
        }
        if ($this->_settings['work_start'] < 0) {
            throw new SupermarketException('Ошибка: Параметр work_start должен быть целым положительным числом');
        }
        if (!isset($this->_settings['work_end'])) {
            throw new SupermarketException('Ошибка: ожидается параметр work_end');
        }
        if ($this->_settings['work_end'] < 0) {
            throw new SupermarketException('Ошибка: Параметр work_end должен быть целым положительным числом');
        }
        if ($this->_settings['work_start'] > $this->_settings['work_end']) {
            throw new SupermarketException('Ошибка: Параметр work_start должен быть больше параметра work_end');
        }
        
        $startTime = $this->_settings['work_start'] * self::HOUR_SECONDS;
        $endTime = $this->_settings['work_end'] * self::HOUR_SECONDS;
        
        for ($i = $startTime; $i <= $endTime; $i++) {
            $res .= $this->tick($i);
        }
        
        return $res;
    }
    
    private function tick($tm)
    {
        // 1. Делаем снимок состояния
        $res = $this->printState($tm);
        
        // 2. Генерируем нового покупателя
        $this->generateNewCustomer($tm);
        
        // 3. Обсчитываем рабочий процесс кассы
        $this->processCashboxes($tm);
        
        return $res;
    }
    
    /**
     * @param $tm
     * @return string
     */
    private function printState($tm)
    {
        $res = '';
        if (($tm % self::HOUR_SECONDS) === 0) {
            $res = 'Час ' . round($tm / self::HOUR_SECONDS) . ': ';
            /** @var Cashbox $cashbox */
            foreach ($this->_cashboxes as $cashbox) {
                $res .= $cashbox->printState() . '  |  ';
            }
            $res .= PHP_EOL;
        }
        
        return $res;
    }
    
    /**
     * С заданной периодичностью генерирует нового покупателя с заданной вероятностью
     * @param int $tm
     * @throws CustomerException
     */
    private function generateNewCustomer(int $tm)
    {
        if (($tm % $this->_settings['new_customer_interval']) === 0) {
            $hour = (int)floor($tm / self::HOUR_SECONDS);
            $prob = mt_rand(1, 100);
            if ($prob <= $this->_settings['customer_probability_map'][$hour] * 100) {
                $customer = Customer::getNewCustomer($this->_settings['customer_settings']);
                $cashbox = $this->getEligibleCashbox();
                $customer->assignToCashbox($cashbox);
                $customer->startServe($tm);
            }
        }
    }
    
    /**
     * @return Cashbox|null
     */
    private function getEligibleCashbox()
    {
        /** @var Cashbox $closedCashbox */
        $closedCashbox = null;
        /** @var Cashbox $eligibleCashbox */
        $eligibleCashbox = null;
        
        // поищем открытую кассу с минимальной очередью
        /** @var Cashbox $cashbox */
        foreach ($this->_cashboxes as $cashbox) {
            if (($closedCashbox === null) && ($cashbox->getState() === Cashbox::STATE_CLOSED)) {
                $closedCashbox = $cashbox;
            }
            
            if (
                (
                    $cashbox->getState() === Cashbox::STATE_OPENED ||
                    $cashbox->getState() === Cashbox::STATE_STANDBY
                ) && (
                    $eligibleCashbox === null ||
                    $eligibleCashbox->getQueueLength() > $cashbox->getQueueLength()
                )
            ) {
                $eligibleCashbox = $cashbox;
            }
        }
        
        // проверим, а надо ли и можно ли открыть еще одну кассу
        if (
            $eligibleCashbox === null ||
            (
                $eligibleCashbox !== null &&
                $eligibleCashbox->getQueueLength() >= $this->_settings['open_cashbox_queue_size'] &&
                $closedCashbox !== null
            )
        ) {
            $closedCashbox->open();
            $eligibleCashbox = $closedCashbox;
        }
        
        return $eligibleCashbox;
    }
    
    private function processCashboxes($tm)
    {
        /** @var Cashbox $cashbox */
        foreach ($this->_cashboxes as $cashbox) {
            if ($cashbox->getState() !== Cashbox::STATE_CLOSED) {
                $cashbox->doWork($tm);
            }
        }
    }
}