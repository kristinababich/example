<?php

namespace frontend\modules\order\controllers;

use common\models\CommercialOffer;
use common\models\RequestItemReply;
use common\models\search\RequestItemReplySearch;
use common\models\search\RequestItemSearch;
use frontend\modules\order\forms\RequestForm;
use frontend\modules\order\services\CreateOrderService;
use frontend\modules\order\services\RequestFilterService;
use Yii;
use common\models\Request;
use common\models\search\RequestSearch;
use frontend\access\EmployeeController;
use yii\db\Exception;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RequestController implements the CRUD actions for Request model.
 */
class RequestController extends EmployeeController
{
    public const TAB_MAIN = 'main';
    public const TAB_REPLIES = 'replies';
    public const TAB_OFFERS = 'offers';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Request models.
     * @return mixed
     */
    public function actionIndex($type = null)
    {
        $searchModel = new RequestSearch();
        $searchModel->type_request = $type;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'type' => $type,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Request model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Request model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($system_type_id = Request::SYSTEM_TYPE_REQUEST)
    {
        $model = new RequestForm();
        if ($model->saveData(Yii::$app->request->post())) {
            return $this->redirect((Url::previous(self::class) ? Url::previous(self::class) : ['index']));
        }
        if (Yii::$app->request->referrer && strpos(Yii::$app->request->referrer, 'index') !== false) {
            Url::remember(Yii::$app->request->referrer, self::class);
        }
        return $this->render('create', [
            'model' => $model,
            'tab' => self::TAB_MAIN
        ]);
    }

    /**
     * Finds the Request model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Request the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Request::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
