<?php
namespace backend\modules\sale\models;

use common\modules\tender\models\frontend\UserFavoritesTenders;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * SaleValue
 */
class SaleValue extends \common\modules\sale\models\SaleValue
{

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SaleValue::find();
        $query->joinWith(['parent']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => empty($this->defaultOrder) ? ['defaultOrder' => ['status_on' => SORT_DESC]] : [],
            'pagination' => [
                'forcePageParam' => false,
                'pageSizeParam' => false,
                'pageSize' => !empty($settings['elementsPageAdmin']) ? $settings['elementsPageAdmin'] : 20
            ]
        ]);

        if (!$this->load($params)) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            self::tableName() . '.id' => $this->id,
            self::tableName() . '.model_name' => $this->model_name,
            self::tableName() . '.status_on' => $this->filterStatus,
            self::tableName() . '.model_id' => $this->model_id,
        ]);



        if ($this->created_at !== null) {
            $query->andFilterWhere(['between', self::tableName() . '.created_at', $this->created_at, $this->created_at + 3600 * 24]);
        }
        if ($this->updated_at !== null) {
            $query->andFilterWhere(['between', self::tableName() . '.updated_at', $this->updated_at, $this->updated_at + 3600 * 24]);
        }

        return $dataProvider;
    }

}