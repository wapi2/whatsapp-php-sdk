<?php

namespace Wapi2\WhatsApp\Exception;

class WhatsAppException extends \Exception
{
    /**
     * @var array|null
     */
    private $errorData;

    /**
     * Códigos de error específicos
     */
    const ERROR_FILE_SIZE = 1001;
    const ERROR_FILENAME_LENGTH = 1002;
    const ERROR_CAPTION_LENGTH = 1003;
    const ERROR_INVALID_FILE_FORMAT = 1004;
    const ERROR_GROUP_PERMISSION = 1005;
    const ERROR_SESSION_INVALID = 1006;
    const ERROR_CONNECTION = 1007;
    const ERROR_AUTHENTICATION = 1008;

    /**
     * Mensajes de error predefinidos
     */
    const ERROR_MESSAGES = [
        self::ERROR_FILE_SIZE => 'El archivo excede el tamaño máximo permitido de 16MB',
        self::ERROR_FILENAME_LENGTH => 'El nombre del archivo no puede exceder los 255 caracteres',
        self::ERROR_CAPTION_LENGTH => 'El caption no puede exceder los 1024 caracteres',
        self::ERROR_INVALID_FILE_FORMAT => 'Formato de archivo no soportado',
        self::ERROR_GROUP_PERMISSION => 'No tienes permisos para enviar mensajes en este grupo',
        self::ERROR_SESSION_INVALID => 'La sesión no es válida o ha expirado',
        self::ERROR_CONNECTION => 'Error de conexión con el servidor',
        self::ERROR_AUTHENTICATION => 'Error de autenticación'
    ];

    /**
     * @param string $message
     * @param int $code
     * @param array|null $errorData
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, $errorData = null, \Throwable $previous = null)
    {
        // Si se proporciona un código de error conocido pero no un mensaje,
        // usar el mensaje predefinido
        if ($code !== 0 && empty($message) && isset(self::ERROR_MESSAGES[$code])) {
            $message = self::ERROR_MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
        $this->errorData = $errorData;
    }

    /**
     * Obtener datos adicionales del error
     * 
     * @return array|null
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * Verifica si el error es de un tipo específico
     * 
     * @param int $errorCode
     * @return bool
     */
    public function isErrorType($errorCode)
    {
        return $this->code === $errorCode;
    }

    /**
     * Verifica si el error está relacionado con límites o validaciones
     * 
     * @return bool
     */
    public function isValidationError()
    {
        return in_array($this->code, [
            self::ERROR_FILE_SIZE,
            self::ERROR_FILENAME_LENGTH,
            self::ERROR_CAPTION_LENGTH,
            self::ERROR_INVALID_FILE_FORMAT
        ]);
    }

    /**
     * Verifica si el error está relacionado con permisos o autenticación
     * 
     * @return bool
     */
    public function isAuthenticationError()
    {
        return in_array($this->code, [
            self::ERROR_GROUP_PERMISSION,
            self::ERROR_SESSION_INVALID,
            self::ERROR_AUTHENTICATION
        ]);
    }

    /**
     * Crea una nueva instancia para error de tamaño de archivo
     * 
     * @param array|null $errorData
     * @return self
     */
    public static function fileSizeError($errorData = null)
    {
        return new self('', self::ERROR_FILE_SIZE, $errorData);
    }

    /**
     * Crea una nueva instancia para error de longitud de nombre de archivo
     * 
     * @param array|null $errorData
     * @return self
     */
    public static function filenameLengthError($errorData = null)
    {
        return new self('', self::ERROR_FILENAME_LENGTH, $errorData);
    }

    /**
     * Crea una nueva instancia para error de longitud de caption
     * 
     * @param array|null $errorData
     * @return self
     */
    public static function captionLengthError($errorData = null)
    {
        return new self('', self::ERROR_CAPTION_LENGTH, $errorData);
    }

    /**
     * Crea una nueva instancia para error de formato de archivo
     * 
     * @param string $format Formato no soportado
     * @param array|null $errorData
     * @return self
     */
    public static function invalidFileFormatError($format, $errorData = null)
    {
        return new self(
            sprintf('El formato de archivo %s no está soportado', $format),
            self::ERROR_INVALID_FILE_FORMAT,
            $errorData
        );
    }
}