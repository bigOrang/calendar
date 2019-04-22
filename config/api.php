<?php
/**
 * Created by PhpStorm.
 * User: id_orange
 * Date: 2019/2/15
 * Time: 16:19
 */

return [
    'prefix' => [
        '47' => 'http://47.100.19.248:8000/',
        'mcpapi' => 'https://mcpapi.iyuyun.net:18443/',
    ],
    'getToken' => [//获取token
        'basic'=>[
            'username'=>'micro-service-salaryQuery',
            'password'=>'123456'
        ],
        'method' => 'post',
        'url' => 'oauth/token',
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
        'url' => 'oauth/service/staff/list',
        'header' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'school_id' => '1007',
            'mdc_value' => '12345-54321-12345-54321-12345',
            'master_key' => 'tUnmjGTZglI49CQWmsqhJQmSs83V2Y1e',
        ],
        'master_key_47' => 'abcdefghijkopqrstuvwxyz123456789',
        'master_key_mcpapi' => 'tUnmjGTZglI49CQWmsqhJQmSs83V2Y1e',
        'body' => []
    ],
    'getUserDetail' => [//校验令牌获取人员信息
        'method' => 'get',
        'url' => 'oauth/user/detail',
        'header' => [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'school_id'     => '1007',
            'mdc_value'     => '12345-54321-12345-54321-12345',
            'client_id'     => 'micro-service-salaryQuery'
        ],
        'body' => []
    ],
    'isManager' => [
        'method' => 'get',
        'url'   => 'oauth/service/config/admin?user_id=%s&service_id=%s',
        'header' => [
            'Content-Type'  => 'application/json',
            'school_id'     => '1002',
            'mdc_value'     => '12345-54321-12345-54321-12345'
        ]
    ],
    'taskAddTest' => [//定时任务添加
        'url' => 'https://testapi.happyn2.com/common/v1/cron/cron/task/add',
        'method' => 'post',
        'header' => [
            'Content-Type' => 'application/json'
        ],
        'body' => [
            'schoolId' => '1007',
            'cronJobModule' => 'SchoolCalendarPush',
            'cronJobName' => 'SchoolCalendarPush',
            'cronJobClassName' => 'com.viroyal.campus.cms.cron.job.impl.SchoolCalendarPushJob',
            'cronJobEnabled' => true,
            'cronJobDateType' => 0,
            'cronJobDesc' => '百家湖中学常规检查定时任务',
            'cronJobExp' => '0 30 17 * * ?',
            'cronJobTimeShow' => '17:30',
        ]
    ],
    'taskEditTest' => [
        'url' => 'https://testapi.happyn2.com/common/v1/cron/cron/task/%s/modify',
        'method' => 'put',
        'header' => [
            'Content-Type' => 'application/json'
        ],
        'body' => [
            'cronJobDesc' => '百家湖中学常规检查定时任务',
            'cronJobExp' => '0 30 17 * * ?',
            'cronJobTimeShow' => '17:30',
        ]
    ],
    'taskDeleteTest' => [
        'url' => 'https://testapi.happyn2.com/common/v1/cron/cron/task/%s/delete',
        'method' => 'delete',
        'header' => [
            'Content-Type' => 'application/json'
        ],
        'body' => [
            'cronJobDesc' => '百家湖中学常规检查定时任务',
            'cronJobExp' => '0 30 17 * * ?',
            'cronJobTimeShow' => '17:30',
        ]
    ]
];