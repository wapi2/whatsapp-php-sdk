# WhatsApp PHP SDK

SDK PHP ligero y eficiente para interactuar con la API de WhatsApp Web a través de wapi2.com.

## Requisitos Previos

1. PHP >= 7.0
2. Extensiones PHP: curl, json
3. Cuenta activa en [wapi2.com](https://wapi2.com)
4. Token de autenticación de wapi2.com
5. Al menos una sesión de WhatsApp creada en el panel de wapi2.com

## Instalación

```bash
composer require wapi2/whatsapp-php-sdk
```

## Configuración Inicial

### Creación de Sesiones
Antes de utilizar el SDK, debe crear una sesión de WhatsApp en [wapi2.com](https://wapi2.com):

1. Registre una cuenta en wapi2.com
2. Acceda a la sección "Sesiones"
3. Cree una nueva sesión
4. Escanee el código QR con WhatsApp Web desde su teléfono
5. Guarde el ID de sesión proporcionado para su uso con el SDK

## Uso Básico

```php
use Wapi2\WhatsApp\WhatsAppClient;
use Wapi2\WhatsApp\Exception\WhatsAppException;

try {
    // Inicializar el cliente
    $client = new WhatsAppClient('tu-token-bearer');

    // Obtener todas las sesiones disponibles
    $sessions = $client->getSessions();

    // Verificar el estado de una sesión específica
    $status = $client->checkAuth('ID-DE-SESION');

    // Enviar un mensaje usando una sesión activa
    $result = $client->sendMessage('34612345678', '¡Hola mundo!', 'ID-DE-SESION');

} catch (WhatsAppException $e) {
    echo "Error: " . $e->getMessage();
}
```

## Gestión de Sesiones

El SDK trabaja con sesiones previamente creadas en wapi2.com. Cada sesión representa una instancia de WhatsApp Web vinculada a un número de teléfono específico.

### Métodos de Autenticación Disponibles

- `checkAuth($sessionId)`: Verifica el estado de autenticación de una sesión específica
- `getSessions()`: Obtiene la lista de todas las sesiones disponibles en su cuenta

## Funcionalidades Principales

### Mensajes
- Envío de mensajes de texto
- Envío de imágenes con descripción opcional
- Envío de documentos PDF
- Envío de ubicaciones

### Grupos
- Envío de mensajes a grupos
- Envío de imágenes a grupos
- Envío de documentos PDF a grupos
- Envío de ubicaciones a grupos

### Contactos
- Obtención de lista de contactos
- Verificación de números registrados
- Obtención de información de contactos
- Obtención de fotos de perfil

## Manejo de Errores

El SDK utiliza la clase `WhatsAppException` para manejar diferentes tipos de errores:

```php
try {
    $result = $client->sendMessage('34612345678', 'Mensaje', 'ID-DE-SESION');
} catch (WhatsAppException $e) {
    if ($e->isAuthenticationError()) {
        echo "Error de autenticación: " . $e->getMessage();
    } elseif ($e->isSessionError()) {
        echo "Error en la sesión de WhatsApp: " . $e->getMessage();
    }
}
```

## Consideraciones Importantes

- Las sesiones deben ser creadas y autenticadas previamente en wapi2.com
- Cada sesión está vinculada a un número de teléfono específico
- El token de autenticación debe mantenerse seguro
- Se recomienda verificar el estado de la sesión antes de enviar mensajes

## Soporte

Para soporte técnico o reportar problemas:
- Abra un issue en GitHub
- Consulte la documentación en wapi2.com
- Contacte al soporte de wapi2.com para problemas relacionados con las sesiones

## Licencia

MIT