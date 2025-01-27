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
    private $testDestination = '34612345678';

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
            $this->testDestination,
            'Test message',
            $this->sessionId
        );
        
        $this->assertEquals('success', $response['status']);
    }

    public function testSendImageSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'message' => 'Image sent successfully'
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->sendImage(
            $this->testDestination,
            'base64://test-image',
            'Test caption',
            $this->sessionId
        );

        $this->assertEquals('success', $response['status']);
    }

    public function testSendVideoSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'message' => 'Video sent successfully'
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->sendVideo(
            $this->testDestination,
            'https://example.com/video.mp4',
            'Test video caption',
            $this->sessionId
        );

        $this->assertEquals('success', $response['status']);
    }

    public function testSendOfficeDocumentSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'message' => 'Document sent successfully'
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->sendOfficeDocument(
            $this->testDestination,
            'https://example.com/document.docx',
            'test_document.docx',
            'Test document caption',
            $this->sessionId
        );

        $this->assertEquals('success', $response['status']);
    }

    public function testSendZipFileSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'message' => 'ZIP file sent successfully'
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->sendZipFile(
            $this->testDestination,
            'https://example.com/file.zip',
            'test_archive.zip',
            'Test ZIP caption',
            $this->sessionId
        );

        $this->assertEquals('success', $response['status']);
    }

    public function testGetGroupsSuccess()
    {
        $expectedResponse = [
            'status' => 'success',
            'groups' => [
                [
                    'id' => 'group1',
                    'name' => 'Test Group',
                    'isAdmin' => true,
                    'canPost' => true
                ]
            ]
        ];

        $this->mockCurlRequest($expectedResponse);
        $response = $this->client->getGroups($this->sessionId);

        $this->assertEquals('success', $response['status']);
        $this->assertCount(1, $response['groups']);
    }

    public function testFilenameExceedsMaxLength()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionCode(WhatsAppException::ERROR_FILENAME_LENGTH);

        $longFilename = str_repeat('a', 256) . '.pdf';
        $this->client->sendPDF(
            $this->testDestination,
            'https://example.com/doc.pdf',
            $longFilename,
            'Test caption',
            $this->sessionId
        );
    }

    public function testFileSizeExceedsMaxLimit()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionCode(WhatsAppException::ERROR_FILE_SIZE);
        $this->expectExceptionMessage('El archivo excede el tamaño máximo permitido de 16MB');

        // Crear un string base64 que represente un archivo grande
        $largeContent = 'data:application/pdf;base64,' . base64_encode(str_repeat('a', 17 * 1024 * 1024));
        
        // Mock la respuesta del servidor para evitar el procesamiento JSON
        $this->mockCurlRequest(['status' => 'success'], 200);
        
        $this->client->sendOfficeDocument(
            $this->testDestination,
            $largeContent,
            'test.docx',
            'Test caption',
            $this->sessionId
        );
    }

    public function testInvalidFileFormat()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionCode(WhatsAppException::ERROR_INVALID_FILE_FORMAT);
        $this->expectExceptionMessage('El formato de archivo txt no está soportado');

        $this->client->sendOfficeDocument(
            $this->testDestination,
            'https://example.com/document.txt',
            'document.txt',
            'Test caption',
            $this->sessionId
        );
    }

    public function testCaptionExceedsMaxLength()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionCode(WhatsAppException::ERROR_CAPTION_LENGTH);

        $longCaption = str_repeat('a', 1025);
        $this->client->sendImage(
            $this->testDestination,
            'https://example.com/image.jpg',
            $longCaption,
            $this->sessionId
        );
    }

    public function testConnectionError()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('Error en la petición cURL: Connection timeout');

        $this->client->testError = 'Connection timeout';
        $this->client->sendMessage(
            $this->testDestination,
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
            $this->testDestination,
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