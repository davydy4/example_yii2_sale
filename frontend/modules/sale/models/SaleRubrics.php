<?php
namespace frontend\modules\sale\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Рубрики акций
 */
class SaleRubrics extends \common\modules\sale\models\SaleRubrics
{

    public static function findActive()
    {
        return static::find()->andWhere('status_on =:status', [':status' => self::STATUS_ON]);
    }

    /**
     * Возвращает потомков (1ый уровень)
     *
     * @return object $this
     */
    public function getActiveChildren()
    {
        return self::findActive()->andWhere(['parent_id' => $this->id])->orderBy(['lft' => SORT_ASC]);
    }

    /**
     * Возращает родителей
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActiveParent()
    {
        return self::findActive()->andWhere(['<=', 'lft', $this->lft])->andWhere(['>=', 'rgt', $this->rgt])->orderBy(['lft' => SORT_ASC]);
    }
}