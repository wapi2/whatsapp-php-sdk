<?php

namespace Wapi2\WhatsApp;

use Wapi2\WhatsApp\Exception\WhatsAppException;

class WhatsAppClient
{
    /**
     * @var string
     */
    private $apiUrl = 'https://wapi2.com';

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $token
     * @param string|null $apiUrl
     */
    public function __construct($token, $apiUrl = null)
    {
        $this->token = $token;
        if ($apiUrl !== null) {
            $this->apiUrl = rtrim($apiUrl, '/');
        }
        
        $this->headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    /**
     * Verificar estado de autenticación de una sesión
     * 
     * @param string $sessionId ID de la sesión creada en wapi2.com
     * @return array Estado actual de la sesión
     * @throws WhatsAppException
     */
    public function checkAuth($sessionId)
    {
        return $this->request('GET', "/auth/checkauth/{$sessionId}");
    }

    /**
     * Obtener todas las sesiones disponibles
     * 
     * @return array Lista de sesiones creadas en wapi2.com
     * @throws WhatsAppException
     */
    public function getSessions()
    {
        return $this->request('GET', '/auth/getsessions');
    }

    /**
     * Enviar mensaje de texto
     * 
     * @param string $to Número de teléfono o ID de grupo
     * @param string $message
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendMessage($to, $message, $sessionId)
    {
        return $this->request(
            'POST',
            "/chat/{$to}/message",
            ['message' => $message],
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar imagen
     * 
     * @param string $to Número de teléfono o ID de grupo
     * @param string $image URL o Base64 de la imagen
     * @param string|null $caption
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendImage($to, $image, $caption = null, $sessionId)
    {
        $this->validateCaption($caption);
        
        $data = ['image' => $image];
        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'POST',
            "/chat/{$to}/image",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar documento PDF
     * 
     * @param string $to Número de teléfono o ID de grupo
     * @param string $pdf URL o Base64 del PDF (máx 16MB)
     * @param string $filename Nombre del archivo (máx 255 caracteres)
     * @param string|null $caption Texto opcional (máx 1024 caracteres)
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendPDF($to, $pdf, $filename, $caption = null, $sessionId)
    {
        $this->validateFilename($filename);
        $this->validateCaption($caption);
        
        $data = [
            'pdf' => $pdf,
            'filename' => $filename
        ];
        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'POST',
            "/chat/{$to}/pdf",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar video
     * 
     * @param string $to Número de teléfono o ID de grupo
     * @param string $video URL o Base64 del video (mp4, 3gp, mov - máx 16MB)
     * @param string|null $caption Texto opcional (máx 1024 caracteres)
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendVideo($to, $video, $caption = null, $sessionId)
    {
        $this->validateCaption($caption);
        
        $data = ['video' => $video];
        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'POST',
            "/chat/{$to}/video",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar documento de Office
     * 
     * @param string $to Número de teléfono o ID de grupo
     * @param string $document URL o Base64 del documento (doc, docx, xls, xlsx, ppt, pptx - máx 16MB)
     * @param string $filename Nombre del archivo (máx 255 caracteres)
     * @param string|null $caption Texto opcional (máx 1024 caracteres)
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendOfficeDocument($to, $document, $filename, $caption = null, $sessionId)
    {
        $this->validateFilename($filename);
        $this->validateCaption($caption);
        
        // Validar formato de archivo Office
        $this->validateFileFormat($filename, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
        
        // Si es base64, validar tamaño
        if (preg_match('/^data:.*;base64,/', $document) || ctype_alnum($document)) {
            $this->validateFileSize($document);
        }
        
        $data = [
            'document' => $document,
            'filename' => $filename
        ];
        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'POST',
            "/chat/{$to}/office",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar archivo ZIP
     * 
     * @param string $to Número de teléfono o ID de grupo
     * @param string $document URL o Base64 del archivo ZIP (máx 16MB)
     * @param string $filename Nombre del archivo (máx 255 caracteres)
     * @param string|null $caption Texto opcional (máx 1024 caracteres)
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendZipFile($to, $document, $filename, $caption = null, $sessionId)
    {
        $this->validateFilename($filename);
        $this->validateCaption($caption);
        
        $data = [
            'document' => $document,
            'filename' => $filename
        ];
        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'POST',
            "/chat/{$to}/zip",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar ubicación
     * 
     * @param string $to Número de teléfono o ID de grupo
     * @param float $latitude
     * @param float $longitude
     * @param string|null $description
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendLocation($to, $latitude, $longitude, $description = null, $sessionId)
    {
        $data = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
        if ($description !== null) {
            $data['description'] = $description;
        }

        return $this->request(
            'POST',
            "/chat/{$to}/location",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Obtener todos los contactos
     * 
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function getContacts($sessionId)
    {
        return $this->request('GET', '/contact/getcontacts', [], ['session_id' => $sessionId]);
    }

    /**
     * Obtener todos los grupos
     * 
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function getGroups($sessionId)
    {
        return $this->request('GET', '/contact/getgroups', [], ['session_id' => $sessionId]);
    }

    /**
     * Verificar si un número está registrado en WhatsApp
     * 
     * @param string $phone
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function isRegisteredUser($phone, $sessionId)
    {
        return $this->request(
            'GET',
            "/contact/isregistereduser/{$phone}",
            [],
            ['session_id' => $sessionId]
        );
    }

    /**
     * Validar longitud del nombre de archivo
     * 
     * @param string $filename
     * @throws WhatsAppException
     */
    private function validateFilename($filename)
    {
        if (strlen($filename) > 255) {
            throw WhatsAppException::filenameLengthError();
        }
    }

    /**
     * Validar longitud del caption
     * 
     * @param string|null $caption
     * @throws WhatsAppException
     */
    private function validateCaption($caption)
    {
        if ($caption !== null && strlen($caption) > 1024) {
            throw WhatsAppException::captionLengthError();
        }
    }

    /**
     * Validar tamaño del archivo
     * 
     * @param string $fileContent
     * @throws WhatsAppException
     */
    /**
     * Validar tamaño del archivo
     * 
     * @param string $fileContent
     * @throws WhatsAppException
     */
    private function validateFileSize($fileContent)
    {
        // Extraer el contenido base64 si está en formato data URI
        if (preg_match('/^data:[^;]+;base64,(.+)$/', $fileContent, $matches)) {
            $fileContent = $matches[1];
        }

        // Decodificar el contenido base64
        $decodedContent = base64_decode($fileContent);
        if ($decodedContent === false) {
            throw new WhatsAppException(
                'Contenido base64 inválido',
                WhatsAppException::ERROR_INVALID_FILE_FORMAT
            );
        }

        if (strlen($decodedContent) > 16 * 1024 * 1024) { // 16MB
            throw new WhatsAppException(
                'El archivo excede el tamaño máximo permitido de 16MB',
                WhatsAppException::ERROR_FILE_SIZE
            );
        }
    }

    /**
     * Validar formato de archivo
     * 
     * @param string $filename
     * @param array $allowedExtensions
     * @throws WhatsAppException
     */
    private function validateFileFormat($filename, array $allowedExtensions)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            throw new WhatsAppException(
                sprintf('El formato de archivo %s no está soportado', $extension),
                WhatsAppException::ERROR_INVALID_FILE_FORMAT
            );
        }
    }

    /**
     * Realizar petición HTTP
     * 
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $query
     * @return array
     * @throws WhatsAppException
     */
    protected function request($method, $endpoint, array $data = [], array $query = [])
    {
        $url = $this->apiUrl . $endpoint;
        
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = $this->executeCurl($ch);
        $httpCode = $this->getCurlInfo($ch, CURLINFO_HTTP_CODE);
        $error = $this->getCurlError($ch);
        curl_close($ch);

        if ($error) {
            throw new WhatsAppException("Error en la petición cURL: $error");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WhatsAppException('Error al decodificar la respuesta JSON');
        }

        if ($httpCode >= 400) {
            throw new WhatsAppException(
                isset($decoded['message']) ? $decoded['message'] : 'Error en la petición',
                $httpCode,
                $decoded
            );
        }

        return $decoded;
    }

    /**
     * Ejecuta la petición cURL
     * @param resource $ch
     * @return string|bool
     */
    protected function executeCurl($ch)
    {
        return curl_exec($ch);
    }

    /**
     * Obtiene información de la petición cURL
     * @param resource $ch
     * @param int $opt
     * @return mixed
     */
    protected function getCurlInfo($ch, $opt)
    {
        return curl_getinfo($ch, $opt);
    }

    /**
     * Obtiene el error de cURL si existe
     * @param resource $ch
     * @return string
     */
    protected function getCurlError($ch)
    {
        return curl_error($ch);
    }
}