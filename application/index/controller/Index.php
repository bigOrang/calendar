<?php
namespace app\index\controller;

use app\index\model\CalendarDetailModel;
use app\index\model\CalendarDetailUserModel;
use app\index\model\CalendarModel;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Session;
use think\Request;

class Index extends Base
{
    public function index(Request $request)
    {
        $dates = [];
        $calendarModel = new CalendarModel();
        $calendarDetailModel = new CalendarDetailModel();
        if (Session::has("index_c_id")) {
            $getCId = session("index_c_id");
        } else {
            $getCId = $calendarModel
                ->where("start_time","<", date("Y-m-d", time()))
                ->where("end_time",">", date("Y-m-d", time()))->value("id");
            session("index_c_id", $getCId);
        }
        $userDetail = session("index_userDetail");
        $events = $calendarDetailModel->where("c_id", $getCId)->field("start_time, end_time")
            ->where(function ($query) use ($userDetail) {
                $query ->where("is_manager", 1)
                    ->whereOr("release_user_code", $userDetail['user_id']);
            })
            ->select()->toArray();
        foreach ($events as $key=>$value) {
            $dates = array_merge($dates, $this->getDateFromRange($value['start_time'], $value['end_time']));
        }
        $dates = array_values(array_unique($dates));
        $this->assign("date", json_encode($dates));
        return $this->fetch('./index/index');
    }

    public function getEvents(Request $request)
    {
        if ($request->has("time")) {
            $time = substr($request->param("time"),0,10);
        } else {
            $time = time();
        }
        $calendarDetailModel = new CalendarDetailModel();
        $getCId = session("index_c_id");
        $userDetail = session("index_userDetail");
        $events = $calendarDetailModel->where("c_id", $getCId)
            ->where("start_time","<=", date("Y-m-d", $time))
            ->where("end_time",">=", date("Y-m-d", $time))
            ->where(function ($query) use ($userDetail) {
                $query ->where("is_manager", 1)
                    ->whereOr("release_user_code", $userDetail['user_id']);
            })
            ->order("id","desc")
            ->select();
        return $this->responseToJson($events, 'success', 200);
    }

    public function add(Request $request)
    {
        if ($request->isPost()) {
            $requestData = $this->validation($request->post(), 'CalendarDetail');
            Db::connect(session('index_db-config_' . session("index_school_id")))->startTrans();
            try {
                $userDetail = session("index_userDetail");
                $calendarDetailModel = new CalendarDetailModel();
                if ($requestData['push_type'] !== 0) {
                    $insertArr = $task = [];
                    $task = $this->taskOperational($requestData);
                    $id = $calendarDetailModel->insertGetId([
                        'c_id'       => session("index_c_id"),
                        'title'      => $requestData['title'],
                        'start_time' => $requestData['start_time'],
                        'end_time'   => $requestData['end_time'],
                        'push_type'  => $requestData['push_type'],
                        'cron_ids'   => $task,
                        'is_manager' => '0',
                        'release_user_code'    => $userDetail["user_id"],
                        'release_user_name'    => $userDetail["user_name"],
                    ]);
                    $calendarUserModel = new CalendarDetailUserModel();
                    foreach ($requestData['users'] as $k=>$v) {
                        $userArr = explode("_", $v);
                        $insertArr[$k] = [
                            'd_id' => $id,
                            'user_code' => $userArr[0],
                            'user_name' => $userArr[1],
                        ];
                    }
                    $calendarUserModel->insertAll($insertArr);
                } else {
                    $id = $calendarDetailModel->insertGetId([
                        'c_id'       => $requestData['c_id'],
                        'title'      => $requestData['title'],
                        'start_time' => $requestData['start_time'],
                        'end_time'   => $requestData['end_time'],
                        'push_type'  => $requestData['push_type'],
                        'cron_ids'    => '',
                        'release_user_code'    => $userDetail["user_id"],
                        'release_user_name'    => $userDetail["user_name"],
                    ]);
                }
                // 提交事务
                Db::connect(session('index_db-config_' . session("index_school_id")))->commit();
                return $this->responseToJson(['target'=>url("index/index")],'添加成功');
            } catch (\Exception $e) { // 回滚事务
                Db::connect(session('index_db-config_' . session("index_school_id")))->rollback();
                return $this->responseToJson([],'添加失败'.$e->getMessage() , 201);
            }
        }
        $this->assign("teacher", session("index_teachers"));
        return $this->fetch('./index/add');
    }

    public function update(Request $request)
    {
        $calendarDetailModel = new CalendarDetailModel();
        $calendarUsersModel = new CalendarDetailUserModel();
        if ($request->isPost()) {
            $requestData = $this->validation($request->post(), 'CalendarDetail');
            Db::connect(session('index_db-config_' . session("index_school_id")))->startTrans();
            try {
                $userDetail = session("index_userDetail");
                if ($requestData['push_type'] !== 0) {
                    $insertArr = $task = [];
                    //删除定时任务再添加
                    $cronArr = explode(",", $requestData['cron_ids']);
                    foreach ($cronArr as $value)
                        $this->taskCalendar("delete", "","","",$value);
                    $task = $this->taskOperational($requestData);
                    $calendarDetailModel->where("id", $requestData['id'])->update([
                        'c_id'       => $requestData['c_id'],
                        'title'      => $requestData['title'],
                        'start_time' => $requestData['start_time'],
                        'end_time'   => $requestData['end_time'],
                        'push_type'  => $requestData['push_type'],
                        'cron_ids'    => $task,
                        'release_user_code'    => $userDetail["user_id"],
                        'release_user_name'    => $userDetail["user_name"]
                    ]);
                    $calendarUsersModel->where("d_id", $requestData['id'])->delete();
                    foreach ($requestData['users'] as $k=>$v) {
                        $userArr = explode("_", $v);
                        $insertArr[$k] = [
                            'd_id' => $requestData['id'],
                            'user_code' => $userArr[0],
                            'user_name' => $userArr[1],
                        ];
                    }
                    $calendarUsersModel->insertAll($insertArr);
                } else {
                    $calendarDetailModel->where("id", $requestData['id'])->update([
                        'c_id'       => $requestData['c_id'],
                        'title'      => $requestData['title'],
                        'start_time' => $requestData['start_time'],
                        'end_time'   => $requestData['end_time'],
                        'push_type'  => $requestData['push_type'],
                        'release_user_code'    => $userDetail["user_id"],
                        'release_user_name'    => $userDetail["user_name"],
                        'cron_ids'   => ''
                    ]);
                }
                // 提交事务
                Db::connect(session('index_db-config_' . session("index_school_id")))->commit();
                return $this->responseToJson(['target'=>url("index/index")],'编辑成功');
            } catch (\Exception $e) {
                // 回滚事务
                Db::connect(session('index_db-config_' . session("index_school_id")))->rollback();
                return $this->responseToJson([],'编辑失败'.$e->getMessage() , 201);
            }
        }
        if ($request->has("id") && !empty($request->param("id"))) {
            $id = $request->param("id");
            $data = $calendarDetailModel->where("id", $id)->find();
            if ($data['push_type'] !== 0) {
                $data['is_hint'] = 1;
                $data['users'] = $calendarUsersModel->where("d_id", $id)->column("user_code");
                $decbin = decbin($data['push_type']);
                $push_type = array_keys(array_filter(str_split($decbin)));
                foreach ($push_type as $key=>$value)
                    $push_type[$key] = (intval($value)+1);
                $data['push_type'] = $push_type;
            } else {
                $data['is_hint'] = 0;
                $data['push_type'] = [];
                $data['users'] = [];
            }
            $this->assign("info", $data);
            $this->assign("teacher", session("index_teachers"));
            return $this->fetch('./index/edit');
        } else {
            exit($this->alertInfo("相关参数未获取"));
        }
    }


    public function delete(Request $request)
    {
        if ($request->has("ids") && !empty($request->param("ids"))) {
            $ids = $request->param("ids");
            Db::connect(session('index_db-config_' . session("index_school_id")))->startTrans();
            try{
                $cronIds = CalendarDetailModel::where("id", $ids)->value("cron_ids");
                CalendarDetailModel::destroy(function($query) use ($ids) {
                    $query->where("id",$ids);
                });
                if (!empty($cronIds)) {
                    $cronArr = explode(",", $cronIds);
                    foreach ($cronArr as $value)
                        $this->taskCalendar("delete", "","","",$value);
                    CalendarDetailUserModel::destroy(function($query) use ($ids) {
                        $query->whereIn("d_id",$ids);
                    });
                }
                // 提交事务
                Db::connect(session('index_db-config_' . session("index_school_id")))->commit();
                return $this->responseToJson([],'删除成功' , 200);
            }catch (Exception $e) {
                // 回滚事务
                Db::connect(session('index_db-config_' . session("index_school_id")))->rollback();
                return $this->responseToJson([],'删除失败'.$e->getMessage() , 201);
            }
        } else {
            return $this->responseToJson([],'相关参数未获取' , 201);
        }
    }


    public function taskOperational($data)
    {
        $cron = [];
        $task = [];
        $startT = explode("-", $data['start_time']);
        $endT   = explode("-", $data['end_time']);
        $years = $endT[0] - $startT[0];
        $time = $data['start_time'] . ' - '. $data['end_time'];
        if ($years >= 1) {
            if ($years > 1) {
                $year = ((intval($endT[0])) - (intval($startT[0]))) !== 2 ? (intval($startT[0])+1) ."-". (intval($endT[0])-1) : (intval($startT[0])+1) ;
                $cron[] = "0 30 08 ? ? ? {$year}";
            }
            $thisMonthDays = cal_days_in_month(CAL_GREGORIAN,$startT[1],$startT[0]);
            $cron[] = "0 30 08 {$startT[2]}-{$thisMonthDays} {$startT[1]} ? {$startT[0]}";
            if (12 - $startT[1] > 0) {
                $thisMonth = intval($startT[1])+1;
                $cron[] = "0 30 08 ? {$thisMonth}-12 ? ? {$startT[0]}";
            }
            $cron[] = "0 30 08 01-{$endT[2]} {$endT[1]} ? {$endT[0]}";
            if ($endT[1] - 1 > 0) {
                $lastMonth = intval($endT[1])-1;
                $cron[] = "0 30 08 ? 01-{$lastMonth} ? ? {$endT[0]}";
            }
        } else {
            $months = $endT[1] - $startT[1];
            if ($months >= 1) {
                if ($months > 1) {
                    $month = (intval($endT[1]) - intval($startT[1])) !== 2 ? (intval($startT[1])+1) ."-". (intval($endT[1])-1) :(intval($startT[1])+1);
                    $cron[] = "0 30 08 ? {$month} ? {$startT[0]}";
                }
                $thisMonthDays = cal_days_in_month(CAL_GREGORIAN,$startT[1],$startT[0]);
                $cron[] = "0 30 08 {$startT[2]}-{$thisMonthDays} {$startT[1]} ? {$startT[0]}";
                $cron[] = "0 30 08 01-{$endT[2]} {$endT[1]} ? {$endT[0]}";
            } else {
                $days = $endT[2] - $startT[2];
                if ($days >= 1) {
                    $cron[] = "0 30 08 {$startT[2]}-{$endT[2]} {$startT[1]} ? {$endT[0]}";
                } else {
                    $cron[] = "0 30 08 {$endT[2]} {$startT[1]} ? {$endT[0]}";
                }
            }
        }
        $cron = array_unique($cron);
        foreach ($cron as $key=>$value) {
            $task[$key] = $this->taskCalendar("add", $time, $value ,$data['title']);
        }
        $task = implode(",", array_column($task, "id"));
        return $task;
    }

    public function validation($data, $name)
    {
        $valid = $this->validate($data, $name);
        if (true !== $valid) {
            exit($this->responseToJson([], $valid, 201, false));
        }
        $push_type = 0;
        if (is_array($data['push_type'])) {
            foreach ($data['push_type'] as $key=>$value) {
                $push_type += pow(2,$value-1);
            }
        } else {
            $push_type += pow(2,$data['push_type']-1);
        }
        $data['push_type'] = $push_type;
        return $data;
    }
}
