<?php
namespace app\admin\controller;

use app\admin\model\CalendarDetailModel;
use app\admin\model\CalendarDetailUserModel;
use app\admin\model\CalendarModel;
use app\admin\validate\CalendarDetail;
use think\Db;
use think\Exception;
use think\facade\Log;
use think\Request;

class Calendar extends Base
{
    public function index(Request $request)
    {
        $calendarModel = new CalendarModel();
        if ($request->isPost()) {
            try{
                $limit = $request->param('limit', 10);
                $searchData = $request->param();
                $data = $calendarModel->alias("a")->where(function ($query) use ($searchData) {
                    //模糊搜索
                    if (isset($searchData['search']) && !empty($searchData['search'])) {
                        $query->where('a.title', 'like', '%' . $searchData['search'] . '%');
                    }
                    })->paginate($limit);
                $data = json_decode(json_encode($data),true);
            } catch (Exception $exception) {
                Log::error('获取数据错误：'. $exception->getMessage());
                $data = ['total' => 0, 'rows' => []];
            }
            return [
                'total' => $data['total'],
                'rows' => $data['data']
            ];
        }
        return $this->fetch('./calendar/index');
    }

    public function detail(Request $request)
    {
        if ($request->has("id") && !empty($request->param("id"))) {
            $cId = $request->param("id");
            $result = CalendarModel::where("id", $cId)->find()->toArray();
            $data = CalendarDetailModel::where("c_id", $cId)->field("id,title,start_time as start,end_time as end,'bg-purple' as className")->select()->toArray();
            foreach ($data as $key=>$value) {
                if (strtotime($value['start']) !== strtotime($value['end'])) {
                    $data[$key]['end'] = date("Y-m-d", strtotime($value['end'] ."+1 day"));
                }
            }
            if (strtotime($result['start_time']) <= time() && time() <= strtotime($result['end_time'])) {
                $is_in = date("Y-m-d");
            } else {
                $is_in = $result['start_time'];
            }
            $this->assign("is_in", $is_in);
            $this->assign("data", json_encode($data, 320));
            $this->assign("name", $result['title']);
            $this->assign("c_id", $cId);
            return $this->fetch('./calendar/detail');
        } else {
            $this->alertInfo("缺少必要参数");
        }

    }

    public function add(Request $request) {
        if ($request->isPost()) {
            $requestData = $this->validation($request->post(), 'CalendarDetail');
            Db::startTrans();
            try {
                $userDetail = session("userDetail");
                $calendarDetailModel = new CalendarDetailModel();
                if ($requestData['push_type'] !== 0) {
                    $insertArr = $task = [];
                    $task = $this->taskOperational($requestData);
                    $id = $calendarDetailModel->insertGetId([
                        'c_id'       => $requestData['c_id'],
                        'title'      => $requestData['title'],
                        'start_time' => $requestData['start_time'],
                        'end_time'   => $requestData['end_time'],
                        'push_type'  => $requestData['push_type'],
                        'cron_ids'    => $task,
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
                Db::commit();
                return $this->responseToJson([],'添加成功');
            } catch (\Exception $e) { // 回滚事务
                Db::rollback();
                return $this->responseToJson([],'添加失败'.$e->getMessage() , 201);
            }
        }
        if ($request->has("c_id") && !empty($request->param("c_id"))) {
            $cId = $request->param("c_id");
            if ($request->has("start") && $request->has("end")) {
                $start = $request->param("start");
                $end = $request->param("end");
            } else {
                $start = $end = time().'000';
            }
            $sTime = date("Y-m-d", substr($start,0,10));
            $eTime = date("Y-m-d", strtotime(date("Y-m-d", substr($end,0,10)). "-1 day"));
            $this->assign("teacher", session("teachers"));
            $this->assign("sTime", $sTime);
            $this->assign("eTime", $eTime);
            $this->assign("c_id", $cId);
            return $this->fetch('./calendar/add');
        } else {
            $this->alertInfo("缺少必要参数");
        }
    }

    public function update(Request $request)
    {
        $calendarDetailModel = new CalendarDetailModel();
        $calendarUsersModel = new CalendarDetailUserModel();
        if ($request->isPost()) {
            $requestData = $this->validation($request->post(), 'CalendarDetail');
            try {
                $userDetail = session("userDetail");
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
                return $this->responseToJson([],'编辑成功');
            } catch (\Exception $e) {
                return $this->responseToJson([],'编辑失败'.$e->getMessage() , 201);
            }
        }
        if ($request->has("id") && !empty($request->param("id")) && $request->has("c_id") && !empty($request->param("c_id"))) {
            $id = $request->param("id");
            $cId = $request->param("c_id");
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
            $this->assign("c_id", $cId);
            $this->assign("teacher", session("teachers"));
            return $this->fetch('./calendar/edit');
        } else {
            exit($this->alertInfo("相关参数未获取"));
        }
    }

    public function delete(Request $request)
    {
        if ($request->has("ids") && !empty($request->param("ids"))) {
            $ids = $request->param("ids");
            Db::startTrans();
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
                Db::commit();
                return $this->responseToJson([],'删除成功' , 200);
            }catch (Exception $e) {
                // 回滚事务
                Db::rollback();
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
        if (isset($data['time'])) {
            $time = explode(" - ", $data['time']);
            $data['start_time'] = $time[0];
            $data['end_time'] = $time[1];
        } else {
            exit($this->responseToJson([], '未获取到事件时间', 201, false));
        }
        if (!isset($data['c_id'])) {
            exit($this->responseToJson([], '缺少必要参数', 201, false));
        }
        $push_type = 0;
        if (is_array($data['push_type'])) {
            foreach ($data['push_type'] as $key=>$value) {
                $push_type += pow(2,$value-1);
            }
        } else {
            $push_type += pow(2,intval($data['push_type'])-1);
        }

        $data['push_type'] = $push_type;
        unset($data['is_hint']);
        return $data;
    }
}
