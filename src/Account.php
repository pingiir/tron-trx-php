<?php

namespace pingi\TronTrxAPI;

/**
 * Class Account
 *
 * @property Address $address
 *
 * @package pingi\TronTrxAPI
 */
class Account
{
    public $address;
    public $balance = 0.00;
    public $create_time = 0;

    public function __construct(Address $address, float $balance, int $create_time)
    {
        $this->address = $address;
        $this->balance = $balance;
        $this->create_time = $create_time;
    }
}