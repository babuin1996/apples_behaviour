<?php
namespace backend\widgets\apples_table;

class ApplesTable extends \yii\base\Widget
{
    public $dataProvider;

    public function run()
    {

        return $this->render('table', ['dataProvider' => $this->dataProvider]);
    }
}