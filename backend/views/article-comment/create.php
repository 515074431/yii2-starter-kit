<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ArticleComment */

$this->title = 'Create Article Comment';
$this->params['breadcrumbs'][] = ['label' => 'Article Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-comment-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
