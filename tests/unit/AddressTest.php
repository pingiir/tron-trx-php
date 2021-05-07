<?php

use imehrzadm\TronTrxAPI\Address;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \imehrzadm\TronTrxAPI\Address::__construct
     */
    public function testThatInvalidAddressThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Address();
    }
}
