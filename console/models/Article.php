<?php

namespace console\models;

use common\models\query\ArticleQuery;
use phpDocumentor\Reflection\Types\Self_;
use trntv\filekit\behaviors\UploadBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use yii\helpers\Console;
use yii\httpclient\Client;

use common\models\Article as CommonArticle;
/**
 * This is the model class for table "article".
 *
 */
class Article extends CommonArticle
{
    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 0;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'immutable' => true
            ],
        ];
    }

    /**
     * 抓取懂球帝的新闻
     * @param $thirdId 分类
     * @param $categoryId 分类
     */
    public static function FindNewsFromDongQiuDi($thirdId,$categoryId){
    //http://api.dongqiudi.com/app/tabs/iphone/58.json
    $client = new Client(['baseUrl' => 'http://api.dongqiudi.com']);
    $res = $client->get("app/tabs/iphone/$thirdId.json")->send();
    $datas = $res->data['articles'];
    $i=0;
    foreach ($datas as $n){
        $news = static::findOne(['id_third'=>$n['id']]);
        if($news !== null){
            continue;
        }
        else{
            $news = new self();
            $i++;
        }
        $news->category_id = $categoryId;
        $news->status =  self::STATUS_PUBLISHED;
        $news->id_third = $n['id'];
        $thumb = parse_url($n['thumb']);
        $news->thumbnail_base_url = $thumb['scheme'].'://'.$thumb['host'];
        $news->thumbnail_path = $thumb['path'];
        $news->title = $n['title'];
        $news->source = 'dongqiudi';
        //$news->published_at = time();
        $news->summary = $n['title'];
        $news->body = $client->get('article/'.$news->id_third.'.html')->send()->content;
        include_once __DIR__."/../../common/lib/simple_html_dom.php";
        $html = new \simple_html_dom();
        $html->load($news->body);
        $articles = $html->find('article');
        $news->body = (string)$articles[0];
        $news->body  = str_replace('data-src','src',$news->body);
        $news->body  = str_replace('更多靠谱推荐，请戳','',$news->body);
        $news->body  = str_replace('【足球彩票圈】','',$news->body);




        /*$news->body = '<meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0">
    <!-- <meta content="width=640, user-scalable=no, target-densitydpi=device-dpi" name="viewport"> -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
    <meta http-equiv="expires" content="0">
'.$news->body;*/
        $re = $news->save();
        if(!$re){
            Console::output(json_encode($news->errors));
        }
    }
        Console::output("抓取了 $i 条数据");
}



}
