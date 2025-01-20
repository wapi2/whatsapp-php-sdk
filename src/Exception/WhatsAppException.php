<?php

namespace Wapi2\WhatsApp\Exception;

class WhatsAppException extends \Exception
{
    /**
     * @var array|null
     */
    private $errorData;

    /**
     * @param string $message
     * @param int $code
     * @param array|null $errorData
     * @param \Throwable|null $previous
     */
    public function __construct($message, $code = 0, $errorData = null, \Throwable $previous = null)
    {
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
}