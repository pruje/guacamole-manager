<?php

class Connection
{
    public $id;
    protected $name;
    protected $connection_parameters;

    public function __construct($id = null)
    {
        if (isset($id)) {
            $this->id = $id;
        }

        // parameters that are set in connection table, not in connection_parameter table
        $this->connection_parameters = [
            'connection_name',
            'parent_id',
            'protocol',
            'proxy_port',
            'proxy_hostname',
            'proxy_encryption_method',
            'max_connections',
            'max_connections_per_user',
            'connection_weight',
            'failover_only'
        ];
    }


    public function getParameters()
    {
        $connection = (new Database)->selectFirst("SELECT * FROM guacamole_connection
                                                   WHERE connection_id=?", [$this->id]);

        if (!$connection || count($connection) == 0) {
            return false;
        }

        $parameters = (new Database)->select("SELECT parameter_name AS name, parameter_value AS value
                                              FROM guacamole_connection_parameter
                                              WHERE connection_id=?", [$this->id]);
        foreach ($parameters as $param) {
            $connection[$param['name']] = $param['value'];
        }

        return $connection;
    }


    public function getParameter($name)
    {
        if (in_array($name, $this->connection_parameters)) {
            return (new Database)->selectOne("SELECT `$name` FROM guacamole_connection
                                              WHERE connection_id=?", [$this->id]);
        }

        return (new Database)->selectOne("SELECT parameter_value FROM guacamole_connection_parameter
                                          WHERE connection_id=? AND parameter_name=?", [$this->id,$name]);
    }


    public function setParameter($param, $value)
    {
        if (in_array($param, $this->connection_parameters)) {
            return (new Database)->update('guacamole_connection', [$param => $value], ['connection_id' => $this->id]);
        }

        return (new Database)->insertOne('guacamole_connection_parameter', [
            'connection_id' => $this->id,
            'parameter_name' => $param,
            'parameter_value' => $value,
        ], ['parameter_value']);
    }


    public function giveAccessTo(string $type, $id, $permission='READ')
    {
        switch ($type) {
          case 'group':
              $type = 'UserGroup';
              break;
          /*case 'user':
              $type = 'User';
              break;*/
          default:
            Logger::error("Usage error on Connection::giveAccessTo(): bad type ".$type);
            return false;
            break;
        }

        // get entity
        $entity = $type::getById($id);
        if (!$entity) {
            return false;
        }

        return (new Database)->insertOne('guacamole_connection_permission', [
            'entity_id' => $entity->entityId,
            'connection_id' => $this->id,
            'permission' => $permission,
        ], ['permission']);
    }


    public function wakeUp($port=9)
    {
        $macAddressHexadecimal = str_replace(':', '', $this->getParameter('wol-mac-addr'));

        // check if $macAddress is a valid mac address
        if (!ctype_xdigit($macAddressHexadecimal)) {
            Logger::warn('Cannot wol '.$this->id.': bad MAC address');
            return false;
        }

        $broadcastAddress = $this->getParameter('wol-broadcast-addr');
        if (!$broadcastAddress) {
            $broadcastAddress = '255.255.255.255';
        }

        $macAddressBinary = pack('H12', $macAddressHexadecimal);

        $magicPacket = str_repeat(chr(0xff), 6).str_repeat($macAddressBinary, 16);

        if (!$fp = fsockopen('udp://' . $broadcastAddress, $port, $errno, $errstr, 2)) {
            Logger::error('Cannot wol '.$this->id.': UDP socket error');
            return false;
        }
        fputs($fp, $magicPacket);
        fclose($fp);

        Logger::debug('Magic packet ('.$port.') sent to connection '.$this->id.' (bcast: '.$broadcastAddress.')');
        return true;
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


    public static function getByName($name)
    {
        $id = (new Database)->selectOne("SELECT connection_id
                                         FROM guacamole_connection
                                         WHERE connection_name=?", [$name]);
        if (!$id) {
            return false;
        }
        return new self($id);
    }


    public static function create($data)
    {
        $connectionId = (new Database)->selectOne("SELECT connection_id FROM guacamole_connection
                                                   WHERE connection_name=? and parent_id".($data['parent_id'] ? $data['parent_id'] : ' IS NULL'),
                                                  [$data['connection_name']]);

        if (!$connectionId) {
            $connectionId = (new Database)->insertOne('guacamole_connection', [
                'connection_name' => $data['connection_name'],
                'parent_id' => $data['parent_id'],
                'protocol' => $data['protocol'],
            ], ['protocol']);

            if (!$connectionId) {
                Logger::error('Error while insert connection '.$data['connection_name']);
                return false;
            }
        }

        $connection = new self($connectionId);

        foreach ($data as $param => $value) {
            $connection->setParameter($param, $value);
        }

        return $connection;
    }


    /**
     * Import connections
     * @param  array $data  Array of data
     * @return array        Results
     */
    public static function import($templateId, $connectionGroup, $csv, $userGroups=[], $separator=',')
    {
        $status = 'success';
        $imported = 0;
        $details = [];

        // get template default values
        $template = ConnectionTemplate::getById($templateId);
        if (!$template) {
            return [
                'status' => 'error',
                'details' => 'Template not found',
            ];
        }

        $default = $template->getParameters();

        // open CSV file
        try {
            if (($handle = fopen($csv, 'r')) === false) {
                Logger::error("failed to load CSV: ".$csv);
                return [
                    'status' => 'error',
                    'details' => 'Failed to import CSV',
                ];
            }
        } catch (Throwable $t) {
            Logger::error("failed to load CSV: ".$csv);
            return [
                'status' => 'error',
                'details' => 'Failed to import CSV',
            ];
        }

        // read CSV line by line
        $row = 0;
        while (($data = fgetcsv($handle, 0, $separator)) !== false) {
            $row++;

            // set headers
            if ($row == 1) {
                $headers = $data;
                // check if missing one required field
                if (!in_array('hostname', $headers)) {
                    return [
                        'status' => 'error',
                        'details' => 'Missing hostname field in CSV',
                    ];
                }
                continue;
            }

            // set default parameters
            $parameters = $default;
            // set custom parameters
            foreach ($data as $index => $value) {
                $param = $headers[$index];
                $parameters[$param] = $value;
            }

            // transform hostname => name
            if (!array_key_exists('connection_name', $parameters)) {
                $parameters['connection_name'] = explode('.', $parameters['hostname'])[0];
            }

            // add required fields
            $parameters['parent_id'] = null;
            if ($connectionGroup != '')
                $parameters['parent_id'] = $connectionGroup;

            if (isset($parameters['wol-mac-addr'])) {
                $parameters['wol-send-packet'] = 'true';
            }

            $connection = self::create($parameters);

            if ($connection) {
                // add access to users groups
                foreach ($userGroups as $groupId) {
                    $connection->giveAccessTo('group', $groupId);
                }
                $imported++;
            } else {
                $status = 'error';
                $details[] = 'Failed to import '.$parameters['hostname'];
            }
        }

        // close file
        fclose($handle);

        return [
            'status' => $status,
            'imported' => $imported,
            'details' => $details,
        ];
    }
}
