<?php

class ConnectionGroup
{
    protected $id;
    protected $name;

    public function __construct($id = null)
    {
        if (isset($id)) {
            $this->id = $id;
        }
    }


    public function getConnections()
    {
        $connections = (new Database)->select("SELECT connection_id AS id
                                               FROM guacamole_connection
                                               WHERE parent_id=?",[$this->id]);
        if (!$connections) {
            return false;
        }

        $return = [];

        foreach ($connections as $connection) {
            $return[] = new Connection($connection['id']);
        }

        return $return;
    }


    /**
     * Get all connection groups
     */
    public static function getAll()
    {
        return (new Database)->select("SELECT connection_group_id AS id, connection_group_name AS name
                                       FROM guacamole_connection_group
                                       WHERE connection_group_name <> 'Templates' ORDER BY name");
    }
}
