<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Error;

use Arikaim\Core\Utils\Text;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\System\Config;

/**
 * Errors
 */
class Errors extends Collection
{
    /**
     * Prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * Errors
     *
     * @var array
     */
    private $errors;

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->errors = [];
        $this->loadErrorsConfig();
    }

    /**
     * Add error
     *
     * @param string $errorCode
     * @param array $params
     * @return bool
     */
    public function addError($errorCode, $params = [])
    {       
        $message = ($this->hasErrorCode($errorCode) == true) ? $this->getError($errorCode,$params) : $errorCode;
        array_push($this->errors,$message);
     
        return true;
    }
    
    /**
     * Ger errors count
     *
     * @return integer
     */
    public function count()
    {
        return count($this->errors);
    }

    /**
     * Return true if have error
     *
     * @return boolean
     */
    public function hasError()
    {       
        return ($this->count() > 0) ? true : false;         
    }

    /**
     * Return true if error code exists
     *
     * @param string $code
     * @return boolean
     */
    public function hasErrorCode($code)
    {
        return $this->has($code);
    }

    /**
     * Get errors list
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get error code
     *
     * @param string $errorCode
     * @param string|null $default
     * @param array $params
     * @return string
     */
    public function getError($errorCode, $params = [], $default = 'UNKNOWN_ERROR') 
    {
        $error = $this->get($errorCode,null);
        $error = (empty($error) == true) ? $this->get($default,null) : $error;

        return (empty($error) == true) ? null : Text::render($this->prefix . $error['message'], $params);      
    }

    /**
     * Get upload error message
     *
     * @param integer $errorCode
     * @return string
     */
    public function getUplaodFileError($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return "";// no error                
            case UPLOAD_ERR_INI_SIZE:
                return $this->getError("UPLOAD_ERR_INI_SIZE");
            case UPLOAD_ERR_FORM_SIZE:
                return $this->getError("UPLOAD_ERR_FORM_SIZE");
            case UPLOAD_ERR_PARTIAL:
                return $this->getError("UPLOAD_ERR_PARTIAL");
            case UPLOAD_ERR_NO_FILE:
                return $this->getError("UPLOAD_ERR_NO_FILE");
            case UPLOAD_ERR_NO_TMP_DIR:
                return $this->getError("UPLOAD_ERR_NO_TMP_DIR");
            case UPLOAD_ERR_CANT_WRITE:
                return $this->getError("UPLOAD_ERR_CANT_WRITE");
            case UPLOAD_ERR_EXTENSION:
                return $this->getError("UPLOAD_ERR_EXTENSION");
        }

        return "";
    }
    
    /**
     * Load error messages file.
     *
     * @return void
     */
    private function loadErrorsConfig() 
    {
        $list = Config::loadJsonConfigFile('errors.json');         
        $this->data = $list['errors'];
        $this->prefix = $list['prefix'];   
    }
}
