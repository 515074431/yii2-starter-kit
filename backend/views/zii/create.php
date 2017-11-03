<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Zii */

$this->title = 'Create Zii';
$this->params['breadcrumbs'][] = ['label' => 'Ziis', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zii-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
