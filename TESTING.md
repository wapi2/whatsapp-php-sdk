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
   - testSendMessageSessionError
   - testSendImageSuccess
   - testSendPDFSuccess
   - testSendLocationSuccess

3. **Manejo de Errores**
   - testConnectionError
   - testInvalidJsonResponse

## Mocking

Los tests utilizan una clase anónima que extiende de WhatsAppClient para simular las respuestas de la API. Esto se hace sobreescribiendo los métodos de cURL:

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

## Añadir Nuevos Tests

Al añadir nuevos tests, sigue estas pautas:

1. **Nombrado**: Use nombres descriptivos que indiquen qué se está probando
2. **Organización**: Agrupe tests relacionados juntos
3. **Documentación**: Añada comentarios PHPDoc a cada test
4. **Assertions**: Use assertions específicos y descriptivos

Ejemplo:
```php
/**
 * Test envío de mensaje exitoso
 */
public function testSendMessageSuccess()
{
    $expectedResponse = [
        'status' => 'success',
        'message' => 'Message sent successfully'
    ];

    $this->mockCurlRequest($expectedResponse);
    $response = $this->client->sendMessage(...);
    $this->assertEquals('success', $response['status']);
}
```

## Depuración

Para depurar tests específicos:

```bash
# Ejecutar con más detalle
./vendor/bin/phpunit --debug

# Ver stack trace completo
./vendor/bin/phpunit --verbose
```

## Mantenimiento

- Ejecute los tests antes de cada commit
- Mantenga la cobertura de código por encima del 80%
- Actualice la documentación cuando añada nuevos tests
- Revise y actualice los mocks según los cambios en la API

## CI/CD

Los tests se ejecutan automáticamente en GitHub Actions con cada push y pull request. Consulte el archivo `.github/workflows/php.yml` para más detalles.