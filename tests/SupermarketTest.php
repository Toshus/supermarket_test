<?php

use PHPUnit\Framework\TestCase;
use Supermarket\Supermarket;
use Supermarket\SupermarketException;

class SupermarketTest extends TestCase
{
    /**
     * @throws SupermarketException
     */
    public function testCalculateModelExpectWorkStartParamException()
    {
        $config = [
            'cashbox_quantity' => 5,
            'open_cashbox_queue_size' => 5,
            'cashbox_settings' => [
                'check_time' => 10,
                'pay_time' => 20,
                'off_time' => 60,
            ],
            'work_end' => 23,
            'customer_probability_map' => ['8' => .1],
            'customer_settings' => ['max_goods' => 20],
            'new_customer_interval' => 10,
        ];
        
        $supermarket = new Supermarket($config);
        $this->expectErrorMessage('Ошибка: ожидается параметр work_start');
        $this->expectException(SupermarketException::class);
        $supermarket->calculateModel();
    }
    
    /**
     * @throws SupermarketException
     */
    public function testCalculateModelExpectWorkStartAboveZeroException()
    {
        $config = [
            'cashbox_quantity' => 5,
            'open_cashbox_queue_size' => 5,
            'cashbox_settings' => [
                'check_time' => 10,
                'pay_time' => 20,
                'off_time' => 60,
            ],
            'work_start' => -1,
            'work_end' => 23,
            'customer_probability_map' => ['8' => .1],
            'customer_settings' => ['max_goods' => 20],
            'new_customer_interval' => 10,
        ];
        
        $supermarket = new Supermarket($config);
        $this->expectErrorMessage('Ошибка: Параметр work_start должен быть целым положительным числом');
        $this->expectException(SupermarketException::class);
        $supermarket->calculateModel();
    }
    
    /**
     * @throws SupermarketException
     */
    public function testCalculateModelExpectWorkEndParamException()
    {
        $config = [
            'cashbox_quantity' => 5,
            'open_cashbox_queue_size' => 5,
            'cashbox_settings' => [
                'check_time' => 10,
                'pay_time' => 20,
                'off_time' => 60,
            ],
            'work_start' => 8,
            'customer_probability_map' => ['8' => .1],
            'customer_settings' => ['max_goods' => 20],
            'new_customer_interval' => 10,
        ];
        
        $supermarket = new Supermarket($config);
        $this->expectErrorMessage('Ошибка: ожидается параметр work_end');
        $this->expectException(SupermarketException::class);
        $supermarket->calculateModel();
    }
    
    /**
     * @throws SupermarketException
     */
    public function testCalculateModelExpectWorkEndAboveZeroException()
    {
        $config = [
            'cashbox_quantity' => 5,
            'open_cashbox_queue_size' => 5,
            'cashbox_settings' => [
                'check_time' => 10,
                'pay_time' => 20,
                'off_time' => 60,
            ],
            'work_start' => 8,
            'work_end' => -1,
            'customer_probability_map' => ['8' => .1],
            'customer_settings' => ['max_goods' => 20],
            'new_customer_interval' => 10,
        ];
        
        $supermarket = new Supermarket($config);
        $this->expectErrorMessage('Ошибка: Параметр work_end должен быть целым положительным числом');
        $this->expectException(SupermarketException::class);
        $supermarket->calculateModel();
    }
    
    /**
     * @throws SupermarketException
     */
    public function testCalculateModelExpectWorkStartLessWorkEndException()
    {
        $config = [
            'cashbox_quantity' => 5,
            'open_cashbox_queue_size' => 5,
            'cashbox_settings' => [
                'check_time' => 10,
                'pay_time' => 20,
                'off_time' => 60,
            ],
            'work_start' => 23,
            'work_end' => 8,
            'customer_probability_map' => ['8' => .1],
            'customer_settings' => ['max_goods' => 20],
            'new_customer_interval' => 10,
        ];
        
        $supermarket = new Supermarket($config);
        $this->expectErrorMessage('Ошибка: Параметр work_start должен быть больше параметра work_end');
        $this->expectException(SupermarketException::class);
        $supermarket->calculateModel();
    }
    
    /**
     * @throws SupermarketException
     */
    public function testCalculateModel()
    {
        $config = [
            'cashbox_quantity' => 1,
            'open_cashbox_queue_size' => 5,
            'cashbox_settings' => [
                'check_time' => 10,
                'pay_time' => 20,
                'off_time' => 60,
            ],
            'work_start' => 8,
            'work_end' => 8,
            'customer_probability_map' => ['8' => .1],
            'customer_settings' => ['max_goods' => 20],
            'new_customer_interval' => 10,
        ];
        
        $supermarket = new Supermarket($config);
        $res = $supermarket->calculateModel();
        $this->assertSame('Час 8: 0:X  |', trim($res));
    }
}