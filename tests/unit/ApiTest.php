<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use pingi\TronTrxAPI\Api;
use pingi\TronTrxAPI\Exceptions\TronErrorException;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    /**
     * @covers \pingi\TronTrxAPI\Api::getClient
     * @covers \pingi\TronTrxAPI\Api::__construct
     */
    public function testGetClientReturnsInstanceOfClient()
    {
        $api = new Api(new Client());
        $this->assertInstanceOf(Client::class, $api->getClient());
    }

    /**
     * @covers \pingi\TronTrxAPI\Api::post
     * @covers \pingi\TronTrxAPI\Api::checkForErrorResponse
     */
    public function testPostAssocTrueFalse()
    {
        // Create a mock and queue two responses.
        $response = new Response(200, [], json_encode([
            'test' => true,
        ]));

        $mock = new MockHandler([
            $response,
            $response,
            new Response(200, [], json_encode(['Error' => 'Error'])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $api = new Api($client);
        $this->assertArrayHasKey('test', $api->post('/test', ['data' => []], true));

        $response = $api->post('/test');
        $this->assertObjectHasAttribute('test', $response);
        $this->assertTrue($response->test);
    }

    /**
     * @covers \pingi\TronTrxAPI\Api::checkForErrorResponse
     * @dataProvider getResponses
     */
    public function testErrorExceptionIsThrownWithAssoc($client, $assoc)
    {
        $api = new Api($client);

        $this->expectException(TronErrorException::class);
        $api->post('/test', [], $assoc);
    }

    public function getResponses()
    {
        $errorResponse = new Response(200, [], json_encode(['Error' => 'Error']));
        $codeResponse = new Response(200, [], json_encode(['code' => 'code', 'message' => bin2hex('test message')]));

        $mock = new MockHandler([
            $errorResponse,
            $errorResponse,
            $codeResponse,
            $codeResponse,
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return [
            [$client, true],
            [$client, false],
            [$client, true],
            [$client, false],
        ];
    }
}