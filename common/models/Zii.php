<?php

namespace common\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%zii}}".
 *
 * @property int $id
 * @property string $redio 单选
 * @property string $checkbox 多选
 * @property string $dropdown 下拉
 * @property string $thumbnail_base_url 头像基本路径
 * @property string $thumbnail_path 头像路径
 * @property string $bierthday 生日
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property ZiiAttachment[] $ziiAttachments
 */
class Zii extends \yii\db\ActiveRecord
{
    const REDIO_1 = '1';// aAaAaA
    const REDIO_2 = '2';// bBbBbB
    const REDIO_3 = '3';// cbcbcbcb
    const REDIO_4 = '4';// dbdbdbdb
    const REDIO_5 = '5';// ebebebebeb
    const CHECKBOX_A = 'a';// aAaAaA
    const CHECKBOX_B = 'b';// bBbBbB
    const CHECKBOX_C = 'c';// cbcbcbcb
    const CHECKBOX_D = 'd';// dbdbdbdb
    const CHECKBOX_E = 'e';// ebebebebeb
    const DROPDOWN_DAAAA = 'dAAAA';// daAaAaA
    const DROPDOWN_DBBBB = 'dBBBB';// dbBbBbB
    /**
     * @inheritdoc
     */
    public static function redioOptions()
    {
        return [
                self::REDIO_1 => 'aAaAaA',
                self::REDIO_2 => 'bBbBbB',
                self::REDIO_3 => 'cbcbcbcb',
                self::REDIO_4 => 'dbdbdbdb',
                self::REDIO_5 => 'ebebebebeb',
            ];
    }
    /**
     * @inheritdoc
     */
    public static function checkboxOptions()
    {
        return [
                self::CHECKBOX_A => 'aAaAaA',
                self::CHECKBOX_B => 'bBbBbB',
                self::CHECKBOX_C => 'cbcbcbcb',
                self::CHECKBOX_D => 'dbdbdbdb',
                self::CHECKBOX_E => 'ebebebebeb',
            ];
    }
    /**
     * @inheritdoc
     */
    public static function dropdownOptions()
    {
        return [
                self::DROPDOWN_DAAAA => 'daAaAaA',
                self::DROPDOWN_DBBBB => 'dbBbBbB',
            ];
    }

    /**
     * This method is invoked before validation starts.
     * The default implementation raises a `beforeValidate` event.
     * You may override this method to do preliminary checks before validation.
     * Make sure the parent implementation is invoked so that the event can be raised.
     * @return bool whether the validation should be executed. Defaults to true.
     * If false is returned, the validation will stop and the model is considered invalid.
     */
    public function beforeValidate()
    {
        if($this->checkbox && is_array($this->checkbox)) {
             $this->checkbox = join(',', $this->checkbox);
        }
        return parent::beforeValidate();
    }
    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
        $this->checkbox = explode(',',$this->checkbox);
        $this->trigger(parent::EVENT_AFTER_FIND);
    }


    /**
    * @var array
    * 上传图像     */
    public $thumbnail;
    /**
    * @var array
    * 上传图像 多张上传    */
    public $attachments;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                 'class' => TimestampBehavior::className(),
                 //'createdAtAttribute' => 'created_at',
                 'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_at',
                 ],
             ],
            [
                'class' => TimestampBehavior::className(),
                //'updatedAtAttribute' => 'updated_at',
                'attributes' => [
                    self::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
            ],
            [
                 'class' => BlameableBehavior::className(),
                 //'createdByAttribute' => 'created_by',
                 'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_by',
                 ],
             ],
            [
                 'class' => BlameableBehavior::className(),
                 //'updatedByAttribute' => 'updated_by',
                 'attributes' => [
                    self::EVENT_BEFORE_UPDATE => 'updated_by',
                 ],
             ],
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'thumbnail',
                'pathAttribute' => 'thumbnail_path',
                'baseUrlAttribute' => 'thumbnail_base_url',
            ],
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'attachments',
                'pathAttribute' => 'path',
                'baseUrlAttribute' => 'base_url',
                'multiple' => true,
                'uploadRelation' => 'ZiiAttachments',
                'orderAttribute' => 'order',
                'typeAttribute' => 'type',
                'sizeAttribute' => 'size',
                'nameAttribute' => 'name',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%zii}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['redio', 'checkbox'], 'safe'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['dropdown', 'bierthday'], 'string', 'max' => 45],
            [['thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['thumbnail','attachments',], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'redio' => '单选',
            'checkbox' => '多选',
            'dropdown' => '下拉',
            'thumbnail_base_url' => '头像基本路径',
            'thumbnail_path' => '头像路径',
            'bierthday' => '生日',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'created_by' => '创建者',
            'updated_by' => '更新者',
            'thumbnail' => '头像',
            'attachments' => '头像2',
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
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZiiAttachments()
    {
        return $this->hasMany(ZiiAttachment::className(), ['zii_id' => 'id'])->all();
    }

    /**
     * @inheritdoc
     * @return ZiiQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ZiiQuery(get_called_class());
    }

    /**
     * 数据表字段属性
     * @return array
     */
    public function columnOptions(){
        return '{"id":{"type":"","params":""},"redio":{"type":"radio","params":"[\r\n    {\r\n        \"key\": \"1\",\r\n        \"value\": \"aaaa\",\r\n        \"label\": \"aAaAaA\"\r\n    },\r\n    {\r\n        \"key\": \"2\",\r\n        \"value\": \"bbbb\",\r\n        \"label\": \"bBbBbB\"\r\n    },\r\n    {\r\n        \"key\": \"3\",\r\n        \"value\": \"cccc\",\r\n        \"label\": \"cbcbcbcb\"\r\n    },\r\n    {\r\n        \"key\": \"4\",\r\n        \"value\": \"dddd\",\r\n        \"label\": \"dbdbdbdb\"\r\n    },\r\n    {\r\n        \"key\": \"5\",\r\n        \"value\": \"eeee\",\r\n        \"label\": \"ebebebebeb\"\r\n    }\r\n]"},"checkbox":{"type":"checkbox","params":"[\r\n    {\r\n        \"key\": \"a\",\r\n        \"value\": \"aaaa\",\r\n        \"label\": \"aAaAaA\"\r\n    },\r\n    {\r\n        \"key\": \"b\",\r\n        \"value\": \"bbbb\",\r\n        \"label\": \"bBbBbB\"\r\n    },\r\n    {\r\n        \"key\": \"c\",\r\n        \"value\": \"cccc\",\r\n        \"label\": \"cbcbcbcb\"\r\n    },\r\n    {\r\n        \"key\": \"d\",\r\n        \"value\": \"dddd\",\r\n        \"label\": \"dbdbdbdb\"\r\n    },\r\n    {\r\n        \"key\": \"e\",\r\n        \"value\": \"eeee\",\r\n        \"label\": \"ebebebebeb\"\r\n    }\r\n]"},"dropdown":{"type":"dropDown","params":"[\r\n    {\r\n        \"key\": \"dAAAA\",\r\n        \"value\": \"daaaa\",\r\n        \"label\": \"daAaAaA\"\r\n    },\r\n    {\r\n        \"key\": \"dBBBB\",\r\n        \"value\": \"dbbbb\",\r\n        \"label\": \"dbBbBbB\"\r\n    }\r\n]"},"thumbnail_base_url":{"type":"hide","params":""},"thumbnail_path":{"type":"hide","params":""},"bierthday":{"type":"date","params":"{\r\n    \"format\": \"Y-m-d H:i:s\"\r\n}"},"created_at":{"type":"createdAt","params":""},"updated_at":{"type":"updatedAt","params":""},"created_by":{"type":"createdBy","params":"{\"attribute\":\"created_by\",\"table\":\"user\",\"target\":\"username\"}"},"updated_by":{"type":"updatedBy","params":"{\"attribute\":\"updated_by\",\"table\":\"user\",\"target\":\"username\"}"}}';
    }

    /**
     * 图像上传字段属性
     * @return array
     */
    public function imageOptions(){
        return '[
    {
        "attribute": "thumbnail",
        "label":"头像",
        "pathAttribute": "thumbnail_path",
        "baseUrlAttribute": "thumbnail_base_url"
    },
    {
        "attribute": "attachments",
        "label":"头像2",
        "multiple": true,
        "uploadRelation": "ZiiAttachment",
        "pathAttribute": "path",
        "baseUrlAttribute": "base_url",
        "orderAttribute": "order",
        "typeAttribute": "type",
        "sizeAttribute": "size",
        "nameAttribute": "name",
        "foreignKey":"zii_id"
    }
]';
    }
}