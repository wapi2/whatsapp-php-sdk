# WhatsApp PHP SDK

SDK PHP para interactuar con la API de WhatsApp Web mediante wapi2.com. Este SDK proporciona una interfaz simple y eficiente para integrar funcionalidades de WhatsApp en tus aplicaciones PHP.

## Prerequisitos

- PHP >= 7.0
- Extensiones PHP: curl, json
- Cuenta activa en [wapi2.com](https://wapi2.com)
- Token de autenticación de wapi2.com
- Una sesión de WhatsApp activa en wapi2.com

## Instalación

```bash
composer require wapi2/whatsapp-php-sdk
```

## Configuración Inicial

Antes de usar el SDK:

1. Regístrate en [wapi2.com](https://wapi2.com)
2. Accede a tu panel de control
3. Obtén tu token de autenticación
4. Crea una sesión en el apartado "Sesiones"
5. Vincula tu número de WhatsApp mediante el código QR

## Uso Básico

```php
<?php
require 'vendor/autoload.php';

use Wapi2\WhatsApp\WhatsAppClient;
use Wapi2\WhatsApp\Exception\WhatsAppException;

// Configurar cliente
$token = 'tu-token-de-wapi2';
$client = new WhatsAppClient($token);

try {
    // Obtener sesiones disponibles
    $sessions = $client->getSessions();
    
    if (!empty($sessions['message'])) {
        $sessionId = $sessions['message'][0]['session_id'];
        $phoneNumber = $sessions['message'][0]['phone_number'];
        
        // Verificar estado de la sesión
        if ($sessions['message'][0]['status'] === 'ready') {
            // Enviar un mensaje
            $result = $client->sendMessage(
                '34612345678',  // Número destinatario
                '¡Hola desde WhatsApp!',
                $sessionId
            );
            print_r($result);
        }
    }
} catch (WhatsAppException $e) {
    echo "Error: " . $e->getMessage();
}
```

## Funcionalidades Disponibles

### Gestión de Sesiones

```php
// Obtener todas las sesiones
$sessions = $client->getSessions();

// Verificar estado de una sesión
$status = $client->checkAuth($sessionId);
```

### Mensajería

```php
// Enviar mensaje de texto
$client->sendMessage($phone, $message, $sessionId);

// Enviar imagen
$client->sendImage($phone, $imageUrl, $caption, $sessionId);

// Enviar PDF
$client->sendPDF($phone, $pdfUrl, $caption, $sessionId);

// Enviar ubicación
$client->sendLocation($phone, $latitude, $longitude, $description, $sessionId);
```

### Contactos

```php
// Obtener lista de contactos
$contacts = $client->getContacts($sessionId);

// Verificar si un número está en WhatsApp
$isRegistered = $client->isRegisteredUser($phone, $sessionId);
```

## Ejemplo Completo de Implementación

```php
<?php
require 'vendor/autoload.php';

use Wapi2\WhatsApp\WhatsAppClient;
use Wapi2\WhatsApp\Exception\WhatsAppException;

$token = 'tu-token-de-wapi2';

try {
    // Inicializar el cliente
    $client = new WhatsAppClient($token);

    // Obtener sesiones disponibles
    echo "Obteniendo sesiones...\n";
    $sessions = $client->getSessions();

    if (!empty($sessions['message'])) {
        $sessionId = $sessions['message'][0]['session_id'];
        
        echo "\nSesión encontrada:";
        echo "\nID: " . $sessionId;
        echo "\nNúmero: " . $sessions['message'][0]['phone_number'];
        echo "\nEstado: " . $sessions['message'][0]['status'] . "\n";

        // Verificar autenticación
        $auth = $client->checkAuth($sessionId);
        
        if ($sessions['message'][0]['status'] === 'ready') {
            // Enviar mensaje
            $result = $client->sendMessage(
                '34612345678',
                '¡Hola desde el SDK!',
                $sessionId
            );
            
            if ($result['status'] === 'success') {
                echo "\nMensaje enviado exitosamente\n";
            }
            
            // Enviar imagen
            $imageResult = $client->sendImage(
                '34612345678',
                'https://ejemplo.com/imagen.jpg',
                'Descripción de la imagen',
                $sessionId
            );
        } else {
            echo "\nLa sesión no está lista para enviar mensajes\n";
        }
    } else {
        echo "\nNo se encontraron sesiones disponibles\n";
    }

} catch (WhatsAppException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    if ($errorData = $e->getErrorData()) {
        echo "Datos adicionales del error:\n";
        print_r($errorData);
    }
}
```

## Manejo de Errores

El SDK utiliza la clase `WhatsAppException` para manejar errores:

```php
try {
    $result = $client->sendMessage($phone, $message, $sessionId);
} catch (WhatsAppException $e) {
    // Obtener mensaje de error
    echo $e->getMessage();
    
    // Obtener código de error
    echo $e->getCode();
    
    // Obtener datos adicionales del error
    $errorData = $e->getErrorData();
}
```

## Contribuir

Las contribuciones son bienvenidas:

1. Fork el repositorio
2. Crea tu rama de feature (`git checkout -b feature/amazing-feature`)
3. Commit tus cambios (`git commit -m 'Add some amazing feature'`)
4. Push a la rama (`git push origin feature/amazing-feature`)
5. Abre un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## Soporte

- Documentación de la API: [wapi2.com/docs](https://wapi2.com/api-docs)
- Reportar issues: [GitHub Issues](https://github.com/wapi2/whatsapp-php-sdk/issues)