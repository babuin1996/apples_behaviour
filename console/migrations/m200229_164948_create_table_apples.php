<?php

use yii\db\Migration;

/**
 * Class m200229_164948_create_table_apples
 */
class m200229_164948_create_table_apples extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         *  created_at: Обычно для хранения даты\время использую datetime()
         *  но по условиям нужен unixTmeStamp, причём рандомный
         *
         *  status: поскольку в ТЗ предполагается только 2 статуса можно бы было
         *  использовать boolean, но boolean ставится обычно на флаги, например is_dropped,
         *  а т.к. статусов у нас в будущем может быть много, то при добавлении это вызовет трабл
         */
        $this->createTable('apples', [
            'id' => $this->primaryKey()->unsigned(),
            'color' => $this->string(255),
            'status' => $this->integer()->defaultValue(0),
            'size' => $this->float()->unsigned(),
            'created_at' => $this->integer(),
            'dropped_at' => $this->dateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200229_164948_create_table_apples cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200229_164948_create_table_apples cannot be reverted.\n";

        return false;
    }
    */
}
