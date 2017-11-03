<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Zii */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="zii-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'redio')->radioList(common\models\Zii::redioOptions()) ?>

    <?= $form->field($model, 'checkbox')->checkboxList(common\models\Zii::checkboxOptions()) ?>

    <?= $form->field($model, 'dropdown')->dropDownList(common\models\Zii::dropdownOptions(), ['prompt' => '请选择']) ?>

    <?php echo $form->field($model, 'thumbnail')->widget(
    trntv\filekit\widget\Upload::className(),
    [
        'url' => ['/file-storage/upload'],
        'maxFileSize' => 5000000, // 5 MiB
    ]);
    ?>

    <?php echo $form->field($model, 'attachments')->widget(
        trntv\filekit\widget\Upload::className(),
        [
            'url' => ['/file-storage/upload'],
            'sortable' => true,
            'maxFileSize' => 10000000, // 10 MiB
            'maxNumberOfFiles' => 10
        ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
