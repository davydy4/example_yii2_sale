<?php
namespace frontend\modules\sale\models;


use frontend\modules\tags\models\TagsRubrics;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Связь акций с моделями и элементами моделей
 */
class SaleValue extends \common\modules\sale\models\SaleValue
{

    public static function findActive()
    {
        return static::find()->andWhere(self::tableName() . '.status_on =:status', [':status' => self::STATUS_ON]);
    }

}