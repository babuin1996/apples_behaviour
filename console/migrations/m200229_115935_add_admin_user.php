<?php

use yii\db\Migration;

/**
 * Class m200229_115935_add_admin_user
 */
class m200229_115935_add_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('user',
            [
                'username' => 'admin',
                'email' => 'vstrukoff@yandex.ru',
                'auth_key' => new \yii\db\Expression('LEFT(UUID(), 32)'),
                'status' => 1,
                'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
                'created_at' => time(),
                'updated_at' => time(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('users', ['username' => 'admin']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200229_115935_add_admin_user cannot be reverted.\n";

        return false;
    }
    */
}
