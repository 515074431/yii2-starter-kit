<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ArticleComment */

$this->title = 'Update Article Comment: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Article Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="article-comment-update">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
