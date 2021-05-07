<?php

use imehrzadm\TronTrx\Address;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \imehrzadm\TronTrx\Address::__construct
     */
    public function testThatInvalidAddressThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Address();
    }
}
