<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ZiiSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ziis';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zii-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Zii', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute'=>'redio',
                'format' => 'html',
                'filter' => common\models\Zii::redioOptions(),
                'value' => function($model){
                    $return = '';
                    if(!empty($model->redio)){
                        $options = common\models\Zii::redioOptions();
                        $return = Html::label($options[$model->redio]);
                    }
                    return $return;
                }
            ],
            [
                'attribute'=>'checkbox',
                'format' => 'html',
                'filter' => common\models\Zii::checkboxOptions(),
                'value' => function($model){
                    $return = '';
                    if(!empty($model->checkbox)){
                        $options = common\models\Zii::checkboxOptions();
                        foreach ($model->checkbox as $value){
                            $return .= ' '.Html::label($options[$value]);
                        }
                    }
                    return $return;
                }
            ],
            [
                'attribute'=>'dropdown',
                'format' => 'html',
                'filter' => common\models\Zii::dropdownOptions(),
                'value' => function($model){
                    $return = '';
                    if(!empty($model->dropdown)){
                        $options = common\models\Zii::dropdownOptions();
                        $return = Html::label($options[$model->dropdown]);
                    }
                    return $return;
                }
            ],
            'bierthday',
            [
                'attribute' => 'created_at',
                'format' => ['date', "php:Y-m-d H:i:s"],
                'headerOptions' => ['width' => '12%'],
                'filter' => kartik\daterange\DateRangePicker::widget([
                    'name' => 'ZiiSearch[createdAt]',
                    'value' => Yii::$app->request->get('ZiiSearch')['createdAt'],
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => '/',
                        ]
                    ]
                ])
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', "php:Y-m-d H:i:s"],
                'headerOptions' => ['width' => '12%'],
                'filter' => kartik\daterange\DateRangePicker::widget([
                    'name' => 'ZiiSearch[updatedAt]',
                    'value' => Yii::$app->request->get('ZiiSearch')['updatedAt'],
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => '/',
                        ]
                    ]
                ])
            ],
            [
                'attribute' => 'created_by_username',
                 'label' => '创建者',
                'value' => 'createdBy.username'
            ],
            [
                'attribute' => 'updated_by_username',
                 'label' => '更新者',
                'value' => 'updatedBy.username'
            ],
            [
                'attribute'=>'thumbnail',
                //'thumbnail_path:image',
                'format' => ['image',['width'=>'50','height'=>'50','title'=>$model->thumbnail_path]],
                'value'=> function($model){
                        return Yii::$app->glide->createSignedUrl([
                                    'glide/index',
                                    'path' => $model->thumbnail_path,
                                    'w' => 50
                                    ], true);
                    }
            ],
            [
                'attribute'=>'attachments',
                //'thumbnail_path:image',
                'format' => 'html',
                'value'=> function($model) {
                    $return = '';
                    $foreignKeys = $model->getZiiAttachments();
                    if( $foreignKeys) {
                        foreach ($foreignKeys as $item) {
                            $return .= Html::img(Yii::$app->glide->createSignedUrl([
                                                'glide/index',
                                                'path' => $item->path,
                                                'w' => 50
                                                ], true)).'<br>';
                            }
                     }
                    return $return;
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
