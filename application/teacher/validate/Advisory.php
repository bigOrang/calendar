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
class Advisory extends Validate
{
    // 定义验证规则
    protected $rule = [
        "c_id|咨询区"      => "require",
        "title|咨询标题"      => "require|max:20|min:2|unique:AdvisoryModel,title",
        "is_show|是否仅自己查看"       => "require",
    ];

    protected $message  =   [
        'c_id.require' => '咨询区不能为空',
        'title.require' => '咨询标题不能为空',
        'title.max' => '咨询标题不能超过20个字符',
        'title.min' => '咨询标题不能少于2个字符',
        'title.unique' => '咨询标题不能重复',
        'is_show.require'  => '是否仅自己查看不能为空',
    ];
}
