<?php
namespace DbSync;

class FrontOffice extends DataBase
{
    /** @var \PDO */
    protected $connection;

    /** @var string */
    private $table;

    public function __construct()
    {
        try{
            $listOption = array();
            $listOption[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4';
            $this->connection = new \PDO('mysql:host=MYSQL-FO-MASTER;port=3306;dbname=idalgo;charset=utf8', getenv('ENV_FO_LOGIN'), getenv('ENV_FO_PASSWORD'));
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            dump('No FO DB MYSQL Connection', $e);
        } catch (Exception $e) {
            dump('No FO DB MYSQL Connection', $e);
        }
    }

    /**
     * @param string $query
     * @return array
     */
    public function get($query, $filter = null)
    {
        $this->table = $query;
        $request = 'SELECT * FROM '.$query;

        return parent::get($request, $filter);
    }

    /**
     * @param array $columns
     * @return \PDOStatement
     */
    public function prepareInsert(array $columns)
    {
        $query = 'INSERT INTO '.$this->table;
        $query .= ' ('.implode(',', $columns).') VALUES ('.implode(',', array_map(function($col){return ':'.$col;}, $columns)).')';
        return $this->connection->prepare($query);
    }

    /**
     * @param array $columns
     * @param array $keys
     * @return \PDOStatement
     */
    public function prepareUpdate(array $columns, array $keys)
    {
        $columns = array_filter($columns, function ($col) use ($keys){return in_array($col, $keys) === false;});
        
        $query = 'UPDATE '.$this->table.' SET ';
        $query .= implode(',', array_map(function($col){return $col.'=:'.$col;}, $columns));
        $query .= ' WHERE '.implode(' AND ', array_map(function($col){return $col.'=:'.$col;}, $keys));
        return $this->connection->prepare($query);
    }

    /**
     * @param array $columns
     * @return \PDOStatement
     */
    public function prepareDelete(array $columns)
    {        
        $query = 'DELETE FROM '.$this->table.' WHERE '.implode(' AND ', array_map(function($col){return $col.'=:'.$col;}, $columns));
        return $this->connection->prepare($query);
    }

    /**
     * @param \PDOStatement $request
     * @return bool
     */
    public function execute(\PDOStatement $request)
    {
        $return = false;
        try{
            $return = $request->execute();
        } catch (\PDOException $e) {
            dump('Fo Execute Error', $e);
        } catch (Exception $e) {
            dump('Fo Execute Error', $e);
        }

        return $return;
    }
}
