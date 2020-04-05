<?php
namespace backend\modules\sale\models;

use common\modules\settings\models\SiteSettings;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;


/**
 * Страницы
 */
class SaleElements extends \common\modules\sale\models\SaleElements
{


    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(
            parent::attributeLabels(),
            [
                'imageFile' => 'Картинка',
            ]
        );
    }

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SaleElements::find();
        $query->joinWith('parent');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'forcePageParam' => false,
                'pageSizeParam' => false,
                'pageSize' => !empty($settings['elementsPageAdmin']) ? $settings['elementsPageAdmin'] : 20
            ]
        ]);

        $params = !empty($params['SaleElements']) ? $params['SaleElements'] : $params;
        if (!($this->load($params, ''))) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            self::tableName() . '.id' => $this->id,
            self::tableName() . '.parent_id' => $this->parent_id,
            self::tableName() . '.status_on' => $this->status_on,

        ]);

        if ($this->created_at !== null) {
            $query->andFilterWhere(['between', self::tableName() . '.created_at', $this->created_at, $this->created_at + 3600 * 24]);
        }

        if ($this->updated_at !== null) {
            $query->andFilterWhere(['between', self::tableName() . '.updated_at', $this->updated_at, $this->updated_at + 3600 * 24]);
        }

        $query->andFilterWhere(
            ['like', self::tableName() . '.name', $this->name],
            ['like', self::tableName() . '.url', $this->url],
            ['like', self::tableName() . '.header', $this->header]

        );

        if (!empty($this->rubricName)) {
            $query->where(['like', SaleRubrics::tableName() . '.name', trim($this->rubricName) . '%', false]);
        }

        return $dataProvider;
    }
    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $this->date_start = !empty($this->date_start) ? strtotime($this->date_start) : null;
        $this->date_end = !empty($this->date_end) ? strtotime($this->date_end) : null;

        return parent::beforeValidate();
    }

}