<?php

namespace frontend\modules\order\forms;

use common\models\ObjectImage;
use common\models\Request;
use common\models\behaviors\DocumentAttachementBehavior;
use common\models\RequestItemPackage;
use common\models\RequestItemProduct;
use common\models\RequestItemReply;
use common\models\Task;
use frontend\modules\order\services\CopyRequestService;
use frontend\modules\order\services\RequestItemReplyCalculationService;
use frontend\modules\order\services\RequestItemStageService;
use yii\data\ArrayDataProvider;

/**
 * Модель формы task
 */
class RequestForm extends Request
{
    public $isCopy = false;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['document_ids'], 'safe'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => DocumentAttachementBehavior::class
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        return $labels;
    }


    /**
     * Переводим телефоны и email из параметров в массивы
     * {@inheritdoc}
     */
    public function afterFind()
    {
        parent::afterFind();
    }

    /**
     * Сохраняет форму
     *
     * @param array $data параметры запроса
     *
     * @return bool результат сохранения
     * @throws \yii\db\Exception
     * @throws \yii\base\Exception
     */
    public function saveData(array $data): bool
    {
        $transaction = $this->getDb()->beginTransaction();
        try {
            if ($this->load($data) && $this->validate()) {
                if (!$this->save()) {
                    throw new \Exception('Cant safe request item');
                }
                $transaction->commit();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new $e;
        }

        return true;
    }

    /**
     * Возвращает имя класса для поиска сущности по словарю
     *
     * @return string имя класса сохраняемой модели
     */
    protected static function getClassNamespace(): string
    {
        return Request::class;
    }

    /**
     * Возвращает дублированную модель
     *
     * @param Request $parent модель которую дублируют
     *
     * @return RequestForm
     */
    public static function duplicate(Request $parent): self
    {
        $model = new static();
        return CopyRequestService::getDuplicatedModel($parent, $model);
    }

    /**
     * Возвращает список дублированных позиций
     *
     * @param Request $parent модель которую дублируют
     *
     * @return ArrayDataProvider
     */
    public static function getDuplicatedItems(Request $parent): ArrayDataProvider
    {
        return CopyRequestService::getDuplicatedItems($parent);
    }
}
