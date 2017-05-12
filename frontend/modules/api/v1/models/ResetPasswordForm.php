<?php
namespace frontend\modules\api\v1\models;

use common\lib\Sms;
use common\models\User;
use common\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    /**
     * @var
     */
    public $mobile;
    /**
     * @var
     */
    public $password;
    /**
     * @var
     */
    public $code;

    /**
     * @var \common\models\UserToken
     */
    //private $token;


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
            [['mobile','code','password'], 'required'],
            ['mobile','string','min'=>11,'max'=>'11'],
            ['mobile','validateMobile'],
            //['code','checkCode'],
            ['code',  function ($attribute, $params) {
                $smsTtype = 1;
                if(!\zc\yii2Alisms\Sms::checkCode($this->mobile,$this->code,$smsTtype)){
                    $this->addError($this->$attribute,'手机验证码不正确');
                    return false;
                }
            }],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        $user = User::findByMobile($this->mobile);
        $user->reSetPassword($this->password);
        if($user->save()) {
            return $user;
        }else{
            $this->addError('password','修改密码失败');
            return false;
        }


    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'password'=>Yii::t('frontend', 'Password')
        ];
    }
    /**
     * 检验手机验证码
     */
    public function checkCode(){
        if(Sms::checkCode($this->mobile,$this->code)){
            return true;
        }else{
            $this->addError('code','手机验证码不正确');
            return false;
        }
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
