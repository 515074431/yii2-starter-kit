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
        Article::FindNewsFromDongQiuDi(1,1);//
        Article::FindNewsFromDongQiuDi(104,2);
        Article::FindNewsFromDongQiuDi(56,3);
        Article::FindNewsFromDongQiuDi(58,4);
        Article::FindNewsFromDongQiuDi(4,5);
        Article::FindNewsFromDongQiuDi(6,6);
        Article::FindNewsFromDongQiuDi(68,7);
        Article::FindNewsFromDongQiuDi(37,8);
    }

}
