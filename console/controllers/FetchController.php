<?php

namespace console\controllers;

use common\models\Article;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;



/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class FetchController extends Controller
{

    public function actionArticle(){
        Article::FindNewsFromDongQiuDi();
    }
    public function actionSetup()
    {
        $this->runAction('set-writable', ['interactive' => $this->interactive]);
        $this->runAction('set-executable', ['interactive' => $this->interactive]);
        $this->runAction('set-keys', ['interactive' => $this->interactive]);
        \Yii::$app->runAction('migrate/up', ['interactive' => $this->interactive]);
        \Yii::$app->runAction('rbac-migrate/up', ['interactive' => $this->interactive]);
    }



    public function setWritable($paths)
    {
        foreach ($paths as $writable) {
            $writable = Yii::getAlias($writable);
            Console::output("Setting writable: {$writable}");
            @chmod($writable, 0777);
        }
    }

}
