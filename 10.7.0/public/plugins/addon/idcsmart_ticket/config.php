<?php

return [
    # 刷新时间,默认3分钟
    //'refresh_time' => 3,

    'refresh_time'          => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '刷新时间', // 表单的label标题
        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => 3, // 表单的默认值
        'tip'   => '', //表单的帮助提示
    ],
];
