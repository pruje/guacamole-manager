<?php
/*
 *  Database support on standard SQL databases
 *  MySQL/MariaDB, PostgreSQL, SQLite should be classes that extends this one.
 *
 *  DISCLAIMER:
 *  I know, NoSQL like mongoDB should be used for this project, but for now,
 *  I try to do my best with the things I know best.
 *  Please help me to make OnMyShelf better!
 */

class GlobalDatabase
{
    /**
     * Runs a SELECT query and returns the first row of results
     * @param  string $query SQL SELECT query
     * @param  array  $args  Array of args
     * @return array|bool    Array of results, FALSE if error
     */
    public function selectFirst($query, array $args=[])
    {
        // runs select method
        $result = $this->select($query, $args);

        // returns first row of results
        if ($result) {
            return $result[0];
        } else {
            return false;
        }
    }


    /**
     * Runs a SELECT query and returns the first column in the first row of results
     * @param  string $query SQL SELECT query
     * @param  array  $args  Array of args
     * @return array|bool    Array of results, FALSE if error
     */
    public function selectOne($query, array $args=[])
    {
        // runs select method
        $result = $this->selectFirst($query, $args);

        // returns first column of results
        if ($result) {
            return array_values($result)[0];
        } else {
            return false;
        }
    }


    /**
     * Runs a SELECT query and returns the results of the desired column
     * @param  string $query SQL SELECT query
     * @param  array  $args  Array of args
     * @return array|bool    Array of results, FALSE if error
     */
    public function selectColumn($query, array $args=[], $column=0)
    {
        // runs select method
        $result = $this->select($query, $args);

        // returns first column of results
        if ($result === false) {
            return false;
        }

        $values = [];

        foreach ($result as $value) {
            $values[] = array_values($value)[$column];
        }

        return $values;
    }


    /**
     * Runs SELECT COUNT query
     * @param  string $table   Table name
     * @param  array  $filters Array of filters
     * @return array|bool  Array of results, FALSE if error
     */
    public function count($table, array $filters=[])
    {
        $query = 'SELECT COUNT(*) FROM `'.$table.'`';

        if (count($filters) > 0) {
            $query .= ' WHERE ';

            foreach ($filters as $key => $value) {
                $query .= $key.'=? AND ';
            }

            // delete last " AND "
            $query = substr($query, 0, -5);
        }

        // runs query
        return $this->selectOne($query, $filters);
    }


    /**
     * Check if a value exists
     * @param  string $table
     * @param  array  $filters
     * @return bool   Exists
     */
    public function exists(string $table, array $filters=[])
    {
        $count = $this->count($table, $filters);
        if (!is_integer($count)) {
            return false;
        }

        return $count > 0;
    }


    /**
     * Insert one entry
     * @param  string $table Table name
     * @param  array  $args  Array key => value
     * @param  array  $orUpdate
     * @return bool
     */
    public function insertOne(string $table, array $values, array $orUpdate=[])
    {
        return $this->insert($table, [$values], $orUpdate);
    }


    /**
     * Runs UPDATE query
     * @param  string $table   Table name
     * @param  array  $values  Array of values
     * @param  array  $filters Array of filters
     *  @return bool            Success
     */
    public function update(string $table, array $values=[], array $filters=[])
    {
        if (count($values) == 0) {
            return false;
        }

        $query = 'UPDATE `'.$table.'` SET ';

        foreach ($values as $key => $v) {
            $query .= '`'.$key.'`=?,';
        }
        // delete last ","
        $query = substr($query, 0, -1);

        if (count($filters) > 0) {
            $query .= ' WHERE ';

            foreach ($filters as $key => $v) {
                $query .= '`'.$key.'`=? AND ';
            }

            // delete last " AND "
            $query = substr($query, 0, -5);
        }

        $args = array_merge($values, $filters);

        return $this->write($query, $args);
    }


    /**
     * Runs DELETE query
     * @param  string $table   Table name
     * @param  array  $filters Array of filters
     * @return bool   Success
     */
    public function delete(string $table, array $filters=[])
    {
        $query = 'DELETE FROM '.$table;

        if (count($filters) > 0) {
            $query .= ' WHERE ';

            foreach ($filters as $key => $value) {
                $query .= '`'.$key.'`=? AND ';
            }

            // delete last " AND "
            $query = substr($query, 0, -5);
        }

        return $this->write($query, $filters);
    }
}
