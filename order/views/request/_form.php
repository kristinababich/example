<?php

use yii\helpers\Html;
use common\widgets\ActiveForm;
use common\modules\comments\widgets\CommentsWidget;
use frontend\modules\order\controllers\RequestController;

/* @var $this yii\web\View */
/* @var $model common\models\Request */
/* @var $form yii\widgets\ActiveForm */
$this->registerJsFile('@web/js/request.js', ['depends' => [\frontend\assets\AppAsset::class]]);

$formParams = [
    'action' => ($model->isNewRecord ? ($model->parent_id ? ['copy', 'id' => $model->parent_id] : ['create']) : ['update', 'id' => $model->id]),
    'layout' => 'default',
    'options' => ['data-role' => 'alert-form', 'data-main-role' => 'form-chunk', 'data-pjax' => 0],
    //'enableClientValidation' => true,
    'fieldConfig' => [
        'options' => [
            'class' => 'form-group',
        ],
        'inputOptions' => ['disabled' => $model->isDisabled() ? 'disabled' : false]
    ],
];
$view = $this;
$readOnly = $model->isDisabled();
if ($dataProvider) {
    $itemsDataProvider = clone $dataProvider;
}
?>

<div class="request-form">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
            <?php if (!$model->isDisabled()): ?>
                <div class="card-header">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success', 'data-main-role' => 'form-chunk']) ?>
                    <?= Html::submitButton('Сохранить и продолжить',
                        [
                            'class' => 'btn btn-outline-success',
                            'name' => 'to-update',
                            'value' => 'to-update',
                            'data-main-role' => 'form-chunk'
                        ]) ?>
                </div>
            <?php endif; ?>
                <div class="card-body">
                    <?= \common\widgets\Tabs::widget([
                        'headerOptions' => ['data-role' => 'contractor-tabs'],
                        'items' => [
                            [
                                'label' => 'Главная',
                                'content' => ActiveForm::wrapContentWithForm($formParams, function($form) use ($model, $view, $isRequest, $readOnly, $parentModel) {
                                    return $view->render('_form_main', [
                                        'model' => $model,
                                        'form' => $form,
                                        'isRequest' => $isRequest,
                                        'readOnly' => $readOnly,
                                        'parentModel' => $parentModel,
                                    ]);
                                }),
                                'linkOptions' => [
                                    'data-role' => 'change-widget-link', 'data-widget-id' => 'main-info-widgets'
                                ],
                                'active' => $tab == RequestController::TAB_MAIN,
                            ],
                            [
                                'label' => 'Ответы на позиции',
                                'content' => !$model->isNewRecord  && isset($itemsDataProvider)? $this->render('_form_reply_list', [
                                    'items' => $itemsDataProvider->query->orderBy(['id' => SORT_DESC])->all(),
                                    'repliesDataProvider' => $repliesDataProvider, 'request' => $model, 'readOnly' => $readOnly
                                ]) : '',
                                'linkOptions' => [
                                    'class' => $model->isNewRecord ? 'nav-link disabled' : 'nav-link',
                                    'data-role' => 'change-widget-link', 'data-widget-id' => 'reply-info-widgets'
                                ],
                                'active' => $tab == RequestController::TAB_REPLIES,
                            ],
                            [
                                'label' => 'КП',
                                'content' => !$model->isNewRecord  ? $this->render('_form_commercial_offers', [
                                     'model' => $model
                                ]) : '',
                                'linkOptions' => [
                                    'class' => $model->isNewRecord ? 'nav-link disabled' : 'nav-link',
                                    'data-role' => 'change-widget-link', 'data-widget-id' => 'offer-widgets'
                                ],
                                'active' => $tab == RequestController::TAB_OFFERS,
                            ],
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
        <?= \frontend\widgets\LeftPanelWidget::widget([
            'containerOptions' => ['class' => "col-6", 'data-role' => "left-panel-widgets"],
            'items' => [
                [
                    'headerTitle' => 'Договор',
                    'headerButton' => Html::a('Сохранить', '#',
                        ['class' => 'btn btn-outline-info right-float'. (($model->isNewRecord || $readOnly) ? ' disabled' : ''), 'data-role' => "save-contract", 'data-id' => $model->id]),
                    'content' => $model->isNewRecord ? '' : $this->render('_form_contract', [
                        'model' => $model,
                        'readOnly' => $readOnly,
                    ]),
                    'cssClass' => 'main-info-widgets show'
                ],
                [
                    'headerTitle' => 'Позиции',
                    'headerButton' => !$model->isDisabled() ? Html::a('Добавить позицию', ['/order/request-item/create', 'requestId' => $model->id], ['class' => ('btn btn-outline-info right-float' . ($model->isNewRecord ? ' disabled' : ''))]) : '' ,
                    'content' => $model->isNewRecord ? '' : $this->render('_request_items', [
                        'requestItem' => $model,
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ]),
                    'cssClass' => 'main-info-widgets show',
                    'contentCssClass' => 'no-padding-top'
                ],
                [
                    'fullContent' => !$readOnly ? $this->render('_form_offer', [
                        'model' =>  $offerModel,
                        'request' => $model,
                        'buttonLabel' => 'Прикрепить КП',
                        'dateLabel' => 'КП отправлено',
                        'type' => 'draft',
                        'listId' => 'commercial-offer-list',
                        'title' => 'Коммерческие предложения',
                        'tab' => $tab
                    ]) . $this->render('_form_offer', [
                        'model' =>  $offerModel,
                        'request' => $model,
                        'buttonLabel' => 'Прикрепить ответ на КП',
                        'dateLabel' => 'Ответ получен',
                        'type' => 'answer',
                        'listId' => 'commercial-offer-answer-list',
                        'title' => 'Ответы на КП',
                        'tab' => $tab
                    ]) : ''
                ],
                [
                    'headerTitle' => 'Комментарии',
                    'content' =>  CommentsWidget::widget(['model' => $model, 'size' => CommentsWidget::SIZE_SMALL, 'layout' => '{items}<br/><br/>{form}']),
                    'cssClass' => 'main-info-widgets offer-widgets reply-info-widgets' . (!$model->isNewRecord ? ' show' : '')
                ],
            ],
        ])?>
    </div>
</div>