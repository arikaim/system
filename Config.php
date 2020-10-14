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
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\Interfaces\CacheInterface;

/**
 * Config file loader and writer
 */
class Config extends Collection
{
    /**
     * Cache save time
     *
     * @var integer
     */
    public static $cacheSaveTime = 4;
    
    /**
     * Config file name
     *
     * @var string
     */
    private $fileName;
    
    /**
     * Config array comments
     *
     * @var array
     */
    private $comments = [];

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
     * Constructor
     *
     * @param string $fileName
     * @param array|null $cache
     */
    public function __construct($fileName = null, CacheInterface $cache = null, $dir) 
    {       
        $this->cache = $cache;
        $this->fileName = (empty($fileName) == true) ? 'config.php' : $fileName;
        $this->configDir = $dir;
        $data = $this->load($this->fileName);   
        
        Self::$cacheSaveTime = \defined('CACHE_SAVE_TIME') ? \constant('CACHE_SAVE_TIME') : Self::$cacheSaveTime;

        parent::__construct($data);   

        $this->setComment('database settings','db');
        $this->setComment('application settings','settings');
    }
    
    /**
     * Get config file name
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configDir . $this->fileName;
    }

    /**
     * Reload config file
     *
     * @return void
     */
    public function reloadConfig()
    {
        if (\is_null($this->cache) == false) {        
            $this->cache->delete(\strtolower($this->fileName));
        }
        
        $this->data = $this->load($this->fileName);         
    }

    /**
     * Set config dir
     *
     * @param string $dir
     * @return void
     */
    public function setConfigDir($dir) 
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
    public static function read($fileName, $configDir) 
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
    public function load($fileName, $useCache = true) 
    {       
        if (\is_null($this->cache) == false && $useCache == true) {
            $result = $this->cache->fetch(\strtolower($fileName));
            if (\is_array($result) == true) {
                return $result;
            }
        }
      
        $fullFileName = $this->configDir . $fileName;
       
        $result = (File::exists($fullFileName) == true) ? include($fullFileName) : [];    
        if (\is_null($this->cache) == false && (empty($result) == false)) {
            $this->cache->save(\strtolower($fileName),$result,Self::$cacheSaveTime);
        } 

        return $result;            
    }   

    /**
     * Set array key comment
     *
     * @param string $comment
     * @param string $key
     * @return void
     */
    protected function setComment($comment, $key)
    {
        $this->comments[$key] = $comment;
    }

    /**
     * Get array imtem comment as text
     *
     * @param string $key
     * @return string
     */
    protected function getCommentsText($key)
    {
        return (isset($this->comments[$key]) == true) ? "\t// " . $this->comments[$key] . "\n" : '';
    }

    /**
     * Return config file content
     *
     * @return string
     */
    private function getFileContent($data) 
    {   
        $code = $this->getFileContentHeader();
        $code .= $this->exportConfig($data);

        return $code;
    }

    /**
     * Export array as text
     *
     * @param array $data
     * @param string $arrayKey
     * @return string
     */
    protected function exportArray(array $data, $arrayKey)
    {     
        $items = '';  
        $maxTabs = $this->determineMaxTabs($data);
    
        foreach ($data as $key => $value) {
            $items .= (empty($items) == false) ? ",\n" : '';
            $value = Utils::getValueAsText($value);
            $tabs = $maxTabs - $this->determineTabs($key);
            $items .="\t\t'$key'" . $this->getTabs($tabs) . "=> $value";
        }
        $comment = $this->getCommentsText($arrayKey);

        return "$comment\t'" . $arrayKey . "' => [\n" . $items . "\n\t]";
    }

    /**
     * Export item as text
     *
     * @param string $key
     * @param mixed $value
     * @param integer $maxTabs
     * @return string
     */
    protected function exportItem($key, $value, $maxTabs)
    {
        $tabs = $maxTabs - $this->determineTabs($key);
        $value = Utils::getValueAsText($value);

        return "\t'$key'" . $this->getTabs($tabs) . "=> $value";
    }

    /**
     * Export config as text
     *
     * @return string
     */
    protected function exportConfig($data)
    {
        $items = '';
        $maxTabs = $this->determineMaxTabs($data);

        foreach ($data as $key => $item) {
            if (\is_array($item) == true) {
                $items .= (empty($items) == false) ? ",\n" : '';
                $items .= $this->exportArray($item,$key);
            } else {
                $items .= (empty($items) == false) ? ",\n" : '';
                $items .= $this->exportItem($key,$item,$maxTabs);
            }
        }
        return "return [\n $items \n];\n";      
    }

    /**
     * Get config file header
     *
     * @return string
     */
    private function getFileContentHeader() 
    {
        $code = "<?php \n/**\n";
        $code .= "* Arikaim\n";
        $code .= "* @link        http://www.arikaim.com\n";
        $code .= "* @copyright   Copyright (c) 2017-" . date('Y') . " Konstantin Atanasov <info@arikaim.com>\n";
        $code .= "* @license     http://www.arikaim.com/license\n";
        $code .= "*/\n\n";

        return $code;
    }

    /**
     * Save config file
     *
     * @param string|null $fileName
     * @return bool
     */
    public function save($fileName = null, $data = null)
    {
        $fileName = (empty($fileName) == true) ? $this->fileName : $fileName;
        $data = (empty($data) == true) ? $this->data : $data;

        if (\is_null($this->cache) == false) {        
            $this->cache->delete(strtolower($fileName));
        }
       
        $fileName = $this->configDir . $fileName;

        if (File::isWritable($fileName) == false) {
            File::setWritable($fileName);
        }
        $content = $this->getFileContent($data);  
     
        return File::write($fileName,$content);       
    }

    /**
     * Load json config file
     *
     * @param string $fileName
     * @return array
     */
    public function loadJsonConfigFile($fileName)
    {
        $data = File::readJsonFile($this->configDir . $fileName);
        
        return (\is_array($data) == true) ? $data : [];
    }

    /**
     * Get max tabs count
     *
     * @param array $data
     * @param integer $tabSize
     * @return integer
     */
    private function determineMaxTabs(array $data, $tabSize = 4)
    {
        $keys = [];
        foreach ($data as $key => $value) {
            \array_push($keys,\strlen($key));
        }
        return \ceil(\max($keys) / $tabSize);
    }

    /**
     * Get tabs count for array key
     *
     * @param string $key
     * @param integer $tabSize
     * @return integer
     */
    private function determineTabs($key, $tabSize = 4)
    {
        return \round(\strlen($key) / $tabSize);
    }

    /**
     * Get tabs text
     *
     * @param integer $count
     * @return string
     */
    private function getTabs($count)
    {
        $result = '';
        for ($index = 0; $index <= $count; $index++) {
            $result .= "\t";
        }
        
        return $result;
    }
}
