<?php

namespace backend\modules\sale\controllers;

use backend\controllers\SiteController;
use backend\modules\catalogs\models\CatalogElementPropertyForm;
use backend\modules\catalogs\models\CatalogElements;
use backend\modules\catalogs\models\CatalogPropertyValue;
use backend\modules\sale\models\SaleValue;
use Yii;
use backend\modules\sale\models\SaleElements;

/**
 * SaleValuesController
 */
class SaleValueController extends SiteController
{
    public function actionIndex($id)
    {
        $saleValue = Yii::$app->request->post('SaleValue');
        if (!empty($saleValue) && empty(Yii::$app->request->isPjax)) {
            if (!empty($saleValueForm = Yii::$app->request->post('SaleValueForm')))
            {
                foreach ($saleValueForm as $value)
                {
                    $model = new SaleValue();
                    $model->load(Yii::$app->request->post());
                    $model->parent_id = $id;
                    $model->model_id = $value['value'];

                    $model->save();
                }
            }



        }

        $model = SaleElements::findOne($id);

        $searchModel = new SaleValue();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $modelListId = [];
        $modulesList = $this->getModuleList();

        if (Yii::$app->request->isPjax)
        {
            $module = Yii::$app->request->post('model');
            $searchModel->model_name = $module;
            $dirModule = str_replace(['rubrics', 'elements'], '', strtolower($module));
            $dirModule = $dirModule == 'catalog' ? 'catalogs' : $dirModule;
            $model_name = 'frontend\modules\\' . $dirModule . '\models\\' . $module;
            $model_element = new $model_name;

            $modelListId = \yii\helpers\ArrayHelper::map($model_element::find()->all(),'id', 'name');

            return $this->render('_add_new_value',
                ['modelValue' => $searchModel,
                    'modulesList' =>$modulesList,
                    'modelListId' =>$modelListId]);

        }


        return $this->render('index', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modulesList' =>$modulesList,
            'modelListId' =>$modelListId,
        ]);
    }

    /**
     * Возвращает список модулей
     * @return array
     */
    private function getModuleList()
    {
        $modulesList = [];
        foreach (Yii::$app->app->getModuleSettings() as $alias => $value) {

            if (!in_array($alias, ['katalog', 'struktura-sayta', 'professii', 'kompanii'])) {
                continue;
            }

            $rubric = new $value['rubricModelName'];
            $rubric = $rubric::classNameShort();

            $modulesList[$rubric] = $value['moduleName'] . ' (рубрика)';
            $element = new $value['elementModelName'];
            $element = $element::classNameShort();
            $modulesList[$element] = $value['moduleName'] . ' (элемент)';
        }

        return $modulesList;
    }
}