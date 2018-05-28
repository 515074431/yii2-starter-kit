<?php
return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        // Api
        ['class' => 'yii\rest\UrlRule', 'controller' => 'v1/article', 'only' => ['index', 'view', 'options']],
        // Api
        ['class' => 'yii\rest\UrlRule', 'controller' => 'v1/article-comment', 'only' => ['index','create', 'view', 'options']],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'v1/user', 'only' => ['index', 'view', 'options']],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'v1/profile', 'only' => ['index', 'update', 'avatar-upload']],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'v1/prov-city-area-street', 'only' => ['index','create', 'view']]
    ]
];
