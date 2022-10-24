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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use FilesystemIterator;

/**
 * Opcache preloader 
 */
class Preloader
{
    /**
     * Constructor
     *
     * @param array $files
     */
    public function __construct(array $files = [])
    {
        foreach ($files as $file) {
            $this->load($file);
        }
    }
    
    /**
     * Load file or path
     *
     * @param string $file
     * @return object
     */
    public function load(string $file): object
    {
        if (\is_dir($file)) {
            $this->loadPath($file);
        } else {
            require_once($path);
        }

        return $this;
    }

    /**
     * Load all php files in path (recursive)
     *
     * @param string $path
     * @return void
     */
    public function loadPath(string $path): void
    {
        $dir = new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir);
        $phpFiles = new RegexIterator($iterator,'/^.+\.php$/i',RecursiveRegexIterator::GET_MATCH);

        foreach ($phpFiles as $file) {              
            require_once($file[0]);
        }
    }

    /**
     * Get preloaded files
     *
     * @return array
     */
    public static function getCachedFiles(): array
    {
        return \opcache_get_status()['scripts'] ?? [];
    }
}
