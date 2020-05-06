<?php

namespace frontend\modules\order\widgets;

use Yii;
use common\models\search\TaskSearch;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\Url;
use frontend\modules\task\controllers\TaskController;
use frontend\modules\contractor\controllers\ContractorController;
use yii\jui\Widget;

/**
 * alternative main menu (on top)
 */
class ProductionTaskWidget extends Widget
{

    /**
     * related model
     * @var Query
     */
    public $query;

    /**
     * view file
     * @var string 
     */
    public $view = 'default';

    public function run()
    {
        if (!$this->query) {
            throw new Exception('query must be exist');
        }
        $dataProvider = new \yii\data\ActiveDataProvider(['query' => $this->query]);
        return $this->render('task/' . $this->view, [
                'dataProvider' => $dataProvider,
        ]);
    }
}
