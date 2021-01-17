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

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Arikaim\Core\System\Process;
use Arikaim\Core\Utils\Curl;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\Utils\File;
use Exception;

/**
 * Composer commands
 */
class Composer
{   
    /**
     * Run composer commmand
     *
     * @param string $command
     * @param string|array $packages
     * @param bool $quiet
     * @return void
     */
    public static function run(string $command, ?array $packages = null, bool $quiet = false): void
    {       
        \putenv('COMPOSER_HOME=' . APP_PATH . '/vendor/bin/composer');
        \putenv('COMPOSER_CACHE_DIR=/dev/null');
      
        $cmd['command'] = $command;
        if (empty($packages) == false) {
            $cmd['packages'] = (\is_string($packages) == true) ? [$packages] : $packages;
        }
        if ($quiet == true) {
            $option = new InputOption('--quiet',null,InputOption::VALUE_NONE,'Quiet');
            $cmd['--quiet'] = $option;
        }
      
        $input = new ArrayInput($cmd);       
        $application = new Application();    
        $application->setAutoExit(false); 

        $application->run($input);
    }

    /**
     * Run require composer command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function requirePackage(string $packageName, bool $async = false, bool $realTimeOutput = false)
    {
        return Self::runCommand('require ' . $packageName,$async,$realTimeOutput); 
    }
    
    /**
     * Check if package is installed
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return boolean
     */
    public static function hasPackage(string $packageName, bool $async = false, bool $realTimeOutput = false)
    {
        return Self::runCommand('show ' . $packageName,$async,$realTimeOutput); 
    }

    /**
     * Run show command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function show(string $packageName, bool $async = false, bool $realTimeOutput = false)
    {
        return Self::runCommand('show '. $packageName,$async,$realTimeOutput); 
    }

    /**
     * Run remove comand
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function remove(string $packageName, bool $async = false, bool $realTimeOutput = false)
    {
        return Self::runCommand('remove ' . $packageName . ' --no-dev',$async,$realTimeOutput); 
    }

    /**
     * Run update package command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function updatePackage(string $packageName, bool $async = false, bool $realTimeOutput = false)
    {
        return Self::runCommand('update ' . $packageName . ' --no-dev',$async,$realTimeOutput);
    }

    /**
     * Run install package command
     *
     * @param string $packageName
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
    */
    public static function installPackage(string $packageName, bool $async = false, bool $realTimeOutput = false)
    {
        return Self::runCommand('install ' . $packageName . ' --no-dev',$async,$realTimeOutput);
    }

    /**
     * Run update command
     *
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function update(bool $async = false, bool $realTimeOutput = false)
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
    public static function runCommand(string $command, bool $async = false, bool $realTimeOutput = false)
    {
        $command = 'php ' . Path::BIN_PATH . 'composer.phar ' . $command;
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
        } catch(Exception $e) {            
            return $e->getMessage();
        }

        return $output;
    }

    /**
     * Get package data
     *
     * @param string $vendor
     * @param string $package
     * @return array|null
     */
    public static function getPackageData(string $vendor, string $package)
    {
        $info = Curl::get('https://packagist.org/packages/' . $vendor .'/' . $package . '.json');

        return (empty($info) == true) ? null : \json_decode($info,true);
    }

    /**
     * Get package info
     *
     * @param string $vendor Package vendor name
     * @param string $package Package name
     * @return array|null
     */
    public static function getPackageInfo(string $vendor, string $package)
    {            
        $info = Curl::get('https://packagist.org/packages/' . $vendor . '/' . $package . '.json');
        $data = \json_decode($info,true);

        return (\is_array($data) == true) ? $data : null;       
    }

    /**
     * Get package last version
     *
     * @param string $vendor
     * @param string $package
     * @return string|false
     */
    public static function getLastVersion(string $vendor, string $package)
    {
        $info = Self::getPackageInfo($vendor,$package);
        $versions = $info['package']['versions'] ?? false;
        if ($versions === false) {
            return false;
        }
        $keys = \array_keys($versions);
       
        return ($keys[0] == 'dev-master') ? $keys[1] : $keys[0];
    }

    /**
     * Get installed package version
     *
     * @param string $path
     * @param string $packageName
     * @return string|false
     */
    public static function getInstalledPackageVersion(string $path, string $packageName)
    {
        $packages = Self::readInstalledPackages($path);
        if ($packages === false) {
            return false;
        }
        foreach ($packages as $package) {
            if ($package['name'] == $packageName) {
                return $package['version'];
            };   
        }

        return false;
    }

    /**
     * Get local package info
     *
     * @param string $path
     * @param array $packagesList
     * @return array
     */
    public static function getLocalPackagesInfo(string $path, array $packagesList)
    {
        $packages = Self::readInstalledPackages($path);     
        foreach ($packagesList as $item) {
            $result[$item]['version'] = null;                  
        }
        
        if ($packages === false) {
            return $result;
        }

        foreach ($packages as $package) {
            $key = \array_search($package['name'],$packagesList);

            if ($key !== false) {
                $result[$package['name']]['version'] = $package['version'];
            };   
        }

        return $result;
    }

    /**
     * Return true if composer package is installed
     *
     * @param string $path
     * @param string|array $packageList
     * @return boolean
     */
    public static function isInstalled(string $path, $packageList)
    {
        $packages = Self::readInstalledPackages($path);
        if ($packages === false) {
            return false;
        }
        $packageList = (\is_string($packageList) == true) ? [$packageList] : $packageList;
        
        foreach ($packageList as $package) {          
            if (Self::getInstalledPackageVersion($path,$package) === false) {
                return false;
            }
        }
       
        return true;
    }

    /**
     * Read local packages info file 
     *
     * @param string $path
     * @return array|false
     */
    public static function readInstalledPackages(string $path)
    {
        $filePath = $path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'installed.json';

        return File::readJsonFile($filePath);       
    }
}
