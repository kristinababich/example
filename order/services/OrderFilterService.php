<?php

namespace frontend\modules\order\services;

use common\models\Network;
use common\models\Order;
use common\services\FilterService;
use yii\helpers\ArrayHelper;

/**
 * Class OrderFilterService
 *
 * Сервис для работы с фильтрами
 */
class OrderFilterService extends FilterService
{
    /**
     * Возвращает все значения атрибута
     *
     * @param string $q             запрос пользователя
     * @param string $attribute     атрибут
     * @param string $textAttribute текстовое значение
     *
     * @return array
     */
    public static function getValues(?string $q, string $attribute, ?string $textAttribute): array
    {
        return static::getValuesByQuery(Order::find(), $q, $attribute, $textAttribute);
    }

    protected static function getTextAttributeConfig(string $textAttribute): ?array
    {
        $config = [
            'networkTitle' => [
                'model' => Network::class,
                'attribute' => 'title',
                'id_attribute' => 'network_id'
            ]
        ];
        return $config[$textAttribute] ?? null;
    }
}