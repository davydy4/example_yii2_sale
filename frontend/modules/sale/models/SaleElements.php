<?php
namespace frontend\modules\sale\models;

use frontend\modules\sale\models\SaleValue;
use frontend\modules\catalogs\models\CatalogElements;
use frontend\modules\catalogs\models\CatalogPropertyRubricsFilters;
use frontend\modules\tags\models\TagsRubrics;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Страницы
 */
class SaleElements extends \common\modules\sale\models\SaleElements
{
    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public static function findActive()
    {
        return static::find()->andWhere(self::tableName() . '.status_on =:status', [':status' => self::STATUS_ON]);
    }

    public function search($params)
    {
        $settings = Yii::$app->app->getModuleSettings($this::$moduleAlias);
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC],],
            'pagination' => [
                'forcePageParam' => false,
                'pageSizeParam' => false,
                'pageSize' => $settings['elementsPageSite']
            ]
        ]);

        $query->andWhere([
            'status_on' => self::STATUS_ON
        ]);


        $query->andFilterWhere([
            'parent_id' => $params['SaleElements']['parent_id'] ?? 1
        ]);



        if (!($this->load($params))) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public function getParent()
    {
        return $this->hasOne(SaleRubrics::class, ['id' => 'parent_id']);
    }


    /**
     * Возвращает DataProvider для страницы списка
     *
     * @param $request
     * @return ActiveDataProvider
     */
    public function getDataProvider($request, $paramsProvider = [])
    {
        $query = self::findActive();
        $query->joinWith('parent');
        $aliasElement = self::tableName();
        $aliasRubrics = SaleRubrics::tableName();

        $query->andWhere([$aliasElement . '.parent_id' => $paramsProvider['parent_id']]);

        if ($this->load($request->get()) || $this->load($request->post())) {

            $query->andFilterWhere([
                'and',
                ['like', $aliasElement . '.name', $this->filterName . '%', false],
                ['>=', $aliasElement . '.price', $this->filterPriceFrom],
                ['<=', $aliasElement . '.price', $this->filterPriceTo],
                ['like', $aliasRubrics . '.name', $this->filterRubricName . '%', false],
                ['like', $aliasElement . '.code', $this->filterCode, false],
                ['like', $aliasElement . '.article', $this->filterArticle, false],
                ['=', $aliasElement . '.status_hit', !empty($this->filterHit) ? $this->filterHit : null],
                ['=', $aliasElement . '.status_new', !empty($this->filterNew) ? $this->filterNew : null],
                ['=', $aliasElement . '.status_sale', !empty($this->filterSale) ? $this->filterSale : null],
            ]);

            // фильтр
            if ($request->post('CatalogElements') || $request->get('CatalogElements') )
            {
                $data = $request->get('CatalogElements') ?? $request->post('CatalogElements');
                $query = CatalogPropertyRubricsFilters::getQueryProperty($data, $query);

            }

        };

        return new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => $paramsProvider['defaultOrder'] ?? ['id' => SORT_DESC]],
            'pagination' => [
                'totalCount' => $query->count(),
                'defaultPageSize' => $paramsProvider['pageSize'] ?? 20,
            ],
        ]);
    }

    //DataProvider для товаров (model_name, model_id ) в конкретной акции
    public function getDataProviderValue($request, $paramsProvider = [])
    {

        $query = CatalogElements::findActive();
        $query->innerJoin(SaleValue::tableName(), SaleValue::tableName() . '.model_id = ' . CatalogElements::tableName() . '.id');
        $query->andWhere([SaleValue::tableName() . '.parent_id' => $paramsProvider['id']]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => $paramsProvider['defaultOrder'] ?? ['id' => SORT_DESC]],
            'pagination' => [
                'totalCount' => $query->count(),
                'defaultPageSize' => $paramsProvider['pageSize'] ?? 25,
            ],
        ]);
    }
}