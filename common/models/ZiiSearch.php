<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Zii;

/**
 * ZiiSearch represents the model behind the search form of `common\models\Zii`.
 */
class ZiiSearch extends Zii
{
    public $createdAt;
    public $updatedAt;
    public $created_by_username;
    public $updated_by_username;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['redio', 'checkbox', 'dropdown', 'thumbnail_base_url', 'thumbnail_path', 'bierthday'], 'safe'],
            [['createdAt','updatedAt','created_by_username','updated_by_username',], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Zii::find();
        $query->joinWith(['createdBy createdByUser']);
        $query->joinWith(['updatedBy updatedByUser']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->sort->attributes['created_by_username'] = [
            'asc' => ['createdByUser.username' => SORT_ASC],
            'desc' => ['createdByUser.username' => SORT_DESC],
            'label' => '创建者'
        ];
                    $dataProvider->sort->attributes['updated_by_username'] = [
            'asc' => ['updatedByUser.username' => SORT_ASC],
            'desc' => ['updatedByUser.username' => SORT_DESC],
            'label' => '更新者'
        ];
                    $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'redio', $this->redio])
            ->andFilterWhere(['like', 'checkbox', $this->checkbox])
            ->andFilterWhere(['like', 'dropdown', $this->dropdown])
            ->andFilterWhere(['like', 'thumbnail_base_url', $this->thumbnail_base_url])
            ->andFilterWhere(['like', 'thumbnail_path', $this->thumbnail_path])
            ->andFilterWhere(['like', 'bierthday', $this->bierthday]);

        if (!empty($this->createdAt)) {
            $query->andFilterCompare(static::tableName().'.created_at', strtotime(explode('/', $this->createdAt)[0]), '>=');//起始时间
            $query->andFilterCompare(static::tableName().'.created_at', (strtotime(explode('/', $this->createdAt)[1]) + 86400), '<');//结束时间
        }


        if (!empty($this->updatedAt)) {
            $query->andFilterCompare(static::tableName().'.updated_at', strtotime(explode('/', $this->updatedAt)[0]), '>=');//起始时间
            $query->andFilterCompare(static::tableName().'.updated_at', (strtotime(explode('/', $this->updatedAt)[1]) + 86400), '<');//结束时间
        }

            $query->andFilterWhere(['like', 'createdByUser.username', $this->created_by_username]) ;//<=====加入这句
            $query->andFilterWhere(['like', 'updatedByUser.username', $this->updated_by_username]) ;//<=====加入这句
        return $dataProvider;
    }
}
