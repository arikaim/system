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

use Symfony\Component\Process\Process as SProcess;
use Symfony\Component\Process\PhpExecutableFinder;

use Arikaim\Core\Utils\Path;
use Exception;

/**
 * System Process
 */
class Process 
{
    /**
     * Create process
     *
     * @param array|string $command
     * @param array $env
     * @param string $input
     * @param integer $timeout
     * @param array|null $options
     * @return object
     */
    public static function create($command, array $env = null, $input = null, $timeout = 60, $options = null)
    {
        $process = new SProcess($command,null,$env,$input,$timeout,$options);
        $process->enableOutput();

        return $process;
    }

    /**
     * Run console command
     *
     * @param string|array $command
     * @param array $env
     * @param boolean $inheritEnv
     * @return mixed
     */
    public static function run($command, array $env = [], $inheritEnv = true)
    {
        $process = Self::create($command,$env);
        $process->inheritEnvironmentVariables($inheritEnv);
    
        $process->run();

        return ($process->isSuccessful() == true) ? $process->getOutput() : $process->getErrorOutput();          
    }

    /**
     * Run console command
     *
     * @param array $command
     * @param callable|null $callback
     * @param array|null $env
     * @return mixed
     */
    public static function start($command, callable $callback = null, ?array $env = null)
    {
        $process = Self::create($command,$env);
        $process->start($callback);
    
        return $process->getPid();
    }

    /**
     * Set process title
     *
     * @param string $title
     * @return void
     */
    public static function setTitle(string $title): void
    {
        \cli_set_process_title($title);
    }

    /**
     * Run console command in backgorund
     *
     * @param array $command
     * @param callable|null $callback
     * @param array $env
     * @return mixed
    */
    public static function startBackground($command, $callback = null, array $env = [])
    {
        $process = Self::create($command,$env);
        $process->disableOutput();
        $process->start($callback);
        
        return $process->getPid();
    }

    /**
     * Get current script user
     *
     * @return string
     */
    public static function getCurrentUser()
    {
        return \posix_getpwuid(\posix_geteuid());
    }

    /**
     * Get php executable
     *
     * @return string
     */
    public static function findPhp()
    {
        return (new PhpExecutableFinder)->find();
    }

    /**
     * Stop (kill) process
     *
     * @param string|int $pid
     * @return boolean
     */
    public static function stop($pid): bool 
    {
        if (\function_exists('posix_kill') == true) {
            return @\posix_kill($pid,9);
        }
        try {
            $result = \shell_exec(\sprintf('kill %d 2>&1',$pid));
            if (!preg_match('/No such process/',$result)) {
                return true;
            }
        } catch (Exception $e) {
        }

        return false;
    }    

    /**
     * Reurn true if process is running (Linux only)
     *
     * @param integer $pid
     * @return boolean
     */
    public static function isRunning($pid): bool 
    {
        if (\function_exists('posix_kill') == true) {
            $result = \posix_kill((int)$pid,0);
            if ($result !== false) {
                return true;
            }            
        }
        if (\function_exists('posix_getpgid') == true) {
            $result = \posix_getpgid($pid);
            if ($result !== false) {
                return true;
            }   
        }

        return (\file_exists("/proc/$pid") == true);    
    }

    /**
     * Return false if process not exist
     *
     * @param int $pid
     * @return bool
     */
    public static function verifyProcess($pid)
    {
        return \posix_kill($pid,0);
    }

    /**
     * Get process command
     *
     * @param integer $pid
     * @return string
     */
    public static function getCommand($pid) 
    {
        $pid = (int)$pid;

        return \trim(\shell_exec('ps o comm= ' . $pid));
    }

    /**
     * Get curret process pid
     *
     * @return mixed
     */
    public static function getCurrentPid()
    {
        return \posix_getpid();
    }

       /**
     * Run composer command
     *
     * @param string $command
     * @param boolean $async
     * @param boolean $realTimeOutput
     * @return mixed
     */
    public static function runComposerCommand(string $command, bool $async = false, bool $realTimeOutput = false)
    {
        $command = 'php ' . Path::BIN_PATH . 'composer.phar ' . $command;
        $env = [
            'COMPOSER_HOME'      => Path::BIN_PATH,
            'COMPOSER_CACHE_DIR' => '/dev/null'
        ];

        $process = Self::create($command,$env);
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
}
