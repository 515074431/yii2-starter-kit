<?php
namespace common\lib;

class AliSms extends \yii\base\Object {
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

    static function sendCode($tel){
        $randomCode = self::random(4);

        include "alidayu/TopSdk.php";
        $c = new \TopClient;
        $c ->appkey = '23773211' ;// 可替换为您的沙箱环境应用的AppKey
        $c ->secretKey = 'c4ffc305083eeb12488734a245b4f85d' ;// 可替换为您的沙箱环境应用的AppSecret
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "庆茂科技" );
        $req ->setSmsParam( "{code:'$randomCode',product:'聊天软件'}" );
        $req ->setRecNum( "15618769991" );
        $req ->setSmsTemplateCode( "SMS_63140356" );
        $resp = $c ->execute( $req );


        /*$c = new \TopClient;
        $c->appkey = "23773211"; // 可替换为您的沙箱环境应用的AppKey
        $c->secretKey = "c4ffc305083eeb12488734a245b4f85d"; // 可替换为您的沙箱环境应用的AppSecret
        $sessionkey= "test";  // 必须替换为沙箱账号授权得到的真实有效SessionKey

        $req = new \SellerItemGetRequest;
        $req->setFields("num_iid,title,nick,price,approve_status,sku");
        $req->setNumIid("123456789");
        $rsp = $c->execute($req,$sessionkey);*/

        //echo "api result:";
        //print_r($resp);exit;

        if(isset($resp->result)){
            if($resp->result->success){
                //短信发送成功信息
                //发送短信成功
                return [
                    'status'=>true,
                    'code'=>$randomCode,
                    'message'=>'短信发送成功'
                ];
                //return true;
            }else{
                //短信发送失败操作
                return [
                    'status'=>false,
                    'code'=>$randomCode,
                    'message'=>$resp->result->msg,//'发送短信失败',
                    'err_code'=>$resp->result->err_code
                ];
                return false;
            }
        }else{//短信发送异常
            return [
                'status'=>false,
                'code'=>$randomCode,
                'message'=>$resp,
                'error_response'=>$resp
            ];
            return false;
        }



        //$sessionkey= "test";  // 必须替换为沙箱账号授权得到的真实有效SessionKey
        //$rsp = $c->execute($req,$sessionkey);

        //echo "api result:";
       // print_r($resp);



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