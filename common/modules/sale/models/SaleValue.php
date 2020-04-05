<?php

namespace common\modules\sale\models;

use common\components\app\App;
use common\models\ActiveRecordModel;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_sale_value".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $model_name
 * @property int $model_id
 * @property int $status_on
 * @property int $created_at
 * @property int $updated_at
 *
 * @property SaleElements $parent
 */
class SaleValue extends ActiveRecordModel
{
    /**
     * Имя алиаса модуля
     * @var string
     */
    public static $moduleAlias = 'akcii';


    /**
     * Статус записи
     */
    const STATUS_ON = 1;
    const STATUS_OFF = 0;
    public static $status = [
        self::STATUS_ON => 'Включено',
        self::STATUS_OFF => 'Отключено'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sale_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [['parent_id', 'model_name', 'model_id'], 'required'],
                [['parent_id', 'model_id', 'created_at', 'updated_at'], 'integer'],
                [['model_name'], 'string', 'max' => 250],
                [['status_on'], 'default', 'value' => self::STATUS_ON],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Акция',
            'model_name' => 'Модель',
            'model_id' => 'ID элемента каталога (рубрика, товар)',
            'status_on' => 'Статус',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::class,
        ];
    }

    /**
     * Связь с элементом акции
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne( SaleElements::class, ['id' => 'parent_id']);
    }
}