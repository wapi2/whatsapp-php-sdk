# Testing WhatsApp PHP SDK

Esta guía describe cómo ejecutar y mantener los tests del SDK.

## Requisitos

- PHP >= 7.0
- PHPUnit 6.0 o superior
- Composer

## Configuración Inicial

1. Instalar dependencias:
```bash
composer install
```

2. Verificar que PHPUnit está instalado correctamente:
```bash
./vendor/bin/phpunit --version
```

## Ejecutar Tests

### Todos los Tests
```bash
./vendor/bin/phpunit
```

### Tests Específicos
```bash
# Ejecutar una clase de test específica
./vendor/bin/phpunit tests/WhatsAppClientTest.php

# Ejecutar un método de test específico
./vendor/bin/phpunit --filter testSendMessageSuccess

# Ejecutar tests con un patrón específico
./vendor/bin/phpunit --filter 'testSend*'
```

### Cobertura de Código
```bash
# Generar reporte HTML de cobertura
./vendor/bin/phpunit --coverage-html coverage

# Ver cobertura en terminal
./vendor/bin/phpunit --coverage-text
```

## Estructura de los Tests

```
tests/
└── WhatsAppClientTest.php   # Tests principales del cliente WhatsApp
```

### Grupos de Tests

1. **Autenticación**
   - testCheckAuthSuccess
   - testCheckAuthFailure
   - testGetSessionsSuccess

2. **Mensajería**
   - testSendMessageSuccess
   - testSendImageSuccess
   - testSendVideoSuccess
   - testSendPDFSuccess
   - testSendOfficeDocumentSuccess
   - testSendZipFileSuccess
   - testSendLocationSuccess

3. **Grupos y Contactos**
   - testGetContactsSuccess
   - testGetGroupsSuccess
   - testIsRegisteredUserSuccess

4. **Validaciones**
   - testFilenameExceedsMaxLength
   - testCaptionExceedsMaxLength

5. **Manejo de Errores**
   - testConnectionError
   - testInvalidJsonResponse
   - testUnauthorizedError
   - testSessionNotFoundError

## Mocking

Los tests utilizan una clase anónima que extiende de WhatsAppClient para simular las respuestas de la API:

```php
$this->client = new class($testToken) extends WhatsAppClient {
    public $testResponse;
    public $testHttpCode = 200;
    public $testError = '';

    protected function executeCurl($ch)
    {
        return $this->testResponse;
    }
    // ...
};
```

## Ejemplos de Tests

### Test de Envío de Video
```php
public function testSendVideoSuccess()
{
    $expectedResponse = [
        'status' => 'success',
        'message' => 'Video sent successfully'
    ];

    $this->mockCurlRequest($expectedResponse);
    $response = $this->client->sendVideo(
        '34612345678',
        'https://example.com/video.mp4',
        'Test caption',
        'test-session-id'
    );

    $this->assertEquals('success', $response['status']);
}
```

### Test de Excepciones Específicas
```php
class WhatsAppClientTest extends TestCase
{
    public function testFilenameExceedsMaxLength()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionCode(WhatsAppException::ERROR_FILENAME_LENGTH);

        $longFilename = str_repeat('a', 256) . '.pdf';
        $this->client->sendPDF(
            '34612345678',
            'https://example.com/doc.pdf',
            $longFilename,
            'Test caption',
            'test-session-id'
        );
    }

    public function testInvalidFileFormat()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionCode(WhatsAppException::ERROR_INVALID_FILE_FORMAT);

        $this->client->sendOfficeDocument(
            '34612345678',
            'https://example.com/doc.txt',
            'document.txt',
            'Test caption',
            'test-session-id'
        );
    }

    public function testGroupPermissionError()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionCode(WhatsAppException::ERROR_GROUP_PERMISSION);
        
        // Simular error de permisos
        $this->mockCurlRequest(
            ['error' => 'No tienes permisos para enviar mensajes en este grupo'],
            403
        );

        $this->client->sendMessage(
            'group-id',
            'Test message',
            'test-session-id'
        );
    }
}
```

## Consideraciones para Nuevos Tests

1. **Casos de Prueba Comunes**
   - Éxito de la operación
   - Validación de datos de entrada
   - Manejo de errores
   - Límites y restricciones

2. **Datos de Prueba**
   - Usar formatos válidos de números de teléfono
   - Respetar límites de tamaño y longitud
   - Incluir caracteres especiales cuando sea relevante

3. **Validaciones Específicas**
   - Tamaño máximo de archivos (16MB)
   - Longitud de nombres de archivo (255 caracteres)
   - Longitud de captions (1024 caracteres)
   - Formatos de archivo soportados

## Mantenimiento

- Ejecutar los tests antes de cada commit
- Mantener la cobertura de código por encima del 80%
- Actualizar la documentación cuando se añadan nuevos tests
- Revisar y actualizar los mocks según los cambios en la API

## CI/CD

Los tests se ejecutan automáticamente en GitHub Actions con cada push y pull request. Consulte el archivo `.github/workflows/php.yml` para más detalles.