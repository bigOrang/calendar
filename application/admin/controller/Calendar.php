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
            $title = CalendarModel::where("id", $cId)->value("title");
            $data = CalendarDetailModel::where("c_id", $cId)->field("id,title,start_time as start,end_time as end,'bg-purple' as className")->select()->toArray();
            $this->assign("data", json_encode($data, 320));
            $this->assign("name", $title);
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
                $calendarDetailModel = new CalendarDetailModel();
                $id = $calendarDetailModel->insertGetId([
                    'c_id'       => $requestData['c_id'],
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
                return $this->responseToJson([],'添加成功');
            } catch (\Exception $e) { // 回滚事务
                Db::rollback();
                return $this->responseToJson([],'添加失败'.$e->getMessage() , 201);
            }
        }
        if ($request->has("c_id") && !empty($request->param("c_id"))) {
            $cId = $request->param("c_id");
            $this->assign("teacher", session("teachers"));
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
                $calendarDetailModel->where("id", $requestData['id'])->update([
                    'c_id'       => $requestData['c_id'],
                    'title'      => $requestData['title'],
                    'start_time' => $requestData['start_time'],
                    'end_time'   => $requestData['end_time'],
                    'push_type'  => $requestData['push_type']
                ]);
                $calendarUserModel = new CalendarDetailUserModel();
                $calendarUserModel->where("d_id", $requestData['id'])->delete();
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
                    $calendarUserModel->insertAll($insertArr);
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
                $data['push_type'] = $data['push_type'] == 1 ? [2,3] : [$data['push_type']];
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
        unset($data['is_hint']);
        Log::error($data);
        return $data;
    }
}
