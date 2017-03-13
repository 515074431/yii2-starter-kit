<?php

namespace frontend\modules\api\v1\resources;

use common\models\UserProfile;
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

    public function extraFields()
    {
        return ['userProfile','user'];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['article_id', 'user_id', 'content'], 'required'],
            [['article_id', 'content'], 'required'],
            [['article_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string', 'max' => 1024],
        ];
    }

    /**
     * 获取用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    /**
     * 获取用户资料
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile(){
        return $this->hasOne(UserProfile::className(),['user_id'=>'user_id']);
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
