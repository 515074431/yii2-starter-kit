<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2018/5/29
 * Time: 上午10:32
 */

namespace api\modules\v1\models;

use Yii;
use common\models\User;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    /**
     * @var
     */
    public $password;
    /**
     * @var
     */
    public $user = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            /*['mobile','string','min'=>11,'max'=>'11'],
            ['mobile','validateMobile'],
            //['code','checkCode'],
            ['code',  function ($attribute, $params) {
                if(!\zc\yii2Alisms\Sms::checkCode($this->mobile,$this->code,$this->smsid)){
                    $this->addError($this->$attribute,'手机验证码不正确');
                    return false;
                }
            }],*/
            ['password', 'string', 'min' => 6],
        ];
    }
    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function changePassword()
    {
        if($this->user) {
            $this->user->password = $this->password;
            if ($this->user->save()) {
                return $this->user;
            } else {
                $this->addError('password', '修改密码失败');
                return false;
            }
        } else {
            $this->addError('password', '无权修改密码');
            return false;
        }


    }
}