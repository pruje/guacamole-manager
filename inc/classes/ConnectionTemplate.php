<?php

class ConnectionTemplate extends Connection
{
    public function getParameters()
    {
        $parameters = parent::getParameters();
        if (!$parameters) {
            return false;
        }

        unset($parameters['connection_name']);
        unset($parameters['parent_id']);

        return $parameters;
    }


    public static function getById($id)
    {
        $name = (new Database)->selectOne("SELECT connection_name
                                           FROM guacamole_connection
                                           WHERE connection_id=?", [$id]);
        if (!$name) {
            return false;
        }

        return new self($id);
    }


    /**
     * Get all templates
     * @return [type] [description]
     */
    public static function getAll()
    {
        $groupId = (new Database)->selectOne("SELECT connection_group_id FROM guacamole_connection_group
                                              WHERE connection_group_name='Templates'");
        if (!$groupId) {
            return false;
        }

        return (new Database)->select("SELECT connection_id AS id, connection_name AS name
                                       FROM guacamole_connection
                                       WHERE parent_id=? ORDER BY name", [$groupId]);
    }
}
