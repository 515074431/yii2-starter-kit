<?php
namespace api\modules\v1\models;


use common\lib\AliSms;
use common\models\User;
use Yii;
use yii\base\Model;

/**
 * GET CODE
 */
class GetCode extends Model
{
    public $mobile;
    //public $type = 'register';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // mobile are both required
            [['mobile'], 'required'],
            // mobile is validated by validateMobile()
            ['mobile', 'validateMobile'],
        ];
    }
    public function afterValidate()
    {
        parent::afterValidate();
        if ($this->hasErrors()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
        }
    }

    public function attributeLabels()
    {
        return [
            'code'=>'短信验证码',
        ];
    }


    /**
     * Validates the Mobile.
     * This method serves as the inline validation for Mobile.
     */
    public function validateMobile()
    {
        if(preg_match("/^1[34578]\d{9}$/", $this->mobile)){
            $user = User::findByMobile($this->mobile);
            if(!$user){
                return true;
            }else{
                $this->addError('mobile','手机已注册');
            }
        }else{
            $this->addError('mobile','手机格式不正确');
        }
    }

    /**
     * 发验证码
     * @return mixed
     */
    public function sendCode()
    {
        $return = AliSms::sendCode($this->mobile);
        if(!$return['status']){
            $this->addError('code','短信验证码不正确');
        }
        return [
            //'status'=>true,
            'code'=>$return['code'],
            'message'=>$return['message']
        ];
    }

}
