<?php
namespace api\modules\v1\models;

use cheatsheet\Time;
use common\commands\SendEmailCommand;
use common\models\Members;
use common\models\User;
use common\models\UserToken;
use frontend\modules\user\Module;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\Model;
use Yii;
use yii\helpers\Url;

use yii\web\ServerErrorHttpException;
/**
 * Signup form
 */
class SignupForm extends Model
{
    const SCENARIO_GENERATE = 'generate';
    /**
     * @var
     */
    public $username;
    /**
     * @var
     */
    public $mobile;
    /**
     * @var
     */
    public $password;
    /**
     * @var 手机验证码
     */
    public $code;
    /**
     * @var邀请码
     */
    public $invitation_code;
    /**
     * @var
     */
    public $smsid = 1;
    /**
     * @var 备注
     */
    public $remarks;

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
            ['username', 'filter', 'filter' => 'trim'],
            [['username','password','code', 'smsid'], 'required'],
            ['username', 'unique',
                'targetClass'=>'\common\models\User',
                'message' => Yii::t('frontend', 'This username has already been taken.')
            ],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'validateMobile'],// 验证手机号
            ['mobile', 'unique',
                'targetClass'=> '\common\models\User',
                'message' => Yii::t('frontend', 'This mobile address has already been taken.')
            ],

            ['password', 'string', 'min' => 6],
            ['code','safe','on'=>self::SCENARIO_GENERATE],

            //['code', 'checkCode'],
            ['code',  function ($attribute, $params) {
                //$smsType = 1;
                if(!\zc\yii2Alisms\Sms::checkCode($this->mobile,$this->code,$this->smsid)){
                    $this->addError('code','手机验证码不正确');
                    return false;
                }
            }],
            ['invitation_code','string','max'=>11],
            ['remarks','string','max'=>255]
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_GENERATE] = ['username','mobile','password'];
        return $scenarios;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username'=>Yii::t('frontend', 'Username'),
            'mobile'=>Yii::t('frontend', 'Mobile'),
            'password'=>Yii::t('frontend', 'Password'),
            'smsid'=>'SMS ID',
            'invitation_code'=>'邀请码',
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $shouldBeActivated = $this->shouldBeActivated();
            $user = new User();
            $user->username = $this->username;
            $user->mobile = $this->mobile;
            $user->invitation_code = $this->invitation_code;
            $user->status = $shouldBeActivated ? User::STATUS_NOT_ACTIVE : User::STATUS_ACTIVE;
            $user->remarks = $this->remarks;
            $user->setPassword($this->password);
            if(!$user->save()) {
                throw new Exception("User couldn't be  saved");
            }
            $profileData = ['province'=>11,'city'=>1101,'area'=>110101];
            $user->afterSignup($profileData);
            if ($shouldBeActivated) {
                $token = UserToken::create(
                    $user->id,
                    UserToken::TYPE_ACTIVATION,
                    Time::SECONDS_IN_A_DAY
                );
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('frontend', 'Activation mobile'),
                    'view' => 'activation',
                    'to' => $this->mobile,
                    'params' => [
                        'url' => Url::to(['/user/sign-in/activation', 'token' => $token->token], true)
                    ]
                ]));
            }
            return $user;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function shouldBeActivated()
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$userModule) {
            return false;
        } elseif ($userModule->shouldBeActivated) {
            return true;
        } else {
            return false;
        }
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
            //$user = User::findByMobile($this->mobile);
            //if(!$user){
                return true;
            //}else{
                $this->addError('mobile','手机已注册');
            //}
        }else{
            $this->addError('mobile','手机格式不正确');
        }
    }
}
