<?php

use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @covers \imehrzadm\TronTrxAPI\Transaction::isSigned
     */
    public function testIsSigned()
    {
        $transaction = new \imehrzadm\TronTrxAPI\Transaction('', new stdClass());
        $this->assertFalse($transaction->isSigned());

        $transaction->signature = ['wdwd'];
        $this->assertTrue($transaction->isSigned());
    }
}
