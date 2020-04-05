<?php

namespace common\modules\sale\models;

use common\components\app\App;
use common\models\ActiveRecordModel;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use common\modules\catalogs\models\CatalogElements;

/**
 * This is the model class for table "tbl_sale_elements".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $url
 * @property string $full_url
 * @property string $name
 * @property string $header
 * @property string $brieftext
 * @property string $description
 * @property int $date_start
 * @property int $date_end,
 * @property string $image_extention
 * @property int $status_on
 * @property string $meta_title
 * @property string $meta_keywords
 * @property string $meta_description
 * @property int $created_at
 * @property int $updated_at
 */
class SaleElements extends ActiveRecordModel
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
        return '{{%sale_elements}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [['name'], 'required'],
                [['parent_id', 'status_on', 'date_start', 'date_end', 'created_at', 'updated_at'], 'integer'],
                [['description', 'brieftext'], 'string'],
                [['url', 'name', 'header'], 'string', 'max' => 190],
                [['image_extention'], 'string', 'max' => 10],
                [['full_url'], 'string', 'max' => 800],
                [['meta_title', 'meta_keywords', 'meta_description'], 'string', 'max' => 255],
                [['url'], 'unique'],
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
            'url' => 'Url адрес',
            'full_url' => 'Полный url адрес',
            'name' => 'Название',
            'header' => 'Заголовок',
            'description' => 'Описание',
            'brieftext' => 'Краткое описание',
            'date_start' => 'Дата начала',
            'date_end' => 'Дата конца',
            'image_extention' => 'Картинка',
            'status_on' => 'Статус',
            'meta_title' => 'Meta Title',
            'meta_keywords' => 'Meta Keywords',
            'meta_description' => 'Meta Description',
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
            'timestamp'   => TimestampBehavior::class,
            'ActiveRecordLogableBehavior' => [
                'class' => 'common\behaviors\ActiveRecordLogableBehavior'
            ]
        ];
    }

    /**
     * Связь с категорией
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(SaleRubrics::class, ['id' => 'parent_id']);
    }

    /**
     * Связь с SaleValue
     *
     * @return \yii\db\ActiveQuery
     */
    public function getValue()
    {
        return $this->hasMany(SaleValue::class, ['parent_id' => 'id']);
    }

    /**
     * Назначает URL
     *
     * @return string
     */
    public function setUrl()
    {
        return !empty($this->url) ? $this->url : App::generateUrl($this, $this->name);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $this->url = $this->setUrl();

        return parent::beforeValidate();
    }


}
