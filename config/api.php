<?php
/**
 * Created by PhpStorm.
 * User: id_orange
 * Date: 2019/2/15
 * Time: 16:19
 */

return [
    'getToken' => [//获取token
        'basic'=>[
            'username'=>'micro-service-salaryQuery',
            'password'=>'123456'
        ],
        'method' => 'post',
        'url' => 'https://mcpapi.iyuyun.net:18443/oauth/token',
        'header' => [
            'school_id' => '1007',
        ],
        'body' => [
            'grant_type' => 'password',
            'scope' => 'read',
            'username' => '51327',
            'password' => '7c4a8d09ca3762af61e59520943dc26494f8941b1',
        ]
    ],
    'getTeacher' => [//获取教师名单
        'method' => 'get',
        'url' => 'https://mcpapi.iyuyun.net:18443/oauth/service/staff/list',
        'header' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'school_id' => '1007',
            'mdc_value' => '12345-54321-12345-54321-12345',
            'master_key' => 'tUnmjGTZglI49CQWmsqhJQmSs83V2Y1e',
        ],
        'body' => []
    ],
];