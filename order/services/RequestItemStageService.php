<?php

namespace frontend\modules\order\services;

use common\models\ObjectImage;
use common\models\RequestItem;
use common\models\RequestItemStage;
use common\models\RequestItemStageEnum;
use common\models\RequestItemStageItem;

/**
 * Class RequestItemStageService
 *
 * Сервис для работы со стадиями позиции заявки
 */
class RequestItemStageService
{
    /**
     * Проверяет все этапы позиции заявки
     *
     * @param RequestItem $model позиция заявки
     *
     * @return void
     */
    public static function checkStages(RequestItem $model): void
    {
        $stages = RequestItemStageEnum::find()->where(['is', 'parent_id', null])->orderBy(['position_id' => SORT_ASC])->all();
        foreach ($stages as $stage) {
            if (self::checkStageConditions($model, $stage)) {
                foreach ($stage->subItems as $subItem) {
                    self::checkStageConditions($model, $subItem);
                }
            }
        }
    }

    /**
     * Проверяет существует ли метод проверки условия конкретного этапа
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    public static function checkStageConditions(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if (($method = static::getStageMethod($stage->name)) && is_callable([static::class, $method])) {
            return call_user_func([static::class, $method], $model, $stage);
        }
        return false;
    }

    /**
     * Возвращает метод обработчика этапа
     *
     * @param string $stageName название этапа
     *
     * @return string
     */
    protected static function getStageMethod(string $stageName): ?string
    {
        $methods = [
            RequestItemStageEnum::STAGE_NEW => 'checkStageNew',
            RequestItemStageEnum::STAGE_TK_FORMATION => 'checkStageTkFormation',
            RequestItemStageEnum::STAGE_COST_CALCULATION => 'checkStageCostCalculation',
            RequestItemStageEnum::STAGE_COMMERCIAL_OFFER => 'checkStageCommercialOffer',
            RequestItemStageEnum::STAGE_ORDER => 'checkStageOrder',
            RequestItemStageEnum::STAGE_ITEM_POSITION => 'checkStageItemPosition',
            RequestItemStageEnum::STAGE_ITEM_FIELDS => 'checkStageItemFields',
            RequestItemStageEnum::STAGE_ITEM_DESIGN => 'checkStageItemDesign',
            RequestItemStageEnum::STAGE_ITEM_TASK => 'checkStageItemTask',
            RequestItemStageEnum::STAGE_ITEM_TASK_IN_WORK => 'checkStageItemTaskInWork',
            RequestItemStageEnum::STAGE_ITEM_TASK_CLOSE => 'checkStageItemTaskClosed',
            RequestItemStageEnum::STAGE_ITEM_REPLY => 'checkStageItemReply',
            RequestItemStageEnum::STAGE_ITEM_COMMERCIAL_OFFER => 'checkStageItemCommercialOffer',
            RequestItemStageEnum::STAGE_ITEM_COMMERCIAL_OFFER_REPLY => 'checkStageItemCommercialOfferReply',
        ];
        return $methods[$stageName] ?? null;
    }

    /**
     * Проверяет условия для этапа 'Новая'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageNew(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        $status = static::hasOneOfRequiredAttributes($model) ? RequestItemStage::STATUS_ACTIVE : RequestItemStage::STATUS_DONE;
        static::createOrUpdateStage($model, $stage, $status);
        return true;
    }

    /**
     * Проверяет условия для этапа 'Формирование ТЗ'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageTkFormation(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if (static::hasOneOfRequiredAttributes($model)) {
            $status = static::hasAllRequiredAttributes($model) ? ($model->tasks ? RequestItemStage::STATUS_ACTIVE : RequestItemStage::STATUS_DONE) : RequestItemStage::STATUS_IN_TIME;
            static::createOrUpdateStage($model, $stage, $status);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для этапа 'Расчет стоимости'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageCostCalculation(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->replies) {
            if ($model->tasks && !$model->tasksNotClosed) {
                $status = RequestItemStage::STATUS_ACTIVE;
            } else {
                $status = RequestItemStage::STATUS_DONE;
            }
            static::createOrUpdateStage($model, $stage, $status);
            return true;
        } else {
            static::createOrUpdateStage($model, $stage, RequestItemStage::STATUS_IN_TIME);
            return true;
        }

        return false;
    }

    /**
     * Проверяет условия для этапа 'Коммерческое предложение'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageCommercialOffer(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->request->commercialOffers) {
            if ($model->request->order) {
                $status = RequestItemStage::STATUS_ACTIVE;
            } else {
                $status = RequestItemStage::STATUS_DONE;
            }
            static::createOrUpdateStage($model, $stage, $status);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для этапа 'Заказ'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageOrder(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->request->commercialOffers && $model->request->commercialOffersAnswer) {
            if ($model->request->order) {
                $status = RequestItemStage::STATUS_DONE;
            } else {
                $status = RequestItemStage::STATUS_IN_TIME;
            }
            static::createOrUpdateStage($model, $stage, $status);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'Создана позиция'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemPosition(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        static::createOrUpdateStageItem($model, $stage);
        return true;
    }

    /**
     * Проверяет условия для подэтапа 'Обязательные поля заполнены'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemFields(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if (static::hasAllRequiredAttributes($model)) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        } else {
            static::removeStageItem($model, $stage);
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'Дизайн прототипа прикреплен'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemDesign(RequestItem $model, RequestItemStageEnum $stage): bool
    {

        if ($images = ObjectImage::find()->where(['to_object_id' => $model->getObjectId()])->all()) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        } else {
            static::removeStageItem($model, $stage);
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'Задачи на расчет созданы'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemTask(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->tasks) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'Задачи на расчет в работе'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemTaskInWork(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->tasksInWork) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'Прикреплен ответ на позицию'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemReply(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->replies) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'Задача на расчет закрыта'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemTaskClosed(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->tasksClosed) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'КП отправлено'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemCommercialOffer(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->request->commercialOffers) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        }
        return false;
    }

    /**
     * Проверяет условия для подэтапа 'Ответ на КП получен'
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return bool
     */
    protected static function checkStageItemCommercialOfferReply(RequestItem $model, RequestItemStageEnum $stage): bool
    {
        if ($model->request->commercialOffersAnswer) {
            static::createOrUpdateStageItem($model, $stage);
            return true;
        }
        return false;
    }

    /**
     * Создает или обновляет этап в системе и в списке позиции заявки
     *
     * @param RequestItem          $model  позиция заявки
     * @param RequestItemStageEnum $stage  этап
     * @param int                  $status стутус
     *
     * @return void
     */
    protected static function createOrUpdateStage(RequestItem $model, RequestItemStageEnum $stage, int $status): void
    {
        if (!$stageModel = $model->getStageByEnumId($stage->id)) {
            $stageModel = new RequestItemStage();
            $stageModel->request_item_id = $model->id;
            $stageModel->stage_enum_id = $stage->id;
        }
        if ($stageModel->status_id != $status) {
            $stageModel->status_id = $status;
            $stageModel->save();
            $model->updateStageByEnumId($stage->id, $stageModel);
        }
    }

    /**
     * Удаляет подэтап в системе
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return void
     */
    protected static function removeStageItem(RequestItem $model, RequestItemStageEnum $stage): void
    {
        if ($stageItem = $model->getSubStageByEnumId($stage->id)) {
            $stageItem->delete();
            $model->deleteSubStageByEnumId($stage->id);
        }
    }

    /**
     * Создает или обновляет подэтап в системе и в списке позиции заявки
     *
     * @param RequestItem          $model позиция заявки
     * @param RequestItemStageEnum $stage этап
     *
     * @return void
     */
    protected static function createOrUpdateStageItem(RequestItem $model, RequestItemStageEnum $stage): void
    {
        if (!$stageItem = $model->getSubStageByEnumId($stage->id)) {
            $stageItem = new RequestItemStageItem();
            if ($requestItemStage = $model->getStageByEnumId($stage->parent_id)) {
                $stageItem->request_item_stage_id = $requestItemStage->id;
                $stageItem->stage_enum_id = $stage->id;
                $stageItem->status_id = RequestItemStage::STATUS_DONE;
                $stageItem->save();
                $model->updateSubStageByEnumId($stage->id, $stageItem);
            }
        }
    }

    /**
     * Проверяет наличие всех обязательных полей в позиции
     *
     * @param RequestItem $model позиция заявки
     *
     * @return bool
     */
    public static function hasAllRequiredAttributes(RequestItem $model): bool
    {
        foreach (static::getRequiredAttributeForNewStage() as $attribute) {
            if (($model->$attribute === null) || ($model->$attribute === [])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Проверяет наличие одного обязательного поля в позиции
     *
     * @param RequestItem $model позиция заявки
     *
     * @return bool
     */
    protected static function hasOneOfRequiredAttributes(RequestItem $model): bool
    {
        foreach (static::getRequiredAttributeForNewStage() as $attribute) {
            if (($model->$attribute !== null) && ($model->$attribute !== '')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Возвраащет список обязательных полей позиции
     *
     * @return array
     */
    protected static function getRequiredAttributeForNewStage(): array
    {
        return [
            'title',
            'count',
            'quantum_from',
            'quantum_to',
            'individualPackage',
            'groupRequestItemPackagesItems'
        ];
    }
}