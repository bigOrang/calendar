<?php
namespace app\teacher\controller;

use think\Controller;
use think\Db;
use think\facade\Log;
use think\facade\Session;

class Base extends Controller
{
    public function initialize()
    {
        $user_id = input('get.user_id');
        $school_id = input('get.school_id');
        $client_id = input('get.client_id');
        if (!empty($user_id) && !empty($school_id) && !empty($client_id)) {
            if (session('teacher_user_id') !== $user_id || session('teacher_school_id') !== $school_id) {
                Session::delete("teacher_is_login");
                Session::delete("teacher_grade");
                Session::delete("teacher_class");
                Session::delete("teacher_s_id");
            }
            session('teacher_user_id', $user_id);
            session('teacher_school_id', $school_id);
            session('teacher_client_id', $client_id);
            session('teacher_auth_status',1);
        } else {
            if (empty(session('teacher_auth_status'))) {
                exit($this->fetch('./403',[
                    'msg' => '身份过期，请重新登陆!'
                ]));
            }
        }
        if (empty($school_id)) {
            $school_id = session('teacher_school_id');
        }
        $db = Db::table('t_sys_mod_biz_db')->where('school_id', $school_id)->find();
        if ($db) {
            $config = [
                'type'            => 'mysql',
                'hostname'        => $db['db_server'],
                'database'        => $db['db_name'],
                'username'        => $db['db_user'],
                'password'        => $db['db_pass'],
                'hostport'        => $db['db_port'],
                'dsn'             => '',
                'params'          => [],
                'charset'         => 'utf8',
                'prefix'          => '',
            ];
            session('teacher_db-config_'.$school_id, $config);
        } else {
            exit($this->fetch('./404',[
                'msg' => '初始化数据失败!'
            ]));
        }
        if (!Session::has("teacher_is_login"))
            $this->redirect("./teacher/login/index");
    }
    /**
     * @param array $data
     * @param string $msg
     * @param int $code
     * @param bool $default
     * @return array|string
     */
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

    public function getUserInfo()
    {
        //获取token
        $tokenService = config('api.getToken');
        $userInfoService = config('api.getUserInfo');
        $basicHeader[] = "Authorization: Basic ".base64_encode("{$tokenService['basic']['username']}:{$tokenService['basic']['password']}"); //添加头，在name和pass处填写对应账号密码
        $tokenHeader = ['school_id:' . session("teacher_school_id")];
        $tokenHeader = array_merge($tokenHeader, $basicHeader);
        $tokenService['body']['username'] = session("teacher_user_id");
        $token = $this->curlRequest($tokenService['url'], $tokenService['method'], $tokenHeader, $tokenService['body'], []);
        if (!isset($token['access_token'])) {
            exit($this->fetch('./500',[
                'msg' => '获取token失败'
            ]));
        } else {
            $bearerHeader[] = "Authorization: Bearer ".$token['access_token'];
            $userInfoHeader = [
                'Content-Type:' . $userInfoService['header']['Content-Type'],
                'school_id:' . session("teacher_school_id"),
                'mdc_value:' . $userInfoService['header']['mdc_value'],
                'client_id:' . $userInfoService['header']['client_id'],
            ];
            $userInfoHeader = array_merge($userInfoHeader, $bearerHeader);
            $res = $this->curlRequest($userInfoService['url'], $userInfoService['method'], $userInfoHeader, $userInfoService['body'], []);
            if (isset($res['gender'])) {
                return $res;
            } else {
                exit($this->fetch('./500',[
                    'msg' => '获取用户详情失败'
                ]));
            }
        }
    }


    function curlRequest($url, $method = 'POST', $header = [], $data = '', $contentType = ['Content-Type:application/json'])
    {
        $headers = array_merge($contentType, [
            "Connection:Keep-Alive",
            'Accept:application/json',
        ], $header);
        // setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        // setting the POST FIELD to curl
        $method = strtoupper($method);
        switch ($method) {
            case"GET" :
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT" :
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        // getting response from server
        $response = curl_exec($ch);

        //close the connection
        curl_close($ch);
        //return the response
        if (stristr($response, 'HTTP 404') || $response == '') {
            return [
                'error_code' => '1001',
                'error_msg' => '请求错误'
            ];
        }

        return is_string($response) ? json_decode($response, true) : $response;
    }
    /**
     * 导出CSV
     * @param $num
     * @param $header
     * @param $sql
     * @param $dataRow
     * @param string $fileName
     */
    public function exportAllCsv($num, $header, $sql, $dataRow, $fileName = 'CSV文件')
    {
        $limit = 10000;
        $offset = 0;
        //将数据写入 导出文档
        header('Content-Type:application/csv');
        header('Content-Disposition:attachment;filename='.$fileName.'.csv');
        $fp = fopen('php://output', 'w');
        ob_start();
        foreach ($header as $key => $val) {
            $header[$key] = iconv('UTF-8', 'GBK//IGNORE', $val);
        }
        fputcsv($fp, $header);
        //循环获取导出数据并写入，一次最多取10000条
        for (; $offset < $num; $offset += $limit) {
            $sql = sprintf($sql, $limit, $offset);
            $data = Db::query($sql);
//            //循环写入
            foreach ($data as $row) {
                $line = array();
                $line[$dataRow[0]] = $row[$dataRow[0]];
                $line[$dataRow[1]] = $row[$dataRow[1]];
                $line[$dataRow[2]] = $row[$dataRow[2]];
                $line[$dataRow[3]] = $row[$dataRow[3]];
                $line[$dataRow[4]] = $row[$dataRow[4]];
                foreach ($line as $key => $val) {
                    $line[$key] = trim(mb_convert_encoding($val, 'gbk'));
                }
                fputcsv($fp, $line);
            }
        }
        fclose($fp);
        echo ob_get_clean();
    }

    /**
     * 消息提示模板
     * @param string $msg
     * @return mixed
     */
    protected function alertInfo($msg = '')
    {
        return $this->fetch('./common/alert-info', [
            'msg' => $msg,
        ]);
    }


    protected function updateBatch($tableName = "", $multipleData = array()){

        if( $tableName && !empty($multipleData) ) {
            // column or fields to update
            $updateColumn = array_keys($multipleData[0]);
            $referenceColumn = $updateColumn[0]; //e.g id
            unset($updateColumn[0]);
            $whereIn = "";

            $q = "UPDATE ".$tableName." SET ";
            foreach ( $updateColumn as $uColumn ) {
                $q .=  $uColumn." = CASE ";

                foreach( $multipleData as $data ) {
                    $q .= "WHEN ".$referenceColumn." = ".$data[$referenceColumn]." THEN '".$data[$uColumn]."' ";
                }
                $q .= "ELSE ".$uColumn." END, ";
            }
            foreach( $multipleData as $data ) {
                $whereIn .= "'".$data[$referenceColumn]."', ";
            }
            $q = rtrim($q, ", ")." WHERE ".$referenceColumn." IN (".  rtrim($whereIn, ', ').")";
            // Update
            return Db::connect(session('teacher_db-config_' . session("teacher_school_id")))->execute(DB::raw($q));
        } else {
            return false;
        }
    }

}
