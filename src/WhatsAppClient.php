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
     * @param string $phone
     * @param string $message
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendMessage($phone, $message, $sessionId)
    {
        return $this->request(
            'POST',
            "/chat/{$phone}/message",
            ['message' => $message],
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar imagen
     * 
     * @param string $phone
     * @param string $image URL o Base64 de la imagen
     * @param string|null $caption
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendImage($phone, $image, $caption, $sessionId)
    {
        $data = ['image' => $image];
        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'POST',
            "/chat/{$phone}/image",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar documento PDF
     * 
     * @param string $phone
     * @param string $pdf URL o Base64 del PDF
     * @param string|null $caption
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendPDF($phone, $pdf, $caption, $sessionId)
    {
        $data = ['pdf' => $pdf];
        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'POST',
            "/chat/{$phone}/pdf",
            $data,
            ['session_id' => $sessionId]
        );
    }

    /**
     * Enviar ubicación
     * 
     * @param string $phone
     * @param float $latitude
     * @param float $longitude
     * @param string|null $description
     * @param string $sessionId
     * @return array
     * @throws WhatsAppException
     */
    public function sendLocation($phone, $latitude, $longitude, $description, $sessionId)
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
            "/chat/{$phone}/location",
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
                $httpCode
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