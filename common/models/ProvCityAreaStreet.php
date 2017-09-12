<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%prov_city_area_street}}".
 *
 * @property integer $id
 * @property string $code
 * @property string $parentId
 * @property string $name
 * @property integer $level
 */
class ProvCityAreaStreet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prov_city_area_street}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['code', 'parentId'], 'string', 'max' => 11],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'parentId' => 'Parent ID',
            'name' => 'Name',
            'level' => 'Level',
        ];
    }
}
