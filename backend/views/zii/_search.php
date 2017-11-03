<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ZiiSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="zii-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'redio') ?>

    <?= $form->field($model, 'checkbox') ?>

    <?= $form->field($model, 'dropdown') ?>

    <?= $form->field($model, 'thumbnail_base_url') ?>

    <?php  echo $form->field($model, 'thumbnail_path') ?>

    <?php  echo $form->field($model, 'bierthday') ?>

    <?php  echo $form->field($model, 'created_at') ?>

    <?php  echo $form->field($model, 'updated_at') ?>

    <?php  echo $form->field($model, 'created_by') ?>

    <?php  echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
