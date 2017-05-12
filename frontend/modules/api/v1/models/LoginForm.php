<?php
namespace frontend\modules\api\v1\models;

use cheatsheet\Time;
use common\models\User;
use common\models\UserToken;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $identity;
    public $password;
    public $rememberMe = true;

    private $user = false;

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
            // username and password are both required
            [['identity', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'identity'=>Yii::t('frontend', 'Username or mobile'),
            'password'=>Yii::t('frontend', 'Password'),
            'rememberMe'=>Yii::t('frontend', 'Remember Me'),
        ];
    }


    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', Yii::t('frontend', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            /*if (Yii::$app->user->login($this->getUser(), $this->rememberMe ? Time::SECONDS_IN_A_MONTH : 0)) {
                return true;
            }*/
            $user = $this->getUser();
            $user->setToken(UserToken::TYPE_ACTIVATION);
            return $user;
        }else{
            $response = Yii::$app->getResponse();
            $response->setStatusCode(401);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->user === false) {
            $this->user = User::find()
                ->active()
                ->andWhere(['or', ['username'=>$this->identity], ['mobile'=>$this->identity]])
                ->one();
        }

        return $this->user;
    }
}
