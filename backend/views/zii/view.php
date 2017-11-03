<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Zii */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ziis', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zii-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute'=>'redio',
                'format' => 'html',
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
            'created_at:datetime',
            'updated_at:datetime',
            [
                'attribute'=>'created_by',
                'value' => function($model){
                        return $model->createdBy->username;
                    }
            ],
            [
                'attribute'=>'updated_by',
                'value' => function($model){
                        return $model->updatedBy->username;
                    }
            ],
            [
                'attribute'=>'thumbnail',
                //'thumbnail_path:image',
                'format' => ['image',['width'=>'100','height'=>'100','title'=>$model->thumbnail_path]],
                'value'=> Yii::$app->glide->createSignedUrl([
                    'glide/index',
                    'path' => $model->thumbnail_path,
                    'w' => 200
                ], true),
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
                                'w' => 200
                            ], true)).'<br>';
                        }
                    }//var_dump($return);
                    return $return;
                }
            ],
        ],
    ]) ?>

</div>
