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

use Arikaim\Core\System\Process;
use Arikaim\Core\Utils\Curl;
use Arikaim\Core\Utils\Path;

/**
 * Composer commands
 */
class Composer
{   
    /**
     * Run require command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function requireCommand($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("require $packageName",$async,$realTimeOutput); 
    }
    
    /**
     * Check if package is installed
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return boolean
     */
    public static function hasPackage($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("show $packageName",$async,$realTimeOutput); 
    }

    /**
     * Run show command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function show($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("show $packageName",$async,$realTimeOutput); 
    }

    /**
     * Run remove comand
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function remove($packageName,$async = false, $realTimeOutput = false)
    {
        return Self::runCommand("remove $packageName --no-dev",$async,$realTimeOutput); 
    }

    /**
     * Run update package command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function updatePackage($packageName, $async = false, $realTimeOutput = false)
    {
        return Self::runCommand("update $packageName --no-dev",$async,$realTimeOutput);
    }

    /**
     * Run update command
     *
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function update($async = false, $realTimeOutput = false)
    {
        return Self::runCommand('update --no-dev',$async,$realTimeOutput);
    }

    /**
     * Run composer command
     *
     * @param string $command
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function runCommand($command, $async = false, $realTimeOutput = false)
    {
        $command = "php " . Path::BIN_PATH . 'composer.phar ' . $command;
        $env = [
            'COMPOSER_HOME'      => Path::BIN_PATH,
            'COMPOSER_CACHE_DIR' => '/dev/null'
        ];

        $process = Process::create($command,$env);
        try {
            if ($async == true) {
                $process->start();
            } else {
                if ($realTimeOutput == true) {
                    $process->run(function ($type, $buffer) {                       
                        echo $buffer;                        
                    });
                }
                $process->run();
            }
            $output = $process->getOutput();
        } catch(\Exception $e) {            
            return $e->getMessage();
        }

        return $output;
    }

    /**
     * Get package info
     *
     * @param string $vendor Package vendor name
     * @param string $package Package name
     * @return array
     */
    public static function getPackageInfo($vendor, $package)
    {       
        $info = Curl::get("https://packagist.org/packages/$vendor/$package.json");

        return (empty($info) == true) ? null : json_decode($info,true);
    }
}
