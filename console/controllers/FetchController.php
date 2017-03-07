<?php

namespace console\controllers;

use console\models\Article;
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

}
