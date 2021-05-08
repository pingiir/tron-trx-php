<?php

use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @covers \pingi\trontrxapi\Transaction::isSigned
     */
    public function testIsSigned()
    {
        $transaction = new \pingi\trontrxapi\Transaction('', new stdClass());
        $this->assertFalse($transaction->isSigned());

        $transaction->signature = ['wdwd'];
        $this->assertTrue($transaction->isSigned());
    }
}
