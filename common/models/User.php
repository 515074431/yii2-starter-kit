<?php
namespace common\models;

use cheatsheet\Time;
use common\commands\AddToTimelineCommand;
use common\models\query\UserQuery;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $pay_pass
 * @property string $mobile
 * @property string $email
 * @property string $auth_key
 * @property string $access_token
 * @property string $oauth_client
 * @property string $oauth_client_user_id
 * @property string $publicIdentity
 * @property integer $status
 * @property integer $balance
 * @property integer $solid_balance
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $logged_at
 * @property string $password write-only password
 *
 * @property \common\models\UserProfile $userProfile
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_NOT_ACTIVE = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_DELETED = 3;

    const ROLE_USER = 'user';
    const ROLE_MANAGER = 'manager';
    const ROLE_ADMINISTRATOR = 'administrator';

    const EVENT_AFTER_SIGNUP = 'afterSignup';
    const EVENT_AFTER_LOGIN = 'afterLogin';

    public $token = null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'auth_key' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key'
                ],
                'value' => Yii::$app->getSecurity()->generateRandomString()
            ],
            'access_token' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'access_token'
                ],
                'value' => $this->generateAccessToken()/*function () {
                    return Yii::$app->getSecurity()->generateRandomString(40);
                }*/
            ]
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return ArrayHelper::merge(
            parent::scenarios(),
            [
                'oauth_create' => [
                    'oauth_client', 'oauth_client_user_id', 'mobile', 'username', '!status'
                ]
            ]
        );
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'mobile', 'email'], 'unique'],
            ['status', 'default', 'value' => self::STATUS_NOT_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::statuses())],
            [['username'], 'filter', 'filter' => '\yii\helpers\Html::encode']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('common', 'Username'),
            'email' => Yii::t('common', 'E-mail'),
            'mobile' => Yii::t('common', 'Mobile'),
            'status' => Yii::t('common', 'Status'),
            'access_token' => Yii::t('common', 'API access token'),
            'invitation_code' => 'invitation_code',
            'created_at' => Yii::t('common', 'Created at'),
            'updated_at' => Yii::t('common', 'Updated at'),
            'logged_at' => Yii::t('common', 'Last login'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }
    /**
     * 生成 api_token
     */
    public function generateAccessToken()
    {
        //return $this->access_token = Yii::$app->security->generateRandomString(29) . '_' . time();
        return $this->access_token = Yii::$app->security->generateRandomString(40) ;
    }

    /**
     * 获得userToken对象
     * @param $token
     * @param $type
     * @return array|null|ActiveRecord
     */
    public static function userToken($token,$type){
        return UserToken::find()->where(['token'=>$token,'type'=>$type])->andWhere(['>', 'expire_at', time()])->one();
    }

    /**
     * 设置token
     * @param string $type
     */
    public function setToken($type=UserToken::TYPE_ACTIVATION){
        //生成用户token
        $token = UserToken::create(
            $this->id,
            $type,
            Time::SECONDS_IN_A_DAY
        );
        $this->token = $token->__toString();
    }

    /**
     * 校验api_token是否有效
     * @param $token
     * @param int $type 类型
     * @return bool
     */
    public static function accessTokenIsValid($token)
    {
        $type=Yii::$app->request->get('type',UserToken::TYPE_ACTIVATION);
        if (empty($token)) {
            return false;
        }
        $userToken = self::userToken($token,$type);
        return !is_null($userToken);
    }
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {

        $type=Yii::$app->request->get('type',UserToken::TYPE_ACTIVATION);
        // 如果token无效的话，
        if(!static::accessTokenIsValid($token)) {
            throw new \yii\web\UnauthorizedHttpException("token is invalid.");
        }
        $userToken = static::userToken($token,$type);
        return static::findOne($userToken->user_id);
        //return static::userToken($token,$type)->getUser();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->active()
            ->andWhere(['username' => $username])
            ->one();
    }

    /**
     * Finds user by mobile
     *
     * @param string $mobile
     * @return static|null
     */
    public static function findByMobile($mobile)
    {
        return static::find()
            ->active()
            ->andWhere(['mobile' => $mobile])
            ->one();
    }

    /**
     * Finds user by username or mobile or email
     *
     * @param string $login
     * @return static|null
     */
    public static function findByLogin($login)
    {
        return static::find()
            ->active()
            ->andWhere(['or', ['username' => $login], ['mobile' => $login], ['email' => $login]])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
        $this->generateAccessToken();

    }
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function reSetPassword($password)
    {
        $this->password = $password;
        //修改所有token
        //To Do
        UserToken::resetExpire($this->id);

    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPayPass($password)
    {
        $this->pay_pass = Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * Returns user statuses list
     * @return array|mixed
     */
    public static function statuses()
    {
        return [
            self::STATUS_NOT_ACTIVE => Yii::t('common', 'Not Active'),
            self::STATUS_ACTIVE => Yii::t('common', 'Active'),
            self::STATUS_DELETED => Yii::t('common', 'Deleted')
        ];
    }

    /**
     * Creates user profile and application event
     * @param array $profileData
     */
    public function afterSignup(array $profileData = [])
    {
        $this->refresh();
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'user',
            'event' => 'signup',
            'data' => [
                'public_identity' => $this->getPublicIdentity(),
                'user_id' => $this->getId(),
                'created_at' => $this->created_at
            ]
        ]));
        $profile = new UserProfile();
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $this->link('userProfile', $profile);

        //生成用户token
        $this->setToken(UserToken::TYPE_ACTIVATION);

        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        //$auth = Yii::$app->authManager;;
        //$auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
    }

    /**
     * @return string
     */
    public function getPublicIdentity()
    {
        if ($this->userProfile && $this->userProfile->getFullname()) {
            return $this->userProfile->getFullname();
        }
        if ($this->username) {
            return $this->username;
        }
        if ($this->email) {
            return $this->email;
        }
        return $this->mobile;
    }
}
