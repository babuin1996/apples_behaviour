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

                return $model::getApplesStatuses()[$model['status']];
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

                $eatButton =
                    Html::beginForm('/apples/eat').
                    Html::submitButton('Откусить', ['class' => 'btn btn-success']).' '.
                    Html::input('text', 'percent', 25, ['style' => 'width: 10%;']).' '.
                    Html::hiddenInput('id', $model['id']).
                    Html::label('% от яблока' ).
                    Html::endForm();

                $dropButton = Html::a('Уронить', Url::toRoute(['/apples/drop']), [
                    'class' => 'btn btn-primary',
                    'data-method' => 'POST',
                    'data-params' => ['id' => $model['id']],
                    'data' => [
                        'confirm' => 'Уронить это яблоко?',
                    ],
                ]);

                if ($model['status'] === Apples::STATUS_IS_HANGING) {

                    return $dropButton;
                }

                if ($model['status'] === Apples::STATUS_IS_DROPPED) {

                    return $eatButton;
                }

                return '-';
            }
        ],
    ],
])
?>