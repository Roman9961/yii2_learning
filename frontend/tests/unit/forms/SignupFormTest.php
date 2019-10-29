<?php
namespace frontend\tests\unit\models;

use common\fixtures\UserFixture;
use frontend\forms\SignupForm;
use frontend\services\auth\SignupService;

class SignupFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;


    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ]);
    }

    public function testCorrectSignup()
    {
        $form = new SignupForm([
            'username' => 'some_username',
            'email' => 'some_email@example.com',
            'password' => 'some_password',
        ]);

        $user = (new SignupService())->signup($form);
        expect($user)->true();

        /** @var \common\entities\User $user */
        $user = $this->tester->grabRecord('common\entities\User', [
            'username' => 'some_username',
            'email' => 'some_email@example.com',
            'status' => \common\entities\User::STATUS_INACTIVE
        ]);

        $this->tester->seeEmailIsSent();

        $mail = $this->tester->grabLastSentEmail();

        expect($mail)->isInstanceOf('yii\mail\MessageInterface');
        expect($mail->getTo())->hasKey('some_email@example.com');
        expect($mail->getFrom())->hasKey(\Yii::$app->params['supportEmail']);
        expect($mail->getSubject())->equals('Account registration at ' . \Yii::$app->name);
        expect($mail->toString())->stringContainsString($user->verification_token);
    }

    public function testNotCorrectSignup()
    {
        $form = new SignupForm([
            'username' => 'troy.becker',
            'email' => 'nicolas.dianna@hotmail.com',
            'password' => 'some_password',
        ]);

        expect_not((new SignupService())->signup($form));
        expect_that($form->getErrors('username'));
        expect_that($form->getErrors('email'));

        expect($form->getFirstError('username'))
            ->equals('This username has already been taken.');
        expect($form->getFirstError('email'))
            ->equals('This email address has already been taken.');
    }
}
