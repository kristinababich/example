<?php

namespace frontend\modules\order\services;

use common\models\Document;
use common\models\ObjectImage;
use common\models\Request;
use common\models\RequestItem;
use common\models\RequestItemPackage;
use common\models\RequestItemProduct;
use frontend\modules\order\forms\RequestForm;
use frontend\modules\order\forms\RequestItemForm;
use yii\data\ArrayDataProvider;

/**
 * Class CopyRequestService
 *
 * Сервис для дублирования заявки
 */
class CopyRequestService
{
    /**
     * Возвращает дублированную модель
     *
     * @param Request $source      модель которую дублируют
     * @param Request $destination модель которая является дублем
     *
     * @return RequestForm
     */
    public static function getDuplicatedModel(Request $source, Request $destination): Request
    {
        $destination->isCopy = true;
        $attributes = $source->getAttributes();
        unset($attributes['id']);
        unset($attributes['object_id']);
        unset($attributes['dispatched_at']);
        unset($attributes['status_id']);
        unset($attributes['user_id']);
        $destination->setAttributes($attributes);
        $destination->parent_id = $source->id;
        return $destination;
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
        $sourceItems = $parent->requestItems;
        $items = [];
        foreach ($sourceItems as $sourceItem) {
            $items []= static::getDuplicatedItem($sourceItem);
        }
        return new ArrayDataProvider(
            [
                'allModels' => $items,
                'sort' => false,
            ]
        );
    }

    /**
     * Возвращает дублированную позицию
     *
     * @param RequestItem $sourceItem модель которую дублируют
     *
     * @return ArrayDataProvider
     */
    public static function getDuplicatedItem(RequestItem $sourceItem): RequestItem
    {
        $model = new RequestItem();
        $model->isCopy = true;
        $attributes = $sourceItem->getAttributes();
        unset($attributes['id']);
        unset($attributes['object_id']);
        unset($attributes['dispatched_at']);
        unset($attributes['count']);
        $model->setAttributes($attributes);
        return $model;
    }

    /**
     * Дублирует позиции
     *
     * @param Request $parent    модель которую дублируют
     * @param int     $requestId ид дублированной модели
     *
     * @return void
     */
    public static function duplicateItems(Request $parent, int $requestId): void
    {
        foreach ($parent->requestItems as $sourceItem) {
            self::duplicateItem($sourceItem, $requestId);
        }
    }

    /**
     * Дублирует позицию
     *
     * @param RequestItem $sourceItem модель которую дублируют
     * @param int         $requestId  ид дублированной модели
     *
     * @return void
     */
    public static function duplicateItem(RequestItem $sourceItem, int $requestId): bool
    {
        $requestItem = static::getDuplicatedItem($sourceItem);
        $requestItem->request_id = $requestId;
        if (!$requestItem->save()) {
            return false;
        }
        foreach ($sourceItem->images as $image) {
            $imageModel = new ObjectImage();
            $attributes = $image->getAttributes();
            $imageModel->setAttributes($attributes);
            $imageModel->to_object_id = $requestItem->getObjectId();
            $imageModel->save();
        }
        foreach ($sourceItem->documents as $document) {
            $documentModel = new Document();
            $attributes = $document->getAttributes();
            $documentModel->setAttributes($attributes);
            $documentModel->to_object_id = $requestItem->getObjectId();
            $documentModel->save();
        }
        foreach ($sourceItem->requestItemPackages as $requestItemPackage) {
            $requestItemPackageModel = new RequestItemPackage();
            $attributes = $requestItemPackage->getAttributes();
            $requestItemPackageModel->setAttributes($attributes);
            $requestItemPackageModel->request_item_id = $requestItem->id;
            $requestItemPackageModel->save();
        }
        foreach ($sourceItem->requestItemProducts as $requestItemProduct) {
            $requestItemProductModel = new RequestItemProduct();
            $attributes = $requestItemProduct->getAttributes();
            $requestItemProductModel->setAttributes($attributes);
            $requestItemProductModel->request_item_id = $requestItem->id;
            $requestItemProductModel->save();
        }
        RequestItemStageService::checkStages($requestItem);
        return true;
    }
}