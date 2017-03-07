<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article_comment}}".
 *
 * @property integer $id
 * @property integer $article_id
 * @property integer $user_id
 * @property string $content
 * @property integer $created_at
 * @property integer $updated_at
 */
class ArticleComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%article_comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_id', 'user_id', 'content', 'created_at', 'updated_at'], 'required'],
            [['article_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_id' => '文章ID',
            'user_id' => '用户ID',
            'content' => '内容',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}
