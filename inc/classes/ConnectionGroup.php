<?php

class ConnectionGroup
{
    public $id;
    public $name;
    public $parent_id;

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


    public static function getById($id)
    {
        $data = (new Database)->selectFirst("SELECT connection_group_name AS name, parent_id
                                        FROM guacamole_connection_group
                                        WHERE connection_group_id=?", [$id]);
        if (!$data) {
            return false;
        }

        $group = new self($id);
        $group->name = $data['name'];
        $group->parent_id = $data['parent_id'];

        return $group;
    }


    /**
     * Get all connection groups
     */
    public static function getAll()
    {
        $groups = (new Database)->select("SELECT connection_group_id AS id, connection_group_name AS name, parent_id
                                       FROM guacamole_connection_group
                                       WHERE connection_group_name <> 'Templates' ORDER BY name");

        foreach ($groups as &$group) {
            $parent_id = $group['parent_id'];
            while ($parent_id) {
                $parent = self::getById($parent_id);
                if (!$parent) {
                    break;
                }
                $group['name'] = $parent->name . ' > ' . $group['name'];
                $parent_id = $parent->parent_id;
            }
        }

        // you can use array_column() instead of the above code
        $ids  = array_column($groups, 'id');
        $names = array_column($groups, 'name');

        // Sort the data with volume descending, edition ascending
        // Add $data as the last parameter, to sort by the common key
        array_multisort($names, $ids, $groups);

        return $groups;
    }
}
