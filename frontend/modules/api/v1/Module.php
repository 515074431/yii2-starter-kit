<?php

namespace frontend\modules\api\v1;

use Yii;

class Module extends \frontend\modules\api\Module
{
    public $controllerNamespace = 'frontend\modules\api\v1\controllers';

    public function init()
    {
        parent::init();
        Yii::$app->user->identityClass = 'frontend\modules\api\v1\models\ApiUserIdentity';
        Yii::$app->user->enableSession = false;
        Yii::$app->user->loginUrl = null;
        Yii::$app->response->on('beforeSend',function ($event) {
            $response = $event->sender;
            if ($response->data !== null ) {
                $response->data = [
                    'status' => $response->isSuccessful,
                    'data' => $response->data,
                ];
                $response->statusCode = 200;
            }else{
                $response->data = [
                    'status' => false,
                    'data' => $response->data,
                ];
                $response->statusCode = 200;
            }
        });
    }
}
