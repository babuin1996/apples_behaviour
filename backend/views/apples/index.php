<?php

use app\models\Apples;
use backend\widgets\apples_table\ApplesTable;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Яблоки';

/*
 * Представление index вызывается в нескольких методах контроллера.
 * Чтобы каждый рез не передовать dataProvider в представление, разместил его здесь
 */
$dataProvider = new ActiveDataProvider([
    'query' => Apples::find(),
]);
?>
<div class="site-index">
    <div class="alert alert-warning" role="alert">
        <b>Внимание!</b> При генерации новых яблок, будут <b>удалены</b> все старые.
    </div>
    <?php foreach ($errors as $error) : ?>
        <div class="alert alert-danger" role="alert">
            <?= $error[0] ?>
        </div>
    <?php endforeach; ?>
    <?= Html::a('<i class="glyphicon glyphicon-apple"></i> Сгенерировать яблоки', Url::to('/apples/generate'), ['class' => 'btn btn-primary']) ?>
    <br>
    <br>
    <?= ApplesTable::widget(['dataProvider' => $dataProvider]) ?>
</div>
