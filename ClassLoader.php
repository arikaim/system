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

use Exception;

/**
 * Class loader
 */
class ClassLoader 
{
    /**
     * Arikaim core path
     *
     * @var string
     */
    private $coreNamespace;

    /**
     * Namepaces
     *
     * @var array
     */
    private $packagesNamespace;

    /**
     * Document root
     *
     * @var string
     */
    private $documentRoot;

    /**
     * Path
     *
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $basePath
     * @param string|null $documentRoot
     * @param string|null $coreNamespace
     * @param array $packagesNamespace
     */
    public function __construct(string $basePath, ?string $documentRoot = null, ?string $coreNamespace = null, array $packagesNamespace = []) 
    {        
        $this->coreNamespace = $coreNamespace;
        $this->packagesNamespace = $packagesNamespace;

        if ($documentRoot != null) {
            $this->documentRoot = $documentRoot;
        } else {
            $this->documentRoot = (\php_sapi_name() == 'cli') ? __DIR__ : $_SERVER['DOCUMENT_ROOT'];   
        }

        $this->path = $this->documentRoot . $basePath;
    }
    
    /**
     * Register loader
     * 
     * @return void
     */
    public function register(): void 
    {
        \spl_autoload_register([$this,'LoadClassFile'],false,false);
    }

    /**
     * Load class file
     *
     * @param string $class
     * @return mixed
     */
    public function LoadClassFile(string $class) 
    {
        $file = $this->getClassFileName($class);
      
        return (\file_exists($file) == true) ? require_once $file : false;        
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getDocumentRoot(): string
    {
        return $this->documentRoot;         
    }

    /**
     * Get class file name
     *
     * @param string $class
     * @return string
     */
    public function getClassFileName(string $class): string 
    {     
        $namespace = \substr($class,0,\strrpos($class,'\\'));     
        $tokens = \explode('\\',$class);
        $class = \end($tokens);
        $namespace = $this->namespaceToPath($namespace); 
     
        return $this->path . DIRECTORY_SEPARATOR .  $namespace . DIRECTORY_SEPARATOR . $class . '.php';       
    }

    /**
     * Get namspace
     *
     * @param string $class
     * @return string
     */
    public function getNamespace(string $class): string 
    {           
        return \substr($class,0,\strrpos($class,'\\'));       
    } 
    
    /**
     * Convert namespace to path
     *
     * @param string $namespace
     * @param boolean $full
     * @return string
     */
    public function namespaceToPath(string $namespace, bool $full = false): string 
    {  
        $namespace = \ltrim($namespace,'\\');
        $namespace = str_replace($this->coreNamespace,\strtolower($this->coreNamespace),$namespace);
    
        if (\strpos($namespace,$this->packagesNamespace[0]) !== false ||
            \strpos($namespace,$this->packagesNamespace[1]) !== false) {
            
            $namespace = \strtolower($namespace);                             
        }

        $namespace = \str_replace('\\',DIRECTORY_SEPARATOR,$namespace);
         
        return ($full == true) ? $this->path . DIRECTORY_SEPARATOR . $namespace : $namespace;   
    } 

    /**
     *  Load class alias
     *
     * @param string $class
     * @param string $alias
     * @return bool
     */
    public function loadClassAlias(string $class, string $alias)
    {
        return (\class_exists($class) == true) ? \class_alias($class,$alias) : false;                
    }

    /**
     * Load class aliaeses
     *
     * @param array $items
     * @return bool
     */
    public function loadAlliases(array $items): bool
    {                
        foreach ($items as $class => $alias) {      
            if ($this->loadClassAlias($class,$alias) == false) { 
                throw new Exception('Error load class alias for class (' . $class .') alias (' . $alias . ')',1);  
                    
                return false;
            }
        }
        
        return true;
    }
}
