<?php

use app\models\Apples;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => 'Цвет',
            'format' => 'raw',
            'value' => function($model) {

                return '<i class="glyphicon glyphicon-apple" style="color: '.$model['color'].';" title="'.$model['color'].'" ></i>';
            }
        ],
        [
            'label' => 'Остаток',
            'format' => 'raw',
            'value' => function($model) {

                $percent = $model['size']*100;

                return $percent.'% <div class="progress">
                          <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$percent.'%"></div>
                        </div>';
            }
        ],
        [
            'label' => 'Статус',
            'format' => 'raw',
            'value' => function($model) {

                return Apples::getApplesStatuses()[$model['status']];
            }
        ],
        [
            'label' => 'Когда упало',
            'format' => 'raw',
            'value' => function($model) {

                return $model['dropped_at'] ? $model['dropped_at'] : '-';
            }
        ],
        [
            'label' => '',
            'format' => 'raw',
            'value' => function($model) {

                return $model['status'] === Apples::STATUS_IS_HANGING ?
                    Html::a('Уронить', Url::toRoute(['/apples/drop', 'id' => $model['id']]), ['class' => 'btn btn-primary']) :
                    Html::a('Откусить', Url::toRoute(['/apples/eat', 'id' => $model['id'], 'percent' => 25]), ['class' => 'btn btn-primary']);
            }
        ],
    ],
])
?>