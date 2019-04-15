<?php

namespace app\index\model;

use think\Db;
use think\Model;

class BaseModel extends Model
{
    protected static $school_id;
    protected $connection = '';
    public function __construct($data = [])
    {
        self::$school_id = session('index_school_id');
        $this->connection = session('index_db-config_' . self::$school_id);
        parent::__construct($data);
    }
}
