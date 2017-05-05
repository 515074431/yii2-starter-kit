<?php

namespace common\models;

use Yii;
use raoul2000\workflow\events\WorkflowEvent;

//Yii::setAlias('@workflowDefinitionNamespace','common\workflow');
/**
 * This is the model class for table "{{%post}}".
 *
 * @property integer $id
 * @property string $slug
 * @property string $title
 * @property string $body
 * @property string $view
 * @property integer $category_id
 * @property string $thumbnail_base_url
 * @property string $thumbnail_path
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $published_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $createdBy
 * @property ArticleCategory $category
 * @property User $updatedBy
 */
class Post extends \yii\db\ActiveRecord
{
//    const STATUS_DRAFT=1;
//    const STATUS_CORRECTION=2;
//    const STATUS_READY=3;
//    const STATUS_PUBLISHED=4;
//    const STATUS_ARCHIVED=5;

    const STATUS_DRAFT='draft';
    const STATUS_CORRECTION='correction';
    const STATUS_READY='ready';
    const STATUS_PUBLISHED='published';
    const STATUS_ARCHIVED='archived';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post}}';
    }

    public function behaviors()
    {
        return [
            //\raoul2000\workflow\base\SimpleWorkflowBehavior::className()
            'simpleWorkflow' => [
              'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
              //'statusAttribute' => 'col_status',
              'defaultWorkflowId' => 'post',
              //'source' => 'myWorkflowSource',
          ],
        ];
    }

    public function init()
    {
        $this->on(
            WorkflowEvent::afterChangeStatus('post/draft', 'post/correction'),
            [$this, 'sendMail']
        );
        /*$this->on(
            WorkflowEvent::afterEnterStatus('PostWorkflow/correction'),
            [$this, 'sendMail']
        );
        $this->on(
            WorkflowEvent::beforeEnterStatus('PostWorkflow/published'),
            function ($event) {
                $event->isValid = \Yii::$app->user->can('chief.editor');
            }
        );*/
    }

    public function sendMail($event)
    {
        echo    'The post [' . $event->sender->owner->title . '] is ready to be corrected.';
        return;
        MailingService::sendMailToCorrector(
            'A Post is ready for correction',
            'The post [' . $event->sender->owner->title . '] is ready to be corrected.'
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug', 'title', 'body'], 'required'],
            [['body'], 'string'],
            [['category_id',  'created_by', 'updated_by', 'published_at', 'created_at', 'updated_at'], 'integer'],
            [['slug', 'thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
            [['title'], 'string', 'max' => 512],
            [['view'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 40],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArticleCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slug' => 'Slug',
            'title' => 'Title',
            'body' => 'Body',
            'view' => 'View',
            'category_id' => 'Category ID',
            'thumbnail_base_url' => 'Thumbnail Base Url',
            'thumbnail_path' => 'Thumbnail Path',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'published_at' => 'Published At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ArticleCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\PostQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\PostQuery(get_called_class());
    }
}
