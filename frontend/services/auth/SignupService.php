<?php


namespace frontend\services\auth;


use common\entities\User;
use frontend\forms\SignupForm;
use Yii;

class SignupService
{
    public function signup(SignupForm $form): bool
    {
        if (!$form->validate()) {
            return false;
        }

        $user = User::signup(
            $form->username,
            $form->email,
            $form->password
        );

        try{
            $user->save();
            $this->sendEmail($user, $form);
        }catch (\Swift_TransportException $e){
            throw new \RuntimeException('Mail error');
        }catch (\Exception $e){
            throw new \RuntimeException('Saving error');
        }

        return true;

    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user, SignupForm $form)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($form->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}