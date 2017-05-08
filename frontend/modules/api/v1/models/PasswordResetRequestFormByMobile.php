<?php
namespace frontend\modules\api\v1\models;

use cheatsheet\Time;
use common\commands\SendEmailCommand;
use common\lib\AliSms;
use common\models\UserToken;
use Yii;
use common\models\User;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestFormByMobile extends Model
{
    /**
     * @var user mobile
     */
    public $mobile;

    public function afterValidate()
    {
        parent::afterValidate();
        if ($this->hasErrors()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
        }
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'validateMobile'],// 验证手机号
            ['mobile', 'exist',
                'targetClass' => '\common\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => '手机号不存在'
            ],
        ];
    }


    /**
     * 发送验证码
     * Sends an ValidateCode to mobile, for resetting the password.
     *
     * @return boolean whether the mobile was send
     */
    public function sendValidateCode()
    {

        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'mobile' => $this->mobile,
        ]);
        if ($user) {
            $token = UserToken::create($user->id, UserToken::TYPE_PASSWORD_RESET, Time::SECONDS_IN_A_DAY);
            if ($user->save()) {

                $return = AliSms::sendCode($this->mobile);
                if(!$return['status']){
                    $this->addError('code','短信验证码不正确');
                }
                return [
                    //'status'=>true,
                    'code'=>$return['code'],
                    'message'=>$return['message']
                ];

            }else{
                $this->addError('mobile','用户保存失败');
            }
        }else{
            $this->addError('mobile','用户失效');
        }
        return false;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'mobile'=>'手机'
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
            if($user){
                return true;
            }else{
                $this->addError('mobile','手机未注册');
            }
        }else{
            $this->addError('mobile','手机格式不正确');
        }
    }
}
