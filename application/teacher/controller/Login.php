<?php
namespace app\teacher\controller;

use app\admin\model\SpecialModel;
use app\teacher\model\ClassModel;
use think\Controller;
use think\facade\Session;
use think\Request;

class Login extends Controller
{
    public function index()
    {
        $userId = session("teacher_user_id");
        $classModel = new ClassModel();
        $specialModel = new SpecialModel();
        $data = $classModel->alias("a")->where("supervisor1_code", $userId)
            ->leftJoin("t_sys_grade b","a.grade_no=b.no")
            ->whereOr("supervisor2_code", $userId)->field("a.class_no, a.grade_no, b.name as grade_name, a.name as class_name")
            ->select()->toArray();
        $grade = array_values(array_unique(array_column($data, "grade_no")));
        foreach ($grade as $list=>$info) {
            foreach ($data as $key=>$value) {
                if ($value['grade_no'] == $info) {
                    $grade[$list] = ['text'=> $value['grade_name'],'value' => $value['grade_no']];
                    $class[$list][] = ['value' => $value['class_no'], 'text'=> $value['class_no'].'班'];
                    $grade[$list]['children'] = $class[$list];
                }
            }
        }
        $topic = $specialModel->where("type",1)->field("id as value,title as text")->select()->toArray();
        $this->assign("topicData", json_encode($topic, 320));
        $this->assign("gradeData", json_encode($grade, 320));
        return $this->fetch('./login');
    }

    public function setGrade(Request $request)
    {
        if ($request->isPost()) {
            $requestData = $request->param();
            session("teacher_is_login",1);
            session("teacher_grade", $requestData['grade']);
            session("teacher_class", $requestData['class']);
            session("teacher_s_id", $requestData['topic']);
            return $this->responseToJson([],'success', 200);
        } else {
            return $this->responseToJson([],'非法的访问方式', 201);
        }
    }

    public function out()
    {
        Session::delete("teacher_is_login");
        Session::delete("teacher_grade");
        Session::delete("teacher_class");
        Session::delete("teacher_s_id");
        return redirect('./teacher/login/index');
    }

    public function responseToJson($data = [], $msg = 'ok', $code = 200, $default = true) {
        if ($default) {
            return [
                'code' => $code,
                'msg' => $msg,
                'data' => $data,
            ];
        }
        return json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
    }
}
