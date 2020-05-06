<?php

use yii\helpers\Html;
use common\widgets\GridView;
use yii\widgets\Pjax;
use common\models\Request;
use kartik\date\DatePicker;
use yii\bootstrap4\ActiveForm;
use common\widgets\Select2;
use common\components\extended\ActionColumn;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\RequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerJsFile('@web/js/search.js', ['depends' => [\frontend\assets\AppAsset::class]]);
$this->title = $searchModel->getSearchTypeTitle($type);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="request-index">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php Pjax::begin(['id' => 'filter', 'timeout' => 10000]); ?>
                    <div class="product-component-search">
                        <?php $form = ActiveForm::begin([
                            'action' => ['index'],
                            'method' => 'get',
                            'options' => [
                                'data-pjax' => 1,
                                'id' => 'filter-form',
                            ],
                        ]); ?>
                        <div class="row">
                            <div class="col-sm-5">
                                <?= Html::a('Добавить заявку', ['create', 'system_type_id' => common\models\Request::SYSTEM_TYPE_REQUEST], ['class' => 'btn btn-outline-info']) ?>
                                <?= Html::a('Добавить прeсейл', ['create', 'system_type_id' => common\models\Request::SYSTEM_TYPE_PRESALE], ['class' => 'btn btn-outline-info']) ?>
                            </div>
                            <div class="col-sm-4">
                            </div>
                            <div class="col-sm-3">
                                <?=
                                $form->field($searchModel, "newFilter")->widget(Select2::class, [
                                    'data' => $searchModel->getFiltersTitles(),
                                    'theme' => Select2::THEME_BOOTSTRAP4,
                                    'options' => ['placeholder' => 'Выберите фильтр', 'data-role' => 'new-filter-select', 'value' => ''],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'language' => "ru",
                                    ],
                                    'pluginLoading' => false,
                                ])->label(false);?>
                            </div>
                        </div>
                        <div class="row">
                            <?php foreach ($searchModel->filters as $key => $filter) : ?>
                                <div class="col-sm-3" data-role="filter-container">
                                    <?= $filter->render($form, $searchModel); ?>

                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                    <?php  Pjax::end(); ?>
                    <?php Pjax::begin(['timeout' => 5000, 'id' => 'filter-grid']); ?>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped dataTable dtr-inline table-responsive'],
                        'filterModel' => $searchModel,
                        'filterSelector' => '[data-role=filter]',
                        'options' => ['id' => 'request-grid', 'class' => 'grid-with-filters'],
                        'columns' => [
                            'id',
                            [
                                'attribute' => 'system_type_id',
                                'content' => function (
                                    Request $model
                                ) {
                                    return $model->getSystemTypeLabel();
                                },
                                'label' => 'Вид',
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'system_type_id',
                                    'data' => Request::getSystemTypeLabelList(),
                                    'theme' => Select2::THEME_BOOTSTRAP4,
                                    'options' => ['placeholder' => '-'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'escapeMarkup' => new JsExpression('function (markup){return markup;}')
                                    ],
                                ]),
                            ],
                            [
                                'attribute' => 'subject',
                                'content' => function (Request $model) {
                                    return Html::a($model->subject, \yii\helpers\Url::to(['/order/request/update/', 'id' => $model->id]));
                                }
                            ],
                            [
                                'attribute' => 'user_id',
                                'content' => function (
                                Request $model
                                ) {
                                    return $model->user ? $model->user->shortname : '';
                                },
                                'label' => 'Ответственный',
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'user_id',
                                    'initValueText' => $searchModel->user_id ? $searchModel->user->shortname : '', // set the initial display text
                                    'options' => ['placeholder' => '-'],
                                    'theme' => Select2::THEME_BOOTSTRAP4,
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        //'minimumInputLength' => 3,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/order/request/get-values', 'attribute' => 'user_id', 'textAttribute' => 'userName']),
                                            'dataType' => 'json',
                                            'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        ],
                                    ]),
                                'enableSorting' => false
                            ],
                            [
                                'attribute' => 'network_id',
                                'content' => function (
                                Request $model
                                ) {
                                    return $model->network ? $model->network->title : '';
                                },
                                'label' => 'Сеть',
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'network_id',
                                    'initValueText' => $searchModel->network_id ? $searchModel->network->title : '', // set the initial display text
                                    'options' => ['placeholder' => '-'],
                                    'theme' => Select2::THEME_BOOTSTRAP4,
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        //'minimumInputLength' => 3,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/order/request/get-values', 'attribute' => 'network_id', 'textAttribute' => 'networkTitle']),
                                            'dataType' => 'json',
                                            'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        ],
                                    ]),
                                'enableSorting' => false
                            ],
                            [
                                'attribute' => 'status_id',
                                'content' => function (
                                Request $model
                                ) {
                                    return $model->getStatusLabel();
                                },
                                'label' => 'Статус',
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'status_id',
                                    'data' => Request::getStatusList(),
                                    'theme' => Select2::THEME_BOOTSTRAP4,
                                    'options' => ['placeholder' => '-'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'escapeMarkup' => new JsExpression('function (markup){return markup;}')
                                    ],
                                ]),
                            ],
                            [
                                'attribute' => 'created_at',
                                'content' => function (
                                Request $model
                                ) {
                                    return Yii::$app->formatter->asDate($model->object->created_at);
                                },
                                'label' => 'Дата создания',
                                'filter' => DatePicker::widget([
                                    'options' => [
                                        'placeholder' => 'Выберите дату',
                                        'autocomplete' => 'off',
                                    ],
                                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                                    'removeIcon' => '×',
                                    'model' => $searchModel,
                                    'attribute' => 'created_at',
                                    //'value' => $searchModel->expiration_at ?: Yii::$app->formatter->asDate(time()),
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'autoclose' => true,
                                        'format' => 'dd.mm.yyyy',
                                        'orientation' => 'bottom',
                                    ],
                                ]),
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '<div class="table-action">{update} {delete} {copy}</div>',
                                'buttons' => [
                                    'update' => function ($url, $model) {
                                        return Html::a('<i class="align-middle mr-2 fas fa-fw fa-pencil-alt"></i>', $url, ['data-pjax' => 0]);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        return ActionColumn::defaultDeleteButton($url, $model, $key);
                                    },
                                     'copy' => function ($url, $model) {
                                        return Html::a('<i class="align-middle mr-2 fas fa-fw fa-copy"></i>', $url, ['data-pjax' => 0]);
                                    },
                                ],
                                'visibleButtons' => [
                                    'update' => function ($model) {
                                        return !$model->isDisabled();
                                    },
                                    'delete' => function ($model) {
                                        return !$model->isDisabled();
                                    },
                                ]
                            ],
                        ],
                    ]);
                    ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>