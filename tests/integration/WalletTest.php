<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use pingi\TronTrxAPI\Account;
use pingi\TronTrxAPI\Address;
use pingi\TronTrxAPI\Api;
use pingi\TronTrxAPI\Block;
use pingi\TronTrxAPI\Exceptions\TronErrorException;
use pingi\TronTrxAPI\Transaction;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
    private $_api;

    const TEST_INSTANTIATE_AMMOUNT_SUN = 10000001;

    const TEST_TRANSACTION_AMOUNT = 1000000;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->_api = new Api(new Client([
            'base_uri' => 'https://api.shasta.trongrid.io',
        ]));
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::__construct
     * @covers \pingi\TronTrxAPI\Address::__construct
     * @covers \pingi\TronTrxAPI\Wallet::generateAddress
     * @covers \pingi\TronTrxAPI\Wallet::genKeyPair
     */
    public function testGenerateAddress()
    {
        $wallet = new pingi\TronTrxAPI\Wallet($this->_api);

        $address = $wallet->generateAddress();
        $this->assertInstanceOf(Address::class, $address);

        return $address;
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::validateAddress
     * @covers \pingi\TronTrxAPI\Address::isValid
     */
    public function testValidatingAddress()
    {
        $wallet = new pingi\TronTrxAPI\Wallet($this->_api);

        $address = new Address('test', '', '');
        $this->assertFalse($address->isValid());

        $address = new Address('1234567890123456789012345678901221', '', '');
        $this->assertFalse($address->isValid());
        $this->assertFalse($wallet->validateAddress($address));

        for ($i = 0; $i < 3; $i++) {
            $address = $wallet->generateAddress();
            $this->assertEquals($wallet->validateAddress($address), $address->isValid());
        }
    }

    /**
     * @depends testGenerateAddress
     */
    public function testSignatureHexSigning(Address $address)
    {
        $wallet = new pingi\TronTrxAPI\Wallet($this->_api);

        $toHex = $wallet->toHex($address->address);
        $this->assertEquals($address->hexAddress, $toHex);
        $this->assertEquals($address->address, $wallet->hexString2Address($toHex));
    }


    /**
     * @covers \pingi\TronTrxAPI\Wallet::easyTransferByPrivate
     * @return Address
     */
    public function testEasyTransferByPrivateThrowsException()
    {
        $this->expectException(Exception::class);

        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);
        $address = $wallet->generateAddress();

        $this->assertTrue($wallet->easyTransferByPrivate(
            'PRIVATE',
            $address,
            1
        ));
    }


    /**
     * @covers \pingi\TronTrxAPI\Wallet::getAccount
     * @covers \pingi\TronTrxAPI\Account::__construct
     */
    public function testGetAccount()
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);
        $address = $wallet->generateAddress();
        $this->instantiateAddress($address);

        $account = $wallet->getAccount($address);

        $this->assertInstanceOf(Account::class, $account);

        return $account;
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::getAccount
     */
    public function testGetAccountReturnsNullOnFail()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $api = new Api($client);
        $wallet = new \pingi\TronTrxAPI\Wallet($api);

        $address = new Address('NOT_VALID', '', $wallet->toHex('NOT_VALID'));
        $this->assertNull($wallet->getAccount($address));
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::createTransaction
     * @covers \pingi\TronTrxAPI\Transaction::__construct
     */
    public function testCreateTransaction()
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);

        $toAddress = $wallet->generateAddress();
        $fromAddress = $wallet->generateAddress();
        $this->instantiateAddress($fromAddress);

        $transaction = $wallet->createTransaction($toAddress, $fromAddress, self::TEST_TRANSACTION_AMOUNT);
        $this->assertInstanceOf(Transaction::class, $transaction);

        return ['transaction' => $transaction, 'address' => $fromAddress];
    }

    /**
     * @covers  \pingi\TronTrxAPI\Wallet::signTransaction
     * @depends testCreateTransaction
     */
    public function testSignTransaction(array $input)
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);

        /** @var Transaction $transaction */
        $transaction = $input['transaction'];

        $wallet->signTransaction($transaction, $input['address']->privateKey);

        $this->assertTrue($transaction->isSigned());

        return $transaction;
    }

    /**
     * @covers  \pingi\TronTrxAPI\Wallet::broadcastTransaction
     */
    public function testBroadcastTransactionFailsWhenNotSigned()
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);

        $this->expectException(\Exception::class);
        $wallet->broadcastTransaction(new Transaction('', new stdClass()));
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::broadcastTransaction
     * @covers \pingi\TronTrxAPI\Wallet::getTransactionById
     * @depends testSignTransaction
     */
    public function testBroadcastTransaction(Transaction $transaction)
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);
        $hexAddress = $transaction->raw_data->contract[0]->parameter->value->owner_address;

        $address = new Address($wallet->fromHex($hexAddress), '', $hexAddress);

        $beforeTransactionAccunt = $wallet->getAccount($address);
        $this->assertNotNull($beforeTransactionAccunt);

        $this->assertTrue($wallet->broadcastTransaction($transaction));
        $this->assertEquals($transaction->txID, $wallet->getTransactionById($transaction->txID)->txID);

        echo 'Success test transaction' . PHP_EOL;
        echo 'From Address: https://explorer.shasta.trongrid.io/address/' . $address->address . PHP_EOL;
        echo 'Transaction: https://explorer.shasta.trongrid.io/transaction/' . $transaction->txID . PHP_EOL;
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::getAccountNet
     */
    public function testGetAccountNet()
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);
        $netAccountTest = $wallet->generateAddress();

        $this->instantiateAddress($netAccountTest);

        $response = $wallet->getAccountNet($netAccountTest);
        $this->assertArrayHasKey('freeNetLimit', $response);
        $this->assertArrayHasKey('TotalNetLimit', $response);
    }


    /**
     * @depends testGetAccount
     * @covers  \pingi\TronTrxAPI\Wallet::getAccountNet
     */
    public function testGetAccountNetMock(Account $account)
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);
        $this->assertNull($wallet->getAccountNet($account->address));

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'freeNetLimit'   => 5000,
                'TotalNetLimit'  => 43200000000,
                'TotalNetWeight' => 5989300712,
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $api = new Api($client);
        $wallet = new \pingi\TronTrxAPI\Wallet($api);

        $this->assertNotEmpty($wallet->getAccountNet($account->address));
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::getNowBlock
     */
    public function testNowBlock()
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);
        $block = $wallet->getNowBlock();
        $this->assertInstanceOf(\pingi\TronTrxAPI\Block::class, $block);

        return $block;
    }

    /**
     * @covers \pingi\TronTrxAPI\Wallet::getBlockById
     * @depends testNowBlock
     */
    public function testBlockById(Block $block)
    {
        $wallet = new \pingi\TronTrxAPI\Wallet($this->_api);
        $blockById = $wallet->getBlockById($block->blockID);

        $this->assertEquals($block, $blockById);

        $this->expectException(TronErrorException::class);
        $wallet->getBlockById('InvalidBlockId');
    }

    public function testFreezeBalance()
    {
        //Freeze balance of 2, then check accountnet for bandwidth.
//        $wallet->freezeBalance($address, 1000001, 1);
//        $wallet->getAccountNet($address);
        $this->markTestSkipped();
    }

    /**
     * @param Address $fromAddress
     */
    private function instantiateAddress(Address $fromAddress)
    {
        $this->assertEquals(200, $this->_api->getClient()->post('https://www.trongrid.io/shasta/submit', [
            'form_params' => [
                'value' => $fromAddress->address,
            ],
        ])->getStatusCode());
    }
}
