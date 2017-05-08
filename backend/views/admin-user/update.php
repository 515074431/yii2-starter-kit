<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AdminUser */
/* @var $id $id */
/* @var $roles yii\rbac\Role[] */

$this->title = Yii::t('backend', 'Update {modelClass}: ', ['modelClass' => 'AdminUser']) . ' ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Admin Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->mobile, 'url' => ['view', 'id' => $id]];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('backend', 'Update')];
?>
<div class="user-update">

    <?php echo $this->render('_form', [
        'model' => $model,
        'roles' => $roles
    ]) ?>

</div>
