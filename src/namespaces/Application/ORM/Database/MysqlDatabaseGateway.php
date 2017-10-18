<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Database;

use Application\ORM\Exception\{ORMGenericException,ORMConnectException,ORMQueryException};
use \PDO;

/**
 * A MySQL DB adapter; part of the larger ORM abstraction layer. Should be
 * subclassed.
 * 
 * @property bool $isConnected Read-only property that returns TRUE if the
 * connection is alive (performs a query).
 * 
 * @property bool $connected Read-only alias of property
 * MysqlDatabaseGateway::isConnected.
 */
class MysqlDatabaseGateway implements DatabaseGatewayInterface
{
    private $pdo = NULL;

    public function __get($key)
    {
        if($key == 'isConnected' || $key == 'connected')
        {
            if($this->pdo === NULL)
                return FALSE;

            try
            {
                return (bool) $this->query('SELECT 1');
            }
            
            catch(\Exception $ex)
            {
                return FALSE;
            }
        }
    }

    /**
     * @param \PDO $pdo Internal PDO instance
     */
    public function __construct($pdo = NULL)
    {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new PDO internal instance if one does not already exist. It
     * is not necessary to call this function manually in most cases. Does
     * nothing if called when already connected ($this->connected == TRUE).
     *
     * @param  \PDO $pdo configured PDO instance
     */
    public function connect($pdo = NULL)
    {
        if($this->pdo !== NULL)
            return;

        $opt = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_LAZY,
            PDO::ATTR_EMULATE_PREPARES   => FALSE,

            /* Only set to false if memory issues; know the implications (breaks the usefulness of FETCH_LAZY, for instance)! */
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE,
        );

        try
        {
            $this->pdo = $pdo ?? new PDO(DB_DSN, DB_USER, DB_PASSWD, $opt);
        }
        
        catch(\PDOException $ex)
        {
            throw new ORMConnectException($ex);
        }
    }

    /**
     * Attempt to disconnect PDO from the DB backend. Idempotent. Not guaranteed
     * in any way to work.
     * 
     * @see https://stackoverflow.com/questions/18277233/pdo-closing-connection
     */
    public function disconnect()
    {
        $this->pdo = NULL;
    }

    /**
     * Returns the internal PDO object instance. Note that the returned
     * reference should not be stored in a variable that is not unset at some
     * point or the `disconnect()` method will fail silently. Blame PDO.
     *
     * @return \PDO (MySQL) The internal PDO instance
     */
    public function getPDOObject()
    {
        if($this->pdo == NULL)
            $this->connect();

        return $this->pdo;
    }

    /**
     * Returns the result of interpreting $sql as a prepared statement and
     * executing it using the provided $params. If $params is empty, then $sql
     * is executed as a typical non-prepared SQL query.
     *
     * Note: if using the default PDO::FETCH_LAZY fetch style, what you fetch
     * from this result set will not be serializable nor can you call
     * \PDOStatement::fetchAll()!
     * 
     * @return \PDOStatement The result of executing the SQL query
     */
    public function query($sql, array $params = [])
    {
        try
        {
            $pdo = $this->getPDOObject();

            if(!$params)
                return $pdo->query($sql);

            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            return $statement;
        }
        
        catch(\PDOException $ex)
        {
            throw new ORMQueryException($statement->queryString ?? "(raw) $sql", $ex);
        }
    }

    /**
     * Returns the insertion id from the most recent applicable database
     * interaction. This is a thin wrapper around \PDO::lastInsertId().
     * 
     * @return mixed
     */
    public function getLastInsertId()
    {
        try
        {
            return $this->getPDOObject()->lastInsertId();
        }
        
        catch(\PDOException $ex)
        {
            throw new ORMGenericException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
