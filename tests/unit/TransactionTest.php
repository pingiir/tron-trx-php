<?php

use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @covers \imehrzadm\TronTrx\Transaction::isSigned
     */
    public function testIsSigned()
    {
        $transaction = new \imehrzadm\TronTrx\Transaction('', new stdClass());
        $this->assertFalse($transaction->isSigned());

        $transaction->signature = ['wdwd'];
        $this->assertTrue($transaction->isSigned());
    }
}
