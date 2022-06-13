<?php

class UserGroup
{
    protected $id;
    public $name;
    public $entityId;
    public $disabled;

    public function __construct($id = null)
    {
        if (isset($id)) {
            $this->id = $id;
        }
    }


    public static function getById($id)
    {
        $ug = (new Database)->selectFirst("SELECT e.entity_id as eid, e.name, g.disabled
                                           FROM guacamole_user_group g JOIN guacamole_entity e
                                           ON g.entity_id = e.entity_id AND e.type='USER_GROUP'
                                           WHERE g.user_group_id=?", [$id]);

        if (!$ug) {
            return false;
        }

        $userGroup = new self($id);
        $userGroup->entityId = $ug['eid'];
        $userGroup->name = $ug['name'];
        $userGroup->disabled = $ug['disabled'];

        return $userGroup;
    }


    /**
     * Get all connection groups
     */
    public static function getAll()
    {
        return (new Database)->select("SELECT g.user_group_id as id, e.name
                                       FROM guacamole_user_group g
                                       JOIN guacamole_entity e
                                       ON g.entity_id = e.entity_id
                                       AND e.type='USER_GROUP' ORDER BY name");
    }
}
