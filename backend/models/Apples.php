<?php

namespace app\models;

use Yii;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "apples".
 *
 * @property int $id
 * @property string|null $color
 * @property int|null $status
 * @property float|null $size
 * @property int|null $created_at
 * @property string|null $dropped_at
 */
class Apples extends \yii\db\ActiveRecord
{
    const STATUS_IS_HANGING = 0;
    const STATUS_IS_DROPPED = 1;
    const STATUS_IS_EXPIRED = 2;

    // Устанавливаем диапазон кол-ва яблок при генерации
    const APPLE_LIFETIME = 5*60*60;

    // Устанавливаем диапазон кол-ва яблок при генерации
    const AMOUNT_MIN_VALUE = 3;
    const AMOUNT_MAX_VALUE = 15;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apples';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'created_at'], 'integer'],
            [['size'], 'number'],
            [['color'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'color' => 'Color',
            'status' => 'Status',
            'size' => 'Size',
            'created_at' => 'Created At',
            'dropped_at' => 'Dropped At',
        ];
    }

    public function init()
    {
        self::setExpiredStatusForExpiredApples();

        parent::init();
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {

            if (!$this->isNewRecord) {

                $model = self::findOne($this->id);

                // Валидация при поедании яблока
                if ($model->size != $this->size) {

                    if ($this->status === self::STATUS_IS_HANGING) {

                        $this->addError('size', 'Нельзя есть яблоко, которое ещё находится на дереве.');
                    } else if ($this->status !== self::STATUS_IS_HANGING) {

                        if ($this->dropped_at) {

                            if ($this->checkIfExpired()) {

                                $this->addError('size', 'Нельзя есть испорченное яблоко.');
                            }
                        } else {

                            $this->addError('size', 'Нельзя есть яблоко, которое неизвестно когда упало, т.к. может быть испорчено.');
                        }
                    }
                }

                if ($model->status === self::STATUS_IS_DROPPED && $this->status == self::STATUS_IS_HANGING) {

                    $this->addError('status', 'Яблоко нельзя повесить обратно на дерево.');
                }

                if (($this->size) < 0) {

                    $this->addError('size', 'Невозможно съесть больше, чем '.$model->getSizeInPercent().' от этого яблока.');
                }

                if (round($model->size, 0) != 0 && round($model->size, 0) != 1) {

                    $this->addError('size', 'Яблоко имеет неправильный размер.');
                }
            }

            return true;
        }

        return false;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            /*
             * Если яблоко новое, то оно обязатольно должно:
             *  1 - Висеть на дереве
             *  2 - Иметь рандомный UnixTimeStamp для поля created_at (вывел в отдельный метод на будущее)
             *  3 - Иметь случайный цвет
             *  4 - Быть целым (вариант, что птички поклюют не рассматирваем)
             */
            if ($this->isNewRecord) {

                $this->color = $this->getRandomColorFromApplesColors();
                $this->created_at = $this->getValueForCreatedAt();
                $this->status = self::STATUS_IS_HANGING;
                $this->size = 1;
            }

            // Если яблоко упало - записать время падения
            if ($this->status === self::STATUS_IS_DROPPED && !$this->dropped_at) {

                $this->dropped_at = new Expression('NOW()');
            }

            // Если от яблока ничего не осталось по каким-то причинам - удаляем
            if ($this->size == 0) {

                $this->delete();
            }

            return true;
        }

        return false;
    }

    /**
     * Возвращает статусы яблока
     *
     * @return array
     */
    public static function getApplesStatuses()
    {
        return [
            self::STATUS_IS_HANGING => 'Висит на дереве',
            self::STATUS_IS_DROPPED => 'Упало',
            self::STATUS_IS_EXPIRED => 'Испорчено',
        ];
    }

    /**
     * Проверяет, испорчено ли яблоко
     *
     * @return boolean
     */
    private function checkIfExpired()
    {

        return time() - strtotime($this->dropped_at) >= self::APPLE_LIFETIME;
    }

    /**
     * Возвращает остаток от яблока в %
     *
     * @return string
     */
    private function getSizeInPercent()
    {

        return ($this->size*100).'%';
    }

    /**
     * Присваивает created_at рандомный UnixTimeStamp
     *
     * @return mixed
     */
    private function getValueForCreatedAt()
    {
        if (!$this->created_at) {

            return rand(1, time());
        }

        return false;
    }

    /**
     * Съесть яблоко
     *
     * @var $percent integer Процент от объёма яблока
     *
     * @throws \Throwable
     * @return boolean
     */
    public function eat($percent)
    {
        if (ctype_digit($percent)) {

            $this->size -= $percent/100;

            if ($this->validate() && $this->save()) {

                return true;
            }
        } else {

            $this->addError('size', 'Размер % от яблока для укуса должен быть целым числом.');
        }

        return false;
    }

    /**
     * Уронить яблоко
     *
     * @return boolean
     */
    public function fallToGround()
    {
        if ($this->status === self::STATUS_IS_HANGING) {

            $this->status = self::STATUS_IS_DROPPED;

            if ($this->validate() && $this->save()) {

                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает массив цветов яблок
     *
     * @return array
     */
    private function getApplesColors()
    {
        return [
            'green',
            'lime',
            'yellow',
            'gold',
            'indianred',
            'red',
        ];
    }

    /**
     * Возвращает случайный цвет из списка цветов (вне зависимости от их количества)
     *
     * @return string
     */
    private function getRandomColorFromApplesColors()
    {
        $colors = $this->getApplesColors();

        return $colors[rand(0, count($colors)-1)];
    }

    /**
     * Установить статус "Испорчено" для испорченых яблок
     *
     * @return void
     */
    private static function setExpiredStatusForExpiredApples()
    {
        self::updateAll(
            [
                'status' => self::STATUS_IS_EXPIRED
            ],
            ['AND',
                ['status' => self::STATUS_IS_DROPPED], // Яблоко, находящееся на дереве испортиться не может
                ['<=', 'dropped_at', new Expression('NOW() - INTERVAL '.self::APPLE_LIFETIME.' SECOND')]
            ]
        );
    }
}
