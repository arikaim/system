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
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\Collection\Collection;

use Arikaim\Core\Interfaces\SystemErrorInterface;

/**
 * Errors
 */
class Errors extends Collection implements SystemErrorInterface
{
    /**
     * Errors file name
     *
     * @var string
     */
    protected $fileName;
    
    /**
     * Console errors filename
     *
     * @var string
     */
    protected $consoleFile;

    /**
     * Error file loaded
     *
     * @var bool
    */
    private $loaded;

    /**
     * Constructor
     * 
     * @param array $systemErrors
     */
    public function __construct(string $fileName, string $consoleFile, bool $consoleApp = false) 
    {
        //$this->data = $systemErrors; 
        $this->fileName = $fileName;
        $this->consoleFile = $consoleFile;
        $this->consoleApp = $consoleApp;
        $this->loaded = false;
    }

    /**
     * Load validation errors
     *
     * @param string $fileName
     * @return array
     */
    public function loadValidationErrors($fileName = 'validation-errors.json'): array
    {
        $data = File::readJsonFile(Path::CONFIG_PATH . $fileName);
        
        return ($data === false) ? [] : $data;
    }

    /**
     * Load erros
     *
     * @return void
     */
    protected function loadErrors(): void 
    {
        $errors = File::readJsonFile($this->fileName);
        $errors = ($errors === false) ? [] : $errors;
        
        if ($this->consoleApp == true) {
            $consoleErrors = File::readJsonFile($this->consoleFile);
            $consoleErrors = ($consoleErrors === false) ? [] : $consoleErrors;
            $errors = \array_merge($errors,$consoleErrors);
        } 

        $this->data = $errors;
        $this->loaded = true;
    }

    /**
     * Return true if error code exists
     *
     * @param string $code
     * @return boolean
     */
    public function hasErrorCode(string $code): bool
    {
        if ($this->loaded == false) {
            $this->loadErrors();
        }

        return $this->has($code);
    }

    /**
     * Get error code
     *
     * @param string $errorCode
     * @param string|null $default
     * @param array $params
     * @return string|null
     */
    public function getError(string $errorCode, array $params = [], ?string $default = null): ?string 
    {
        if ($this->loaded == false) {
            $this->loadErrors();
        }
       
        $error = $this->get($errorCode,null);
       
        return (empty($error) == true) ? $default : Text::render($error['message'],$params);      
    }

    /**
     * Get upload error message
     *
     * @param integer $errorCode
     * @return string
     */
    public function getUplaodFileError($errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return '';// no error                
            case UPLOAD_ERR_INI_SIZE:
                return $this->getError('UPLOAD_ERR_INI_SIZE');
            case UPLOAD_ERR_FORM_SIZE:
                return $this->getError('UPLOAD_ERR_FORM_SIZE');
            case UPLOAD_ERR_PARTIAL:
                return $this->getError('UPLOAD_ERR_PARTIAL');
            case UPLOAD_ERR_NO_FILE:
                return $this->getError('UPLOAD_ERR_NO_FILE');
            case UPLOAD_ERR_NO_TMP_DIR:
                return $this->getError('UPLOAD_ERR_NO_TMP_DIR');
            case UPLOAD_ERR_CANT_WRITE:
                return $this->getError('UPLOAD_ERR_CANT_WRITE');
            case UPLOAD_ERR_EXTENSION:
                return $this->getError('UPLOAD_ERR_EXTENSION');
        }

        return '';
    }
}
