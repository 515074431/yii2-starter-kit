<?php
/* @var $this yii\web\View */
/* @var $model backend\models\AdminUserForm */
/* @var $roles yii\rbac\Role[] */
$this->title = Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => 'Admin User',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Admin Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <?php echo $this->render('_form', [
        'model' => $model,
        'roles' => $roles
    ]) ?>

</div>
