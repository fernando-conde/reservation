<?php
namespace DbSync;

class BackOffice extends DataBase
{
    /** @var \PDO */
    protected $connextion;

    public function __construct()
    {
        try{
            $this->connection = new \PDO('sqlsrv:Server=SQLSERVER-BO,1433', getenv('ENV_BO_LOGIN'), getenv('ENV_BO_PASSWORD'));
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );  
        } catch (\PDOException $e) {
            dump('No BO DB MSSQL Connection', $e);
        } catch (Exception $e) {
            dump('No BO DB MSSQL Connection', $e);
        }
    }

    /**
     * @param string $query
     * @return array
     */
    public function get($query, $filter = null)
    {
        $start = substr($query, 0, 3);
        $query = 'DB_EXPORT.dbo.'.$query;

        if ($start === 'TB_') {
            $query = 'SELECT * FROM '.$query;
        }

        return parent::get($query, $filter);
    }
}
