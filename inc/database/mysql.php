<?php
/*
*  MySQL/MariaDB database support
*/

class Database extends GlobalDatabase
{
    private $connection;

    public function __construct()
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($this->connection->connect_errno) {
            Logger::fatal("error while testing database connection: ".$this->connection->connect_errno);
            exit(1);
        }
    }

    public function __destruct()
    {
        // close database connection
        $this->connection->close();
    }


    /**
     * Runs a SELECT query
     * @param string $query SQL query
     * @param array $args
     * @return array|bool
     */
    public function select(string $query, array $args=[])
    {
        $stmt = $this->connection->prepare($query);
        if ($stmt === false) {
            Logger::error("Error while create statement for query: $query");
            return false;
        }

        $params = [];
        foreach ($args as &$value) {
            if (count($params) == 0) $params[] = '';

            if (is_float($value))       $params[0] .= 'd';
            elseif (is_bool($value)) {
                $params[0] .= 'i';
                if ($value) {
                    $value = 1;
                } else {
                    $value = 0;
                }
            }
            elseif (is_integer($value)) $params[0] .= 'i';
            elseif (is_string($value))  $params[0] .= 's';
            else                        $params[0] .= 'b';

            $params[] = &$value;
        }

        // Logger::debug("SQL query: $query");
        // Logger::var_dump($params);

        if (count($params) > 0) {
            call_user_func_array([$stmt, 'bind_param'], $params);
        }

        if ($stmt->execute() === false) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();

        $return = [];
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }


    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     * @param string $query SQL query
     * @param array $args   Array key => value
     * @return bool
     */
    public function write(string $query, array $args=[])
    {
        if (count($args) == 0) {
            return false;
        }

        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            Logger::error("Error while create statement for query: $query");
            return false;
        }

        $params = [''];
        foreach ($args as &$value) {
            if (is_float($value))       $params[0] .= 'd';
            elseif (is_bool($value)) {
                $params[0] .= 'i';
                if ($value) {
                    $value = 1;
                } else {
                    $value = 0;
                }
            }
            elseif (is_integer($value)) $params[0] .= 'i';
            elseif (is_string($value))  $params[0] .= 's';
            else                        $params[0] .= 'b';

            $params[] = &$value;
        }

        //Logger::debug("SQL query: $query");
        //Logger::var_dump($params);

        call_user_func_array([$stmt, 'bind_param'], $params);

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     * @param  string $table    Table name
     * @param  array  $values   Array or arrays key => value
     * @param  array  $orUpdate Array of fields to update on duplicate key
     * @return bool
     */
    public function insert(string $table, array $values, array $orUpdate=[])
    {
        if (count($values) == 0) {
            return false;
        }

        $query = 'INSERT INTO `'.$table.'` (';

        foreach ($values[0] as $key => $v) {
            $query .= '`'.$key.'`,';
        }
        // delete last comma
        $query = substr($query, 0, -1).') VALUES ';

        // avoid errors if data not correct (missing keys)
        try {
            $queryValues = '('.implode(',', array_values(array_fill(0,count($values[0]),'?'))).'),';
        } catch (Throwable $t) {
            Logger::fatal('Bad array in insert values');
            return false;
        }

        $query .= str_repeat($queryValues, count($values));
        // delete last comma
        $query = substr($query, 0, -1);

        // or update
        if (count($orUpdate) > 0) {
            $query .= ' ON DUPLICATE KEY UPDATE ';
            foreach ($orUpdate as $key) {
                // WARNING: this is working only from MariaDB 10.3.3!
                $query .= '`'.$key.'`=VALUE(`'.$key.'`),';
            }
            // delete last comma
            $query = substr($query, 0, -1);
        }

        $params = [];
        foreach ($values as $row) {
            foreach ($row as $value) {
                $params[] = $value;
            }
        }

        if (!$this->write($query, $params)) {
            return false;
        }

        // returns last inserted ID if any
        if ($this->connection->insert_id) {
            return $this->connection->insert_id;
        }

        return true;
    }
}
