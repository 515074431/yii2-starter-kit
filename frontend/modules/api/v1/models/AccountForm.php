<?php
namespace frontend\modules\api\v1\models;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;

/**
 * Account form
 */
class AccountForm extends Model
{
    public $username;
    public $mobile;
    public $password;
    public $password_confirm;

    private $user;

    public function setUser($user)
    {
        $this->user = $user;
        $this->mobile = $user->mobile;
        $this->username = $user->username;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique',
                'targetClass' => '\common\models\User',
                'message' => Yii::t('frontend', 'This username has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ],
            ['username', 'string', 'min' => 1, 'max' => 255],
            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'validateMobile'],// 验证手机号
            ['mobile', 'unique',
                'targetClass' => '\common\models\User',
                'message' => Yii::t('frontend', 'This mobile has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['not', ['id' => Yii::$app->user->getId()]]);
                }
            ],
            ['password', 'string'],
            [
                'password_confirm',
                'required',
                'when' => function($model) {
                    return !empty($model->password);
                },
                'whenClient' => new JsExpression("function (attribute, value) {
                    return $('#caccountform-password').val().length > 0;
                }")
            ],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false],

        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('frontend', 'Username'),
            'mobile' => Yii::t('frontend', 'Mobile'),
            'password' => Yii::t('frontend', 'Password'),
            'password_confirm' => Yii::t('frontend', 'Confirm Password')
        ];
    }

    public function save()
    {
        $this->user->username = $this->username;
        $this->user->mobile = $this->mobile;
        if ($this->password) {
            $this->user->setPassword($this->password);
        }
        return $this->user->save();
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
