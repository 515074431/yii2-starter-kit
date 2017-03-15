<?php
namespace frontend\modules\api\v1\controllers;

use Yii;
use frontend\modules\api\v1\resources\ArticleComment;
use common\models\ArticleComment as ArticleCommentBase;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\rest\ActiveController;
use yii\rest\Serializer;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

use common\models\User;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
/**
 * Class ArticleController
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ArticleCommentController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'frontend\modules\api\v1\resources\ArticleComment';
    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        /*$behaviors['authenticator'] = [
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
                QueryParamAuth::className(),
            ]
        ];*/


        return $behaviors;
    }
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]
        ];
    }
    public function actionIndex(){

        $articleComents = ArticleCommentBase::find()->where(['article_id'=>Yii::$app->getRequest()->get('article_id')])->orderBy(['created_at'=>SORT_DESC])->all();
        $return = [];
        foreach ($articleComents as $articleComent) {
            $tmp =$articleComent->toArray();
            if($articleComent->userProfile != null){
                $tmp['username'] = $articleComent->user->username;
                $tmp['avatar'] = $articleComent->userProfile->avatar_base_url. $articleComent->userProfile->avatar_path;
            }else{
                $tmp['username'] = null;
                $tmp['avatar'] = null;
            }
            $return [] = $tmp;
        }
        return $return;
    }

    public function actionCreate(){
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass();

        $params = Yii::$app->getRequest()->getBodyParams();
        $access_token = Yii::$app->getRequest()->get('access-token');
        if($access_token){
            $user = User::find()
                ->active()
                ->andWhere(['access_token' => $access_token])
                ->one();
            if($user){
                $params['user_id'] = $user->getId();
            }

        }

        $model->load($params, '');
        if ($model->save()) {
            return $model;
        } elseif ($model->hasErrors()) {
            return $model->errors;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
    }

    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        return new ActiveDataProvider(array(
            'query' => ArticleComment::find()->where(['article_id'=>Yii::$app->getRequest()->get('article_id')])
        ));
    }

    /**
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     * @throws HttpException
     */
    public function findModel($id)
    {
        $model = ArticleComment::find()
            ->andWhere(['id' => (int) $id])
            ->one();
        if (!$model) {
            throw new HttpException(404);
        }
        return $model;
    }
}
