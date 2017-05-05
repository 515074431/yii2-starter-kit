<?php
define("PASS_IS_NOT_RIGHT",3);
define("PHONE_NUM_WRONG",2);
define("VERIFY_CODE_WRONG",6);
define("WRONG_PARAMS",8);
define("WRONG_PERIOD", 9);
define("WRONG_ORDER_ID",10);
define("WRONG_TYPE_CODE", 32);

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.TokenExpire' => 3600*24*30,
    'sms.codeExpire' => 600,
    'order.expire' => 15*60,
    'charge.max_cash' => 1000000,
    'charge.solid.balance' => 0.6,
    'error'=>[
        99999=>'未知错误',
        1=>'请求方法不是POST',
        2=>'手机号格式不正确',
        3=>'密码不正确',
        4=>define("PHONE_REGISTER_NO",4)?'此手机号还未注册':null,
        5=>define("PHONE_REGISTER_YES",5)?'该手机号已经被注册':null,
        6=>'验证码错误',
        7=>define("WRONG_TOKEN",7)?'token是错误的':null,
        8=>'请求参数错误',
        9 => '错误的期数',
        10=>'错误的订单',
        11=>define("USER_NO_MONEY",11)?'用户余额不足':null,
        12=>define("WRONG_PAY_METHOD",12)?'不支持的支付方式':null,
        13=>define("ORDER_ALREADY_PAY",13)?'该笔订单已经支付':null,
        14=>define("FRIEND_CODE_REPEAT",14)?'邀请码不能重复':null,
        15=>define("VERIFY_CODE_LIMIT",15)?'短信验证码达到每日上限':null,
        16=>define("CHANNEL_OR_AMOUNT_IS_EMPTY",16)?'支付渠道或金额为空':null,
        17=>define("WHRONG_BETCODE",17)?'错误的BETCODE':null,
        18=>define("WHRONG_MAX_CASH",18)?'提现额度限制':null,
        19=>define("ADVICEISEMPTY",19)?'投诉内容不能为空':null,
        20=>define("WHRONG_FRIEND_CODE",20)?'邀请码错误':null,
        21=>define("WHRONG_TIMES",21)?'错误的购彩倍数':null,
        22=>define("WHRONG_TYPECODE",22)?'错误的购彩种类':null,
        23=>define("MATCH_COUNT_LIMIT",23)?'比赛总场次最多15场':null,
        24=>define("MATCH_CHUAN_LIMIT",24)?'比赛串数太多':null,
        25=>define("MATCH_CHUAN_TOO_MANY",25)?'组合过关不允许多选':null,
        26=>define("DUANXIN_LIMIT",26)?'今日短信数已达上线':null,
        27=>define("CHARGE_LESS_100WAN",27)?'充值不能大于一百万':null,
        28=>define("IPHONE_TYPE_WRONG",28)?'换取的ipone的type不正确':null,
        29=>define("IPHONE_COUNT_WRONG",29)?'换取的ipone的碎片不够多':null,
        31=>define("KEYS_IS_NOT_ENOUGH",31)?'开宝箱的钥匙不够':null,
        32 => '错误的彩票类型',
        1001=>define("MATCHES_COUNT_WRONG",1001)?'比赛场次不足':null,
        2001=>define("PAY_PASS_NOT_SET",2001)?'支付密码未设置':null,
    ],
];
