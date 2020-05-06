<?php

use common\widgets\GridView;
use common\models\Task;
use yii\helpers\Html;

/** @var \yii\data\ActiveDataProvider $dataProvider */
?>
<?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'columns' => [
        [
            'label' => 'ID',
            'content' => function (Task $model) {
                return Html::a($model->id, $model->itemUrl, ['data-pjax' => 0]);
            }
        ],
        [
            'label' => 'Тема',
            'content' => function (Task $model) {
                return Html::a($model->subject, $model->itemUrl, ['data-pjax' => 0]);
            },
        ],
        [
            'content' => function (
                Task $model
            ) {
                return $model->department ? $model->department->title : '';
            },
            'label' => 'Отдел',
        ],
        [
            'content' => function (Task $model) {
                $id = $model->status_id;
                return Task::getStatusNameById($id);
            },
            'label' => 'Статус',
        ],
        [
            'content' => function (
                Task $model
            ) {
                $identity = Yii::$app->user->identity;
                $identityDepId = 0;
                if ($identityDep = $identity->department) {
                    $identityDepId = $identityDep->id;
                }
                return ($model->user && $model->user->department) ? $model->user->shortname : '';
            },
            'label' => 'Исполнитель',
        ],
        [
            'content' => function (
                Task $model
            ) {
                return Yii::$app->formatter->asDate($model->object->created_at);
            },
            'label' => 'Дата создания',
        ],
        [
            'content' => function (
                Task $model
            ) {
                return  $model->expiration_at;
            },
            'filter' => false,
            'label' => 'Дата окончания'
        ],
    ],
]);
?>
