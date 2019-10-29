<?php


namespace frontend\services\auth;


use common\entities\User;
use frontend\forms\PasswordResetRequestForm;
use frontend\forms\ResetPasswordForm;
use frontend\forms\SignupForm;
use Yii;
use yii\base\InvalidArgumentException;

class PasswordResetService
{
    /**
     * @var User
     */
    private $_user;
    private $supportEmail;

    public function __construct($supportEmail)
    {
        $this->supportEmail = $supportEmail;
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function request(PasswordResetRequestForm $form)
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $form->email,
        ]);

        if (!$user) {
            return false;
        }

        $user->generatePasswordResetToken();

        if (!$user->save()) {
            throw new \RuntimeException('Saving password reset token error.');
        }

        if(!$this->sendEmail($user, $form)){
            throw new \RuntimeException('Sending error.');
        }
    }

    public function validateToken(string $token)
    {
        if (empty($token) || !is_string($token)) {
            throw new \DomainException('Password reset token cannot be blank.');
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new \DomainException('Wrong password reset token.');
        }
    }

    public function reset(ResetPasswordForm $form)
    {
        $user = $this->_user;
        $user->setPassword($form->password);
        $user->removePasswordResetToken();

        if(!$user->save(false)){
            throw new \RuntimeException('Saving error.');
        }
    }

    private function sendEmail(User $user, PasswordResetRequestForm $form)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom($this->supportEmail)
            ->setTo($form->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();        return ;

    }
}