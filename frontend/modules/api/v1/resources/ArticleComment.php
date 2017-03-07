<?php

namespace frontend\modules\api\v1\resources;

use yii\helpers\Url;
use yii\web\Linkable;
use yii\web\Link;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ArticleComment extends \common\models\ArticleComment implements Linkable
{
    public function fields()
    {
        return ['id','article_id','user_id',  'content', 'created_at'];
    }


    /**
     * Returns a list of links.
     *
     * @return array the links
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['article-comment/view', 'id' => $this->id], true)
        ];
    }
}
