<?php

use pingi\trontrxapi\Address;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \pingi\trontrxapi\Address::__construct
     */
    public function testThatInvalidAddressThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Address();
    }
}
