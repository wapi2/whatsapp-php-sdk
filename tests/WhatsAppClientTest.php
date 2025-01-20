<?php

namespace Wapi2\WhatsApp\Tests;

use PHPUnit\Framework\TestCase;
use Wapi2\WhatsApp\WhatsAppClient;
use Wapi2\WhatsApp\Exception\WhatsAppException;

class WhatsAppClientTest extends TestCase
{
    /**
     * @var WhatsAppClient
     */
    private $client;

    /**
     * @var string
     */
    private $testToken = 'test-token-123';

    /**
     * @var string
     */
    private $sessionId = 'test-session-id';

    /**
     * @var string
     */
    private $testPhone = '34612345678';

    protected function setUp()
    {
        $this->client = new class($this->testToken) extends WhatsAppClient {
            public $testResponse;
            public $testHttpCode = 200;
            public $testError = '';

            protected function executeCurl($ch)
            {
                return $this->testResponse;
            }

            protected function getCurlInfo($ch, $opt)
            {
                return $this->testHttpCode;
            }

            protected function getCurlError($ch)
            {
                return $this->testError;
            }
        };
    }

    public function testCheckAuthSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'authenticated' => true,
            'data' => [
                'session_id' => $this->sessionId,
                'status' => 'authenticated'
            ]
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->checkAuth($this->sessionId);
        
        $this->assertEquals('success', $response['status']);
        $this->assertTrue($response['authenticated']);
    }

    public function testGetSessionsSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'data' => [
                [
                    'session_id' => 'session1',
                    'status' => 'authenticated',
                    'created_at' => '2024-01-18T10:00:00Z'
                ]
            ]
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->getSessions();
        
        $this->assertEquals('success', $response['status']);
        $this->assertCount(1, $response['data']);
    }

    public function testSendMessageSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'message' => 'Message sent successfully'
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->sendMessage(
            $this->testPhone,
            'Test message',
            $this->sessionId
        );
        
        $this->assertEquals('success', $response['status']);
    }

    public function testConnectionError()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('Error en la petición cURL: Connection timeout');

        $this->client->testError = 'Connection timeout';
        
        $this->client->sendMessage(
            $this->testPhone,
            'Test message',
            $this->sessionId
        );
    }

    public function testInvalidJsonResponse()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('Error al decodificar la respuesta JSON');

        $this->mockCurlRequest('invalid json response');

        $this->client->sendMessage(
            $this->testPhone,
            'Test message',
            $this->sessionId
        );
    }

    private function mockCurlRequest($response, $httpCode = 200, $error = '')
    {
        $this->client->testResponse = is_array($response) ? json_encode($response) : $response;
        $this->client->testHttpCode = $httpCode;
        $this->client->testError = $error;
    }
}