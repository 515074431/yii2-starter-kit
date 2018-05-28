<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\ProvCityAreaStreet;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class ProvCityAreaStreetController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\ProvCityAreaStreet';

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [];
     }

    public function actionIndex($parentId=1){
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $modelClass::find()->where(['parentId'=>$parentId]),
        ]);
    }
}
