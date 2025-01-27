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
    
    if (!empty($sessions['data'])) {
        $sessionId = $sessions['data'][0]['session_id'];
        
        // Verificar estado de la sesión
        if ($sessions['data'][0]['status'] === 'ready') {
            // Enviar un mensaje
            $result = $client->sendMessage(
                '34612345678',  // Número destinatario o ID de grupo
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

## Límites y Restricciones

- Tamaño máximo de archivos: 16MB
- Longitud máxima del nombre de archivo: 255 caracteres
- Longitud máxima de caption: 1024 caracteres
- Formatos de video soportados: mp4, 3gp, mov
- Formatos de documentos Office soportados: doc, docx, xls, xlsx, ppt, pptx

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
$client->sendMessage($to, $message, $sessionId);

// Enviar imagen
$client->sendImage($to, $imageUrl, $caption, $sessionId);

// Enviar video
$client->sendVideo($to, $videoUrl, $caption, $sessionId);

// Enviar PDF
$client->sendPDF($to, $pdfUrl, $filename, $caption, $sessionId);

// Enviar documento de Office
$client->sendOfficeDocument($to, $documentUrl, $filename, $caption, $sessionId);

// Enviar archivo ZIP
$client->sendZipFile($to, $zipUrl, $filename, $caption, $sessionId);

// Enviar ubicación
$client->sendLocation($to, $latitude, $longitude, $description, $sessionId);
```

### Contactos y Grupos

```php
// Obtener lista de contactos
$contacts = $client->getContacts($sessionId);

// Obtener lista de grupos
$groups = $client->getGroups($sessionId);

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
    $sessions = $client->getSessions();

    if (!empty($sessions['data'])) {
        $sessionId = $sessions['data'][0]['session_id'];
        
        // Verificar autenticación
        $auth = $client->checkAuth($sessionId);
        
        if ($auth['authenticated']) {
            // Enviar mensaje
            $result = $client->sendMessage(
                '34612345678',
                '¡Hola desde el SDK!',
                $sessionId
            );
            
            if ($result['status'] === 'success') {
                echo "\nMensaje enviado exitosamente\n";
            }
            
            // Enviar documento
            $documentResult = $client->sendOfficeDocument(
                '34612345678',
                'https://ejemplo.com/documento.docx',
                'informe.docx',
                'Informe mensual',
                $sessionId
            );

            // Obtener grupos
            $groups = $client->getGroups($sessionId);
            if (!empty($groups['groups'])) {
                // Enviar mensaje a un grupo
                $groupId = $groups['groups'][0]['id'];
                $client->sendMessage(
                    $groupId,
                    'Mensaje para el grupo',
                    $sessionId
                );
            }
        } else {
            echo "\nLa sesión no está autenticada\n";
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

El SDK utiliza la clase `WhatsAppException` para manejar diferentes tipos de errores:

```php
try {
    $result = $client->sendMessage($to, $message, $sessionId);
} catch (WhatsAppException $e) {
    // Verificar tipo específico de error
    if ($e->isValidationError()) {
        // Manejar errores de validación (tamaño archivo, longitud nombre, etc)
        switch ($e->getCode()) {
            case WhatsAppException::ERROR_FILE_SIZE:
                echo "El archivo excede el límite de 16MB";
                break;
            case WhatsAppException::ERROR_FILENAME_LENGTH:
                echo "Nombre de archivo demasiado largo";
                break;
            case WhatsAppException::ERROR_CAPTION_LENGTH:
                echo "Caption demasiado largo";
                break;
        }
    } elseif ($e->isAuthenticationError()) {
        // Manejar errores de autenticación y permisos
        if ($e->isErrorType(WhatsAppException::ERROR_GROUP_PERMISSION)) {
            echo "Sin permisos en el grupo";
        }
    }
    
    // También puedes obtener:
    echo $e->getMessage();      // Mensaje de error
    echo $e->getCode();        // Código de error
    $errorData = $e->getErrorData(); // Datos adicionales
}
```

### Tipos de Errores Específicos

1. **Errores de Validación** (`isValidationError()`):
   - `ERROR_FILE_SIZE`: Archivo excede 16MB
   - `ERROR_FILENAME_LENGTH`: Nombre excede 255 caracteres
   - `ERROR_CAPTION_LENGTH`: Caption excede 1024 caracteres
   - `ERROR_INVALID_FILE_FORMAT`: Formato de archivo no soportado

2. **Errores de Autenticación** (`isAuthenticationError()`):
   - `ERROR_GROUP_PERMISSION`: Sin permisos en grupo
   - `ERROR_SESSION_INVALID`: Sesión inválida o expirada
   - `ERROR_AUTHENTICATION`: Error de autenticación general
```

## Buenas Prácticas

1. **Manejo de Archivos**
   - Verifica el tamaño del archivo antes de enviar (máx 16MB)
   - Usa nombres de archivo descriptivos y cortos (máx 255 caracteres)
   - Incluye la extensión correcta según el tipo de archivo

2. **Mensajes y Captions**
   - Mantén los captions concisos (máx 1024 caracteres)
   - Usa formato de números internacional para teléfonos
   - Verifica que los grupos permitan mensajes antes de enviar

3. **Gestión de Sesiones**
   - Verifica el estado de autenticación antes de enviar mensajes
   - Maneja adecuadamente las desconexiones
   - Implementa reintentos para errores temporales

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

- Documentación de la API: [wapi2.com/docs](https://wapi2.com/docs)
- Reportar issues: [GitHub Issues](https://github.com/wapi2/whatsapp-php-sdk/issues)