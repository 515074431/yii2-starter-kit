<?php
use common\models\AdminUser;
use yii\db\Migration;

class m170322_100859_seed_data extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%admin_user}}', [
            'id' => 1,
            'username' => 'webmaster',
            'email' => 'webmaster@example.com',
            'mobile' => '15012345678',
            'password_hash' => Yii::$app->getSecurity()->generatePasswordHash('webmaster'),
            'auth_key' => Yii::$app->getSecurity()->generateRandomString(),
            'access_token' => Yii::$app->getSecurity()->generateRandomString(40),
            'status' => AdminUser::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time()
        ]);
        $this->insert('{{%admin_user}}', [
            'id' => 2,
            'username' => 'manager',
            'email' => 'manager@example.com',
            'mobile' => '15112345678',
            'password_hash' => Yii::$app->getSecurity()->generatePasswordHash('manager'),
            'auth_key' => Yii::$app->getSecurity()->generateRandomString(),
            'access_token' => Yii::$app->getSecurity()->generateRandomString(40),
            'status'=> AdminUser::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time()
        ]);


        $this->insert('{{%admin_user_profile}}', [
            'user_id'=>1,
            'locale'=>'zh-CN',//Yii::$app->sourceLanguage,
            'province' => '河南省',
            'city' => '开封市',
            'area' => '龙亭区'
        ]);
        $this->insert('{{%admin_user_profile}}', [
            'user_id'=>2,
            'locale'=>Yii::$app->sourceLanguage
        ]);

    }

    public function safeDown()
    {


        $this->delete('{{%admin_user_profile}}', [
            'user_id' => [1, 2]
        ]);

        $this->delete('{{%admin_user}}', [
            'id' => [1, 2]
        ]);
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
