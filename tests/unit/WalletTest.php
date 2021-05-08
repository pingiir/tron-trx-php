<?php

namespace pingi\trontrxapi\Tests\Unit;

use pingi\trontrxapi\Exceptions\TronErrorException;
use pingi\trontrxapi\Wallet;

class WalletTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \pingi\trontrxapi\Wallet::getAddressHex
     */
    public function testGetAddressHexFromPubKeyHex()
    {
        $publicHex = '0469231c045fd16b02429ba4aa04c14d49cfae09e834052d27f8ab4a5c50000cf7de06fddb08d27ddbfaef93896b102b98e74326b0cca74647422c869a4c3758c0';
        $expectedAddressHex = '41fe7323249972344af4dad2f4dab2fcdbf254120e';

        /** @var \pingi\trontrxapi\Wallet $wallet */
        $wallet = $this->getMockBuilder(\pingi\trontrxapi\Wallet::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getAddressHex'])
            ->getMock();

        $addressHex = $wallet->getAddressHex(hex2bin($publicHex));
        $this->assertEquals($expectedAddressHex, $addressHex);
    }

    /**
     * @covers \pingi\trontrxapi\Wallet::getBase58CheckAddress
     */
    public function testGetBase58CheckAddress()
    {
        /** @var \pingi\trontrxapi\Wallet $wallet */
        $wallet = $this->getMockBuilder(\pingi\trontrxapi\Wallet::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getBase58CheckAddress'])
            ->getMock();

        $expectedOutput = 'TZAcZfMseztuzBRXniZH4uxBF6jXBD38N3';
        $addressBase58 = $wallet->getBase58CheckAddress(hex2bin('41fe7323249972344af4dad2f4dab2fcdbf254120e'));

        $this->assertEquals($expectedOutput, $addressBase58);
    }

    /**
     * @covers \pingi\trontrxapi\Wallet::generateAddress
     */
    public function testAttemptLimitWillThrowException()
    {
        $this->expectException(TronErrorException::class);

        /** @var Wallet $wallet */
        $wallet = $this->getMockBuilder(\pingi\trontrxapi\Wallet::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['generateAddress'])
            ->getMock();


        $wallet->expects(self::exactly(5))
            ->method('genKeyPair')
            ->willReturn(['private_key_hex' => 'bla', 'public_key' => 'bla']);


        $wallet->generateAddress();
    }
}
