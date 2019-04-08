<?php
/**
 * Created by PhpStorm.
 * User: id_orange
 * Date: 2019/2/15
 * Time: 13:43
 */

namespace app\admin\model;


use think\Model;
use think\model\concern\SoftDelete;

class CalendarModel extends BaseModel
{
//    use SoftDelete;
    protected $table = "t_sys_calendar";
//    protected $dateFormat = 'Y-m-d H:i:s';
//    protected $autoWriteTimestamp = 'datetime';
//    protected $deleteTime = 'deleted_at';

}