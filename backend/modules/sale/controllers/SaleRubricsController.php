<?php

namespace backend\modules\sale\controllers;

use backend\controllers\SiteController;
use backend\modules\sale\models\SaleRubrics;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * SaleRubricsController
 */
class SaleRubricsController extends SiteController
{
    /**
     * Lists all SaleRubrics models.
     * @return mixed
     */
    public function actionIndex()
    {
        //Создаю дерево и главную странцу, если их нет
        \common\modules\sale\models\SaleRubrics::getRoot();

        $searchModel = new SaleRubrics();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new SaleRubrics model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SaleRubrics();
        if ($model->load(Yii::$app->request->post()) && $model->saveNode()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model
        ]);
    }

    /**
     * Updates an existing SaleRubrics model.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->saveNode()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model
        ]);
    }

    /**
     * Deletes an existing SaleRubrics model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->deleteNode();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SaleRubrics model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SaleRubrics the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SaleRubrics::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}