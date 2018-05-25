<?php

namespace api\modules\v1\controllers;

use api\modules\v1\resources\Article;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\rest\IndexAction;
use yii\rest\OptionsAction;
use yii\rest\Serializer;
use yii\rest\ViewAction;
use yii\web\HttpException;

/**
 * Class ArticleController
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ArticleController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\resources\Article';
    /**
     * @var array
     */
    public $serializer = [
        'class' => Serializer::class,
        'collectionEnvelope' => 'items'
    ];

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view' => [
                'class' => ViewAction::class,
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => OptionsAction::class
            ]
        ];
    }

    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $params = Yii::$app->getRequest()->getQueryParams();
        if(isset($params['fields'])) unset($params['fields']);
        if(isset($params['expand'])) unset($params['expand']);
        if(isset($params['page'])) unset($params['page']);
        if(isset($params['per-page'])) unset($params['per-page']);
        //var_dump($params);exit;
        return new ActiveDataProvider(array(
            'query' => Article::find()->where($params)->published()->orderBy(['published_at'=>SORT_DESC])//
        ));
    }

    /**
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     * @throws HttpException
     */
    public function findModel($id)
    {
        $model = Article::find()
            ->published()
            ->andWhere(['id' => (int)$id])
            ->one();
        if (!$model) {
            throw new HttpException(404);
        }
        return $model;
    }
}
