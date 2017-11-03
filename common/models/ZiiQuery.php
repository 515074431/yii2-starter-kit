<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Zii]].
 *
 * @see Zii
 */
class ZiiQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Zii[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Zii|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
