<?php

namespace app\models;

use Yii;
use yii\db\Exception;

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
            [['dropped_at'], 'safe'],
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

            $model = self::findOne($this->id);

            // Валидация при поедании яблока
            if ($model->size != $this->size) {

                if ($this->status == self::STATUS_IS_HANGING) {

                    $this->addError('size', 'Нельзя есть яблоко, которое ещё находится на дереве.');
                } else if ($this->status == self::STATUS_IS_DROPPED) {

                    if ($this->dropped_at && $this->checkIfExpired()) {

                        $this->addError('size', 'Нельзя есть испорченное яблоко.');
                    } else {

                        $this->addError('size', 'Нельзя есть яблоко, которое неизвестно когда упало, т.к. может быть испорчено.');
                    }
                }
            }

            if (($model->size - $this->size) < 0) {

                $this->addError('size', 'Невозможно съесть больше, чем '.$model->getSizeInPercent().' от яблока.');
            }

            return true;
        }

        return false;
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
     * Съесть яблоко: Если не гнилое - удаляет, в противном случаа вылетает исключение
     *
     * @var $percent integer Процент от объёма яблока
     *
     * @throws \Throwable
     * @return boolean
     */
    public function eat($percent)
    {
        $this->size -= $percent/100;

        if ($this->validate()) {

            $this->size > 0 ? $this->save() : $this->delete();
        }

        return false;
    }
}
