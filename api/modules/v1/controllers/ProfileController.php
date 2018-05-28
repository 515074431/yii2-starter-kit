<?php

namespace api\modules\v1\controllers;

use common\models\User;
use api\modules\v1\models\ResetPayPassForm;
use api\modules\v1\resources\User as UserResource;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii;

use Intervention\Image\ImageManagerStatic;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ProfileController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\resources\User';
    /**
     * @var array
     */
    /*public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items'
    ];*/
    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                [
                    'class' => HttpBasicAuth::className(),
                    'auth' => function ($username, $password) {
                        $user = User::findByLogin($username);
                        return $user->validatePassword($password)
                            ? $user
                            : null;
                    }
                ],
                HttpBearerAuth::className(),
                QueryParamAuth::className()
            ]
        ];

        return $behaviors;
    }
    /**
     * @inheritdoc
     */
    /*protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'avatar-upload' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }*/
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            /*'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]*/
            'avatar-upload' => [
                'class' => UploadAction::className(),
                'deleteRoute' => 'avatar-delete',
                'on afterSave' => function ($event) {
                    /* @var $file \League\Flysystem\File */
                    $file = $event->file;//var_dump($event);exit;
                    $img = ImageManagerStatic::make($file->read())->fit(215, 215);
                    $file->put($img->encode());
                    //

                    $model = Yii::$app->user->identity->userProfile;

                    $model->avatar_path = $event->path;
                    $model->avatar_base_url = Yii::getAlias('@storageUrl').'/source/';//这里写死了，不太好
                    if ($model->save(false)) {
                        return $model;
                    }else{
                        return $model->errors;
                    }
                }
            ],
            'avatar-delete' => [
                'class' => DeleteAction::className()
            ]
        ];
    }

    /**
     * 用户中心，用户资料
     * @return array
     */
    public function actionIndex(){

        if(!\Yii::$app->user->isGuest){
            $user= \Yii::$app->getUser()->getIdentity();
            $userProfile = $user->userProfile;
            return [
                'id' => $user->id,
                'username' => $user->username,
                'mobile' => $user->mobile,
                'invitation_code' => $user->id,
                //'balance' => $user->balance,
                //'solid_balance' => $user->solid_balance,
                'avatar' => $userProfile->avatar,
                'profile' => $userProfile,
            ];
        }else{

        }

    }

    /**
     * 修改用户资料
     * @return string|\yii\web\Response
     */
    public function actionUpdate()
    {
        $model = Yii::$app->user->identity->userProfile;

        if ($model->load(Yii::$app->request->post(),'') && $model->save()) {
            return $model;
        }else{
            $response = Yii::$app->getResponse();
            $response->setStatusCode(401);
            return $model->errors;
        }
    }

    /**
     * 修改支付密码
     * @return array
     */
    public function actionPayPass(){
        $model = new ResetPayPassForm();
        if($model->load(Yii::$app->getRequest()->getBodyParams(),'') && $model->resetPayPass()){
            return ['修改支付密码成功'];
        }else{
            return $model->errors;
        }

    }
    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = UserResource::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
}
