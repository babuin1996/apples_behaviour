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

    // Устанавливаем диапазон кол-ва яблок при генерации
    const AMOUNT_MIN_VALUE = 3;
    const AMOUNT_MAX_VALUE = 15;

    private $created_at;
    private $dropped_at;

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
            ['size', 'in','range'=>range(0,1)],
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

    public function beforeValidate()
    {
        if(parent::beforeValidate()) {

            if (!$this->isNewRecord) {

                $model = self::findOne($this->id);

                // Валидация при поедании яблока
                if ($model->size != $this->size) {

                    if ($this->status === self::STATUS_IS_HANGING) {

                        $this->addError('size', 'Нельзя есть яблоко, которое ещё находится на дереве.');
                    } else if ($this->status === self::STATUS_IS_DROPPED) {

                        if ($this->dropped_at && $this->checkIfExpired()) {

                            $this->addError('size', 'Нельзя есть испорченное яблоко.');
                        } else {

                            $this->addError('size', 'Нельзя есть яблоко, которое неизвестно когда упало, т.к. может быть испорчено.');
                        }
                    }
                }

                if ($model->status === self::STATUS_IS_DROPPED && $this->status == self::STATUS_IS_HANGING) {

                    $this->addError('status', 'Яблоко нельзя повесить обратно на дерево.');
                }

                if (($model->size - $this->size) < 0) {

                    $this->addError('size', 'Невозможно съесть больше, чем '.$model->getSizeInPercent().' от яблока.');
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
             *  2 - Иметь рандомный UnixTimeStamp для поля created_at
             *  3 - Иметь случайный цвет
             *  4 - Быть целым (вариант, что птички поклюют не рассматирваем)
             */
            if ($this->isNewRecord) {

                $this->color = $this->getRandomColorFromApplesColors();
                $this->defineRandomTimeStampForCreatedAt();
                $this->status = self::STATUS_IS_HANGING;
                $this->size = 1;
            }

            // Если яблоко упало - записать время падения
            if ($this->status === self::STATUS_IS_DROPPED || !$this->dropped_at) {

                $this->dropped_at = new Expression('NOW()');
            }

            // Если от яблока ничего не осталось по каким-то причинам - удаляем
            if ($this->size === 0) {

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
        ];
    }

    /**
     * Проверяет, испорчено ли яблоко
     *
     * @return boolean
     */
    private function checkIfExpired()
    {

        return time() - strtotime($this->dropped_at) >= 5*60*60;
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
     * @return boolean
     */
    private function defineRandomTimeStampForCreatedAt()
    {
        if (!$this->created_at) {

            $this->created_at = rand(1, time());

            return true;
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
        $this->size -= $percent/100;

        if ($this->validate() && $this->save()) {

            return true;
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
}
