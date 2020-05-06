<?php

namespace frontend\modules\order\services;

use common\models\CommercialTerms;
use common\models\ComponentPrice;
use common\models\CurrencyRates;
use common\models\Import;
use common\models\ObjectImage;
use common\models\Product;
use common\models\ReplyComponentPrice;
use common\models\ReplyContractInfo;
use common\models\ReplyImport;
use common\models\ReplyImportPrice;
use common\models\ReplyInfo;
use common\models\ReplyLicence;
use common\models\ReplySpecification;
use common\models\ReplySpecificationComponent;
use common\models\ReplySpecificationWork;
use common\models\RequestItem;
use common\models\RequestItemReply;
use common\models\RequestItemStage;
use common\models\RequestItemStageEnum;
use common\models\RequestItemStageItem;
use common\models\Specification;
use common\models\SpecificationComponent;
use frontend\modules\contractor\services\ContractCalculationService;

/**
 * Class RequestItemTargetCostService
 */
class RequestItemTargetCostService
{
    public static function getTargetCost(RequestItem $model): float
    {
        if ($model->target_cost_to && $contract = $model->contract) {
            $terms = CommercialTerms::defaults();
            return round($model->target_cost_to / (
                (100 + $contract->desired_advance_percent)
                / (100 - (ContractCalculationService::getTotalPartnerCosts($contract) + $terms->minimum_profit))
            ), 2);
        }
        return 0;
    }
}