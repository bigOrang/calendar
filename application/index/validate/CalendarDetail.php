<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;

/**
 * 客服验证器
 * @package app\cms\validate
 * @author 蔡伟明 <314013107@qq.com>
 */
class CalendarDetail extends Validate
{
    // 定义验证规则
    protected $rule = [
        "title|事件标题"      => "require|max:100|min:2",
        "start_time|事件开始时间"       => "require",
        "end_time|事件结束时间"       => "require",
        "is_hint|事件是否推送"       => "require",
//        "show_type|事件推送方式"       => "require",
    ];

    protected $message  =   [
        'title.require' => '事件标题不能为空',
        'title.max' => '事件标题不能超过100个字符',
        'title.min' => '事件标题不能少于2个字符',
//        'title.unique' => '事件标题不能重复',
        'start_time.require'  => '事件开始时间不能为空',
        'end_time.require'  => '事件结束时间不能为空',
        'is_hint.require'  => '事件是否推送不能为空',
    ];
}
