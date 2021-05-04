<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\Utils\File;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\Interfaces\CacheInterface;

use Arikaim\Core\System\Traits\PhpConfigFile;

/**
 * Config file loader and writer
 */
class Config extends Collection
{
    use PhpConfigFile;
 
    /**
     * Config file name
     *
     * @var string
     */
    private $fileName;
    
    /**
     * Cache
     *
     * @var CacheInterface|null
    */
    private $cache;

    /**
     * Config files directory
     *
     * @var string
     */
    private $configDir;

    /**
     * List read protected var keys
     *
     * @var array
     */
    private $readProtectedKeys = [];

    /**
     * List write protected var keys
     *
     * @var array
     */
    private $writeProtectedKeys = [];

    /**
     * Constructor
     *
     * @param string|null $fileName
     * @param CacheInterface|null $cache
     * @param string $dir
     */
    public function __construct(?string $fileName = 'config.php', ?CacheInterface $cache = null, string $dir) 
    {       
        $this->cache = $cache;
        $this->fileName = $fileName;
        $this->configDir = $dir;
       
        $data = $this->load($this->fileName);   
    
        parent::__construct($data);   

        $this->setComment('database settings','db');
        $this->setComment('application settings','settings');
    }
    
    /**
     * Set read protecetd vars keys
     *
     * @param array $keys
     * @return void
     */
    public function setReadProtectedVars(array $keys): void
    {
        $this->readProtectedKeys = $keys;
    }

    /**
     * Set write protecetd vars keys
     *
     * @param array $keys
     * @return void
     */
    public function setWriteProtectedVars(array $keys): void
    {
        $this->writeProtectedKeys = $keys;
    }

    /**
     * Return true if var is not read protected
     *
     * @param string $key
     * @return boolean
     */
    public function hasReadAccess(string $key): bool
    {
        return (\in_array($key,$this->readProtectedKeys) == false);
    }

    /**
     * Return true if var is not write protected
     *
     * @param string $key
     * @return boolean
     */
    public function hasWriteAccess(string $key): bool
    {
        return (\in_array($key,$this->writeProtectedKeys) == false);
    }

    /**
     * Get config file name
     *
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->configDir . $this->fileName;
    }

    /**
     * Reload config file
     *
     * @param bool $useCache
     * @return void
     */
    public function reloadConfig(bool $useCache = false): void
    {
        if (\is_null($this->cache) == false) {        
            $this->cache->delete(\strtolower($this->fileName));
        }
        
        $this->data = $this->load($this->fileName,$useCache);         
    }

    /**
     * Set config dir
     *
     * @param string $dir
     * @return void
     */
    public function setConfigDir(string $dir): void 
    {
        $this->configDir = $dir;
    }

    /**
     * Read config file
     *
     * @param string $fileName
     * @param string $configDir
     * @return Collection
     */
    public static function read(string $fileName, string $configDir) 
    {
        return new Self($fileName,null,$configDir);      
    }

    /**
     * Load config file
     *
     * @param boolean $useCache
     * @param string $fileName
     * @return array
     */
    public function load(string $fileName, bool $useCache = true): array 
    {       
        if ((\is_null($this->cache) == false) && ($useCache == true)) {          
            $result = $this->cache->fetch(\strtolower($fileName));
            if (\is_array($result) == true) {
                return $result;
            }
        }
      
        $fullFileName = $this->configDir . $fileName;
       
        $result = (\file_exists($fullFileName) == true) ? include($fullFileName) : [];    
        if ((\is_null($this->cache) == false) && (empty($result) == false)) {
            $this->cache->save(\strtolower($fileName),$result);
        } 

        return $result;            
    }   

    /**
     * Save config file
     *
     * @param string|null $fileName
     * @param array|null $data
     * @return bool
     */
    public function save(?string $fileName = null, ?array $data = null): bool
    {
        $fileName = (empty($fileName) == true) ? $this->fileName : $fileName;
        $data = (empty($data) == true) ? $this->data : $data;

        if (\is_null($this->cache) == false) {        
            $this->cache->delete(\strtolower($fileName));
        }
       
        $fileName = $this->configDir . $fileName;

        return $this->saveConfigFile($fileName,$data);           
    }

    /**
     * Load json config file
     *
     * @param string $fileName
     * @return array
     */
    public function loadJsonConfigFile(string $fileName): array
    {
        $data = File::readJsonFile($this->configDir . $fileName);
        
        return (\is_array($data) == true) ? $data : [];
    }

    /**
     * Check if file exist
     *
     * @param string $fileName
     * @return boolean
     */
    public function hasConfigFile(string $fileName): bool
    {
        return (bool)\file_exists($this->configDir . $fileName);
    }
}
