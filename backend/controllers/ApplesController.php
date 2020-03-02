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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'eat' => ['POST'],
                    'drop' => ['POST'],
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
        return $this->render('index', ['errors' => []]);
    }

    public function actionEat()
    {
        $post = \Yii::$app->request->post();
        $apple = Apples::findOne($post['id']);

        if ($post['percent']) {
            $apple->eat($post['percent']);
        }

        return $this->render('index', ['errors' => $apple->getErrors()]);
    }

    public function actionDrop()
    {
        $post = \Yii::$app->request->post();
        $apple = Apples::findOne($post['id']);
        $apple->fallToGround();

        return $this->render('index', ['errors' => $apple->getErrors()]);
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

        return $this->redirect('index');
    }

}
