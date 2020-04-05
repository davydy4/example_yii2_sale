<?php

namespace common\modules\sale\models;


use common\components\app\App;
use common\models\ActiveRecordModel;
use common\modules\settings\models\SiteSettings;
use yii\helpers\BaseHtml;
use yii\helpers\Json;
use Yii;
use creocoder\nestedsets\NestedSetsBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "tbl_sale_rubrics".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $url
 * @property string $full_url
 * @property string $name
 * @property string $header
 * @property string $brieftext
 * @property string $description
 * @property string $image_extention
 * @property int $status_on
 * @property string $meta_title
 * @property string $meta_keywords
 * @property string $meta_description
 * @property int $created_at
 * @property int $updated_at
 */

class SaleRubrics extends ActiveRecordModel
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
        return '{{%sale_rubrics}}';
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
                [['parent_id', 'lft', 'rgt', 'depth', 'status_on', 'created_at', 'updated_at'], 'integer'],
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
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'depth' => 'Depth',
            'url' => 'Url адрес',
            'full_url' => 'Полный url адрес',
            'name' => 'Название',
            'header' => 'Заголовок',
            'description' => 'Описание',
            'brieftext' => 'Краткое описание',
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
            'tree' => [
                'class' => NestedSetsBehavior::class
            ],
            'ActiveRecordLogableBehavior' => [
                'class' => 'common\behaviors\ActiveRecordLogableBehavior'
            ]
        ];
    }

    /**
     * Связь с элементами
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElements()
    {
        return $this->hasMany(SaleElements::class, ['parent_id' => 'id']);
    }



    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new TreeQuery(get_called_class());
    }


    /**
     * Возвращает родительский узел, если его нет - создает
     *
     * @param self|null $model
     * @return mixed
     * @throws \yii\db\Exception
     */
    public static function getRoot(self $model = null)
    {
        if (is_null($model)) {
            $model = new self;
            $model->parent_id = 0;
        }
        $root = $model::find()->roots()->one();

        if (!$root) {
            $url = '/';
            $model->name = $url;
            $model->url = $url;
            $model->full_url = $url;
            $model->status_on = self::STATUS_ON;

            $model->makeRoot();
            $root = $model::find()->roots()->one();
            $root->url = $url;
            Yii::$app->db->createCommand()->update(self::tableName(), ['url' => $url], ['id' => $root->id])->execute();
        }

        return $root;
    }

    /**
     * Возвращает потомков узла
     *
     * @param SaleRubrics|null $model
     *
     * @return mixed
     */
    public static function getChildrenTree(self $model = null)
    {
        $root = self::getRoot($model);

        return $root->children();
    }

    /**
     * Назначает URL
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function setUrl()
    {
        $root = self::getRoot();
        if (!empty($root) && $root->id == $this->id) {
            return '/';
        }
        return !empty($this->url) ? $this->url : App::generateUrl($this, $this->name);
    }

    /**
     * Назначает полный URL
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function setFullUrl()
    {
        $root = self::getRoot();
        if (!empty($root->id) && $this->id == $root->id) {
            return '/';
        }
        //Получаю родительские записи
        $fullUrl = [];
        $parents = $this->parents()->asArray()->all();
        if (!empty($parents)) {
            foreach ($parents as $parent) {
                if ($parent['url'] == '/' || $parent['depth'] == 0) {
                    continue;
                }
                $fullUrl[] = $parent['url'];
            }
        }
        $fullUrl[] = $this->url;

        return '/' . implode("/", $fullUrl);
    }


    /**
     * Возвращает роительскую профессию
     *
     * @return object
     */
    private function getRootModel()
    {
        $root = self::find()->where(['id' => (int)$this->parent_id])->one();
        if (empty($root)) {
            $root = self::getRoot($root);
        }

        return $root;
    }

    /**
     * Создает новую категорию
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function saveNode()
    {
        $root = $this->getRootModel();
        $this->url = $this->setUrl();

        if ($root->id != $this->id) {
            $this->parent_id = $root->id;
        }

        //Если обновление записи без перестраивания дерева - просто сохраняю без транзакций
        if (!$this->isNewRecord && ($this->oldAttributes['parent_id'] == $this->parent_id || $this->id == $root->id)) {
            $this->full_url = $this->setFullUrl();
            $this->update();
            return true;
        } else {
            $transaction = self::getDb()->beginTransaction();
            try {
                if ($this->appendTo($root)) {
                    // Обноляю full_url
                    $this->full_url = $this->setFullUrl();
                    $this->update();
                    $transaction->commit();
                    return true;
                }
            } catch(\Throwable $e) {
                $transaction->rollBack();

                return false;
            }
        }


        return false;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function deleteNode()
    {
        //Корень удалять нельзя
        if ($this->depth == 0) {
            return false;
        }
        $transaction = self::getDb()->beginTransaction();
        try {
            if (!$this->deleteWithChildren()) {
                throw new \yii\web\HttpException(500, BaseHtml::errorSummary($this));
            }
            $transaction->commit();

            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();

            return false;
        }
    }

    /**
     * Возвращает дерево
     *
     * @return object $this
     */
    public function getTree()
    {
        return self::find()->orderBy(['lft' => SORT_ASC]);
    }

    public function beforeSave($insert) {
        Yii::$app->cache->flush();
        return parent::beforeSave($insert);

    }
}
