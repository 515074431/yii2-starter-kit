<?php

namespace api\modules\v1\controllers;

use common\commands\SendEmailCommand;
use common\models\User;
use common\models\UserToken;
use api\modules\v1\models\GetCode;
use api\modules\v1\models\LoginForm;
use api\modules\v1\models\PasswordResetRequestForm;
use api\modules\v1\models\PasswordResetRequestFormByMobile;
use api\modules\v1\models\ResetPasswordForm;
use api\modules\v1\models\SignupForm;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

use yii\web\ServerErrorHttpException;

use yii\rest\ActiveController;
/**
 * Class SignInController
 * @package frontend\modules\user\controllers
 * @author Eugene Terentev <eugene@terentev.net>
 */
class SignInController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\resources\User';


    /**
     * @return array
     */
    public function actions()
    {
        return [
            'oauth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successOAuthCallback']
            ]
        ];
    }

    /**
     * @return array
     */
    /*public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'signup', 'login', 'request-password-reset', 'reset-password', 'oauth', 'activation'
                        ],
                        'allow' => true,
                        'roles' => ['?']
                    ],
                    [
                        'actions' => [
                            'signup', 'login', 'request-password-reset', 'reset-password', 'oauth', 'activation'
                        ],
                        'allow' => false,
                        'roles' => ['@'],
                        'denyCallback' => function () {
                            return Yii::$app->controller->redirect(['/user/default/index']);
                        }
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post']
                ]
            ]
        ];
    }*/
    /*调测成功结束*/
    /**
     * @param $mobile
     * @return array|mixed
     */
    public function actionGetCode($mobile){
        $model = new GetCode();
        if($model->load(['mobile'=>$mobile],'') && $model->validate()){
            $type = Yii::$app->request->get('type');
            if($type=="register"){
                $user = User::findByMobile($mobile);
                if($user!==null) {
                    return Yii::$app->params['error'][PHONE_REGISTER_YES];
                }
            }

            return $model->sendCode();
        }else{
            return $model->errors;
        }
    }
    /**
     * @return array|string|Response
     */
    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post(),'') ) {
            $user = $model->login();
            if($user){
                return [
                    'id' => $user->id,
                    'access_token' => $user->access_token,
                ];
            }else{
                return $model->errors;
            }
        } else {
            return $this->render('login', [
                'model' => $model
            ]);
        }
    }

    /**
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * 用户注册
     * @return string|Response
     */
    public function actionSignup()
    {
//        $key = "SMS_1_15618769991";
//        $cache = \Yii::$app->cache;
//        $cacheCode = $cache->get($key);
        //$cache = \Yii::$app->cache;
        //var_dump([1 ,15618769991, $cacheCode,$cache]);exit;

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post(),'')) {
            $user = $model->signup();
            if ($user) {
                return [
                    'id' => $user->id,
                    'access_token' => $user->access_token,
                ];
            }else{
                return $model->errors;
            }
        }else{
            throw new BadRequestHttpException('错误请求');
        }
    }

    /**
     * 用户激活
     * @param $token
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionActivation($token)
    {
        $token = UserToken::find()
            ->byType(UserToken::TYPE_ACTIVATION)
            ->byToken($token)
            ->notExpired()
            ->one();

        if (!$token) {
            throw new BadRequestHttpException;
        }

        $user = $token->user;
        $user->updateAttributes([
            'status' => User::STATUS_ACTIVE
        ]);
        $token->delete();
        return [
            'id' => $user->id,
            'access_token' => $user->access_token,
        ];
    }

    /**
     * 修改密码请求验证码
     * @return string|Response
     */
    public function actionRequestPasswordReset($mobile)
    {
        $model = new PasswordResetRequestFormByMobile();
        if ($model->load(['mobile'=>$mobile],'') && $model->validate()) {//校验成功
            $validateCode = $model->sendValidateCode();
            if ($validateCode) {
                return $validateCode;
            } else {
                return $model->errors;
            }
        }else{
           return $model->errors;
        }
    }

    /**
     * 修改密码 根据验证码重置密码
     * @param $token
     * @return string|Response
     * @throws BadRequestHttpException
     */
    public function actionResetPassword()
    {
        $model = new ResetPasswordForm();


        if ($model->load(Yii::$app->request->post(),'') && $model->validate() ) {
            $user =  $model->resetPassword();
            if($user){
                return ['msg'=>'修改成功，请重新登录。'];
                return [
                    'id' => $user->id,
                    'access_token' => $user->access_token,
                ];
            }else{
                return $model->errors;
            }

        }else{
            return $model->errors;
        }

    }

    /**
     * @param $client \yii\authclient\BaseClient
     * @return bool
     * @throws Exception
     */
    public function successOAuthCallback($client)
    {
        // use BaseClient::normalizeUserAttributeMap to provide consistency for user attribute`s names
        $attributes = $client->getUserAttributes();
        $user = User::find()->where([
                'oauth_client'=>$client->getName(),
                'oauth_client_user_id'=>ArrayHelper::getValue($attributes, 'id')
            ])
            ->one();
        if (!$user) {
            $user = new User();
            $user->scenario = 'oauth_create';
            $user->username = ArrayHelper::getValue($attributes, 'login');
            $user->email = ArrayHelper::getValue($attributes, 'email');
            $user->oauth_client = $client->getName();
            $user->oauth_client_user_id = ArrayHelper::getValue($attributes, 'id');
            $password = Yii::$app->security->generateRandomString(8);
            $user->setPassword($password);
            if ($user->save()) {
                $profileData = [];
                if ($client->getName() === 'facebook') {
                    $profileData['firstname'] = ArrayHelper::getValue($attributes, 'first_name');
                    $profileData['lastname'] = ArrayHelper::getValue($attributes, 'last_name');
                }
                $user->afterSignup($profileData);
                $sentSuccess = Yii::$app->commandBus->handle(new SendEmailCommand([
                    'view' => 'oauth_welcome',
                    'params' => ['user'=>$user, 'password'=>$password],
                    'subject' => Yii::t('frontend', '{app-name} | Your login information', ['app-name'=>Yii::$app->name]),
                    'to' => $user->email
                ]));
                if ($sentSuccess) {
                    Yii::$app->session->setFlash(
                        'alert',
                        [
                            'options'=>['class'=>'alert-success'],
                            'body'=>Yii::t('frontend', 'Welcome to {app-name}. Email with your login information was sent to your email.', [
                                'app-name'=>Yii::$app->name
                            ])
                        ]
                    );
                }

            } else {
                // We already have a user with this email. Do what you want in such case
                if ($user->email && User::find()->where(['email'=>$user->email])->count()) {
                    Yii::$app->session->setFlash(
                        'alert',
                        [
                            'options'=>['class'=>'alert-danger'],
                            'body'=>Yii::t('frontend', 'We already have a user with email {email}', [
                                'email'=>$user->email
                            ])
                        ]
                    );
                } else {
                    Yii::$app->session->setFlash(
                        'alert',
                        [
                            'options'=>['class'=>'alert-danger'],
                            'body'=>Yii::t('frontend', 'Error while oauth process.')
                        ]
                    );
                }

            };
        }
        if (Yii::$app->user->login($user, 3600 * 24 * 30)) {
            return true;
        } else {
            throw new Exception('OAuth error');
        }
    }

    /**
     * 用户信息
     * @return string|Response
     */
    public function actionProfile()
    {
        $model = Yii::$app->user->identity->userProfile;
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options'=>['class'=>'alert-success'],
                'body'=>Yii::t('backend', 'Your profile has been successfully saved', [], $model->locale)
            ]);
            return $this->refresh();
        }
        return $this->render('profile', ['model'=>$model]);
    }
}
