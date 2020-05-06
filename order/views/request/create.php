<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Request */

$isRequest = $model->system_type_id == \common\models\Request::SYSTEM_TYPE_REQUEST;
$this->title = 'Добавить ' . ($isRequest ? 'заявку' : 'пресейл');
$this->params['breadcrumbs'][] = ['label' => 'Заявки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="request-create">
    <?= $this->render('_form', [
        'model' => $model,
        'isRequest' => $isRequest,
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'parentModel' => false,
        'repliesDataProvider' => false,
        'offerModel' => $offerModel,
        'tab' => $tab
    ]) ?>
</div>