<?php
namespace app\index\controller;

use app\admin\model\CategoryModel;
use app\admin\model\SpecialModel;
use app\admin\model\TopicDetailModel;
use app\admin\model\TopicModel;
use app\index\model\CalendarDetailModel;
use app\index\model\CalendarDetailUserModel;
use app\index\model\CalendarModel;
use app\teacher\model\StudentModel;
use app\teacher\model\TopicUserModel;
use app\common\Steps;
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
        $events = $calendarDetailModel->where("c_id", $getCId)->field("start_time, end_time")->select()->toArray();
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
        $calendarModel = new CalendarModel();
        $calendarDetailModel = new CalendarDetailModel();
//        $getCId = $calendarModel
//            ->where("start_time","<", date("Y-m-d", time()))
//            ->where("end_time",">", date("Y-m-d", time()))->value("id");
        $getCId = session("index_c_id");
        $events = $calendarDetailModel->where("c_id", $getCId)
            ->where("start_time","<=", date("Y-m-d", $time))
            ->where("end_time",">=", date("Y-m-d", $time))
            ->order("id","desc")
            ->select();
        return $this->responseToJson($events, 'success', 200);
    }

    public function add(Request $request)
    {
        if ($request->isPost()) {
            $requestData = $this->validation($request->post(), 'CalendarDetail');
            Db::startTrans();
            try {
                $calendarModel = new CalendarModel();
//                $getCId = $calendarModel
//                    ->where("start_time","<", date("Y-m-d", time()))
//                    ->where("end_time",">", date("Y-m-d", time()))->value("id");
                $calendarDetailModel = new CalendarDetailModel();
                $id = $calendarDetailModel->insertGetId([
                    'c_id'       => session("index_c_id"),
                    'title'      => $requestData['title'],
                    'start_time' => $requestData['start_time'],
                    'end_time'   => $requestData['end_time'],
                    'push_type'  => $requestData['push_type'],
                ]);
                if ($requestData['push_type'] !== 0) {
                    $insertArr = [];
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
                }
                // 提交事务
                Db::commit();
                return $this->responseToJson(['target'=>url("index/index")],'添加成功');
            } catch (\Exception $e) { // 回滚事务
                Db::rollback();
                return $this->responseToJson([],'添加失败'.$e->getMessage() , 201);
            }
        }
        $this->assign("teacher", session("teacher"));
        return $this->fetch('./index/add');
    }

    public function update(Request $request)
    {
        $calendarDetailModel = new CalendarDetailModel();
        $calendarUsersModel = new CalendarDetailUserModel();
        if ($request->isPost()) {
            $requestData = $this->validation($request->post(), 'CalendarDetail');
            try {
                $calendarDetailModel->where("id", $requestData['id'])->update([
                    'title'      => $requestData['title'],
                    'start_time' => $requestData['start_time'],
                    'end_time'   => $requestData['end_time'],
                    'push_type'  => $requestData['push_type']
                ]);
                $calendarUsersModel->where("d_id", $requestData['id'])->delete();
                if ($requestData['push_type'] != 0) {
                    $insertArr = [];
                    foreach ($requestData['users'] as $k=>$v) {
                        $userArr = explode("_", $v);
                        $insertArr[$k] = [
                            'd_id' => $requestData['id'],
                            'user_code' => $userArr[0],
                            'user_name' => $userArr[1],
                        ];
                    }
                    $calendarUsersModel->insertAll($insertArr);
                }
                return $this->responseToJson(['target'=>url("index/index")],'编辑成功');
            } catch (\Exception $e) {
                return $this->responseToJson([],'编辑失败'.$e->getMessage() , 201);
            }
        }
        if ($request->has("id") && !empty($request->param("id"))) {
            $id = $request->param("id");
            $data = $calendarDetailModel->where("id", $id)->find();
            if ($data['push_type'] !== 0) {
                $data['is_hint'] = 1;
                $data['users'] = $calendarUsersModel->where("d_id", $id)->column("user_code");
                $data['push_type'] = $data['push_type'] == 1 ? [2,3] : [$data['push_type']];
            } else {
                $data['is_hint'] = 0;
                $data['push_type'] = [];
                $data['users'] = [];
            }
            $this->assign("info", $data);
            $this->assign("teacher", session("teacher"));
            return $this->fetch('./index/edit');
        } else {
            exit($this->alertInfo("相关参数未获取"));
        }
    }


    public function delete(Request $request)
    {
        if ($request->has("ids") && !empty($request->param("ids"))) {
            $ids = $request->param("ids");
            try{
                CalendarDetailModel::destroy(function($query) use ($ids) {
                    $query->where("id",$ids);
                });
                CalendarDetailUserModel::destroy(function($query) use ($ids) {
                    $query->whereIn("d_id",$ids);
                });
                return $this->responseToJson([],'删除成功' , 200);
            }catch (Exception $e) {
                return $this->responseToJson([],'删除失败'.$e->getMessage() , 201);
            }
        } else {
            return $this->responseToJson([],'相关参数未获取' , 201);
        }
    }


    public function validation($data, $name)
    {
        $valid = $this->validate($data, $name);
        if (true !== $valid) {
            exit($this->responseToJson([], $valid, 201, false));
        }
        return $data;
    }
}
