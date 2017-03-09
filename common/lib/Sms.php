<?php
namespace common\lib;

class Sms extends \yii\base\Object {
    //随机数或随机字符,可定长度,默认4位数字
    static function random($length = 4 , $numeric = true) {
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

    static function sendCodeByHuYi($tel){
        $mobile_code = self::random(4);
        $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
        $content = "您的验证码是：【{$mobile_code}】。请不要把验证码泄露给其他人。如非本人操作，可不用理会！";
        $post_data = "account=cf_shff&password=ff123456&mobile=".$tel."&content=".rawurlencode($content);
        $gets = self::xml_to_array(self::Post($post_data,$target));

        \Yii::$app->cache->add('sendCode'.$tel,$mobile_code,600);

        if($gets['SubmitResult']['code']==2){
            //发送短信成功
            return [
                'status'=>true,
                'code'=>$mobile_code,
                'message'=>'短信发送成功'
            ];
        }else{
            return [
                'status'=>false,
                'code'=>$mobile_code,
                'message'=>$gets['SubmitResult']['msg'],//'发送短信失败',
                '$gets'=>$gets
            ];
            //return self::sendCode($tel);
        }
    }
    static function checkCode($phone,$code){
        $cacheCode = \Yii::$app->cache->get('sendCode'.$phone);
        //$cache = \Yii::$app->cache;
        //var_dump([$code ,$phone, $cacheCode,$cache]);exit;
        return $code === $cacheCode;
    }

    public static function Post($curlPost,$url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    public  static function xml_to_array($xml){
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches)){
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++){
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml )){
                    $arr[$key] = self::xml_to_array($subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }
}