<?php
namespace app\admin\controller;

use app\admin\model\AdvisoryModel;
use app\admin\model\CategoryModel;
use app\admin\model\SpecialModel;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\Request;

class Index extends Base
{
    public function index(Request $request)
    {
        return $this->fetch('./index');
    }
}
