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
class SaleRubrics extends \common\modules\sale\models\SaleRubrics
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
        $query = SaleRubrics::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['lft' => SORT_ASC]]
        ]);

        if (!($this->load($params))) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status_on' => $this->status_on,
        ]);

        if ($this->created_at !== null) {
            $query->andFilterWhere(['between', 'created_at', $this->created_at, $this->created_at + 3600 * 24]);
        }

        if ($this->updated_at !== null) {
            $query->andFilterWhere(['between', 'updated_at', $this->updated_at, $this->updated_at + 3600 * 24]);
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

    /**
     * Возвращает массив дерева для выпадающего списка отформатированый в человеко-понятном виде иерархии
     *
     * @return array
     */
    public static function getTreeArray()
    {
        $model = new self();
        $root = $model::getRoot();
        $rubrics = $model::getChildrenTree();
        $rubricsItems = [];
        $rubricsItems[$root->id] = $root->name;
        foreach ($rubrics->asArray()->all() as $rubric) {
            $rubricsItems[$rubric['id']] = str_repeat('-', $rubric['depth']) . $rubric['name'];
        }

        return $rubricsItems;
    }

}