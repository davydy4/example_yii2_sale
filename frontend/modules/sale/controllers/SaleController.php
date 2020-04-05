<?php

namespace frontend\modules\sale\controllers;


use common\modules\pages\models\Pages;
use frontend\controllers\SiteController;
use Yii;
use yii\helpers\Url;
use yii\web\HttpException;
use frontend\modules\sale\models\SaleElements;
use frontend\modules\sale\models\SaleRubrics;
/**
 * SaleController
 */
class SaleController extends SiteController
{
    /**
     *
     * Lists all Sale models.
     * @return mixed
     */

    public function actionIndex($param = null)
    {
        // Разибраю ссылку в $params
        $param = trim($param);
        $param = substr($param, -1) == '/' ? substr($param, 0, -1) : $param;
        $param = explode("/", $param);
        $param = trim(end($param));

        // Если на странице просмотра товара
        $element = SaleElements::findActive()->andWhere(['like', 'url', $param, false])->one();

        if (!empty($element)) {
            return $this->saleView($element);
        }
        // вывод всех Elements в Professions
        else {
            return $this->saleList($param);
        }
    }

    /**
     * Выводит список всех акций
     *
     * @param $param
     * @return string
     * @throws HttpException
     */

    private function saleView($element)
    {


        $professionsParams = [];
        $professionsParams['rubric'] = $element->parent;
        $professionsParams['catalogLinks'] = $element->parent->getActiveParent()->all();
        $professionsParams['elements'] = $element;
        $professionsParams['id'] = $element->id;
        $modelElements = new SaleElements();
        $dataProviderElements = $modelElements->getDataProviderValue(Yii::$app->request, $professionsParams);
        $professionsParams['dataProviderElements'] = $dataProviderElements;
        return $this->render('view', $professionsParams);
    }

    /**
     * Выводит акцию и товары (модели) в акции
     *
     * @param $param
     * @return string
     * @throws HttpException
     */
    private function saleList($param)
    {
        // Рубрика в которой нахожусь
        $modelRubrics = SaleRubrics::findActive()->andWhere(['like', 'url', $param])->one();
        if (empty($modelRubrics)) {

            // Проверяю, может это базовый уровень каталога
            // Убрать Get-параметры, если есть
            $chUrl = explode("?", Url::to());
            $checkUrl = str_replace(['/', '.html'], '', $chUrl[0]);
            $page = Pages::find()->where(['like', 'url', $checkUrl])->andWhere(['module_alias' => SaleRubrics::$moduleAlias])->andWhere(['status_on' => Pages::STATUS_ON])->count();
            if (empty($page)) {
                throw new HttpException(404 ,'Страница не найдена');
            }
            $modelRubrics = SaleRubrics::findActive()->andWhere(['depth' => 0])->one();
        }

        if (empty($modelRubrics)) {
            throw new HttpException(404 ,'Страница не найдена');
        }

        // Рубрики доступные для отурытия из текущей $modelRubrics категории
        $rubricsChildren = $modelRubrics->getActiveChildren()->all();

        // Товары в текущей акции
        $paramsProvider = [];
        $paramsProvider['parent_id'] = $modelRubrics->id;
        $modelElements = new SaleElements();
        $dataProviderElements = $modelElements->getDataProvider(Yii::$app->request, $paramsProvider);

        $catalogParams = [];
        $catalogParams['rubric'] = $modelRubrics;
        $catalogParams['rubricsChildren'] = $rubricsChildren;
        $catalogParams['modelElements'] = $modelElements;
        $catalogParams['dataProviderElements'] = $dataProviderElements;
        $catalogParams['catalogLinks'] = $modelRubrics->getActiveParent()->all();

        // Если есть кастомный шаблон для модели - назначаю
        $this->setCustomModelTemplate($modelRubrics);

        return $this->render('index', $catalogParams);
    }
}