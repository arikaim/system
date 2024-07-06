<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\System\Session;

use Arikaim\Core\Utils\DateTime;
use \SessionHandlerInterface;
use \Exception;

/**
 * Database session storage class
 */
class DbSessionStorage implements SessionHandlerInterface
{
    /**
     * Db connection
     *
     * @var Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * Session table name.
     *
     * @var string
     */
    protected $tableName;

    /**
     * Create a new database session handler instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $tableName  
     */
    public function __construct($connection,string $tableName = 'sessions')
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * Registers session handler.
     */
    public function register(): void
    {
        \session_set_save_handler($this,true);
    }

    /**
     * Open handler
     *
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * Close handler
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $sessionId
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function read($sessionId)
    {
        $session = (object) $this
            ->connection
            ->table($this->tableName)
            ->where('session_id','=',$sessionId)
            ->first();

        return (isset($session->data) == true) ? \base64_decode($session->data) : '';  
    }
   
    /**
     * Write session data
     *
     * @param string $sessionId
     * @param mixed $data
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function write($sessionId, $data)
    {
        $sessionData = [
            'session_id'  => $sessionId,
            'data'        => \base64_encode($data),
            'access_time' => DateTime::getCurrentTimestamp(),
        ];

        $session = $this
            ->connection
            ->table($this->tableName)
            ->where('session_id','=',$sessionId)
            ->first();
       

        if ($session != null) {
            $result = $this
                ->connection
                ->table($this->tableName)
                ->where('session_id','=',$sessionId)
                ->update($sessionData);

            return ($result !== false);
        } 

        try {
            $result = $this
                ->connection
                ->table($this->tableName)
                ->insert($sessionData);
            
            return ($result !== null);
        } catch (Exception $e) {
            $result = $this
                ->connection
                ->table($this->tableName)
                ->where('session_id','=',$sessionId)
                ->update($sessionData);
            
            return ($result !== false);
        }
         
        return true;
    }

    /**
     * Delete session
     *
     * @param string $sessionId
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function destroy($sessionId)
    {
        $this
            ->connection
            ->table($this->tableName)
            ->where('session_id','=',$sessionId)
            ->delete();

        return true;
    }

    /**
    * Gc handler
    *
    * @param int $lifetime
    * @return bool
    */
    #[\ReturnTypeWillChange]
    public function gc($lifetime)
    {
        $this
            ->connection
            ->table($this->tableName)
            ->where('access_time','<=',DateTime::getCurrentTimestamp() - $lifetime)
            ->delete();

        return true;
    }
}
