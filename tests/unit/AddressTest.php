<?php

use pingi\TronTrxAPI\Address;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \pingi\TronTrxAPI\Address::__construct
     */
    public function testThatInvalidAddressThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Address();
    }
}
