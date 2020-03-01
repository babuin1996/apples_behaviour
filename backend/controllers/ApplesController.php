<?php

namespace backend\controllers;

use app\models\Apples;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ApplesController extends \yii\web\Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'errors' => [],
        ]);
    }

    public function actionEat($id, $percent)
    {
        $apple = Apples::findOne($id);
        $apple->eat($percent);

        return $this->render('index', ['errors' => $apple->getErrors()]);
    }

    public function actionDrop($id)
    {
        $apple = Apples::findOne($id);
        $apple->fallToGround();

        return $this->redirect('/');
    }

    public function actionGenerate()
    {
        // Удаляем старые яблоки
        Apples::deleteAll();

        // Определяем кол-во яблок
        $amount = rand(Apples::AMOUNT_MIN_VALUE, Apples::AMOUNT_MAX_VALUE);

        for ($i = 0; $i < $amount; $i++) {

            $apple = new Apples;
            $apple->save();
        }

        return $this->redirect('/');
    }

}
