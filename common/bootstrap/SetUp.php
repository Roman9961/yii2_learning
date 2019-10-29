<?php

namespace common\bootstrap;

use frontend\services\auth\PasswordResetService;
use yii\base\Application;
use yii\base\BootstrapInterface;

class SetUp implements BootstrapInterface
{

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $container = \Yii::$container;

        $container->setSingleton(PasswordResetService::class, [], [
            [$app->params['supportEmail'] => $app->name . ' robot']
        ]);

//        $container->setSingleton(PasswordResetService::class, function () use ($app){
//            return new PasswordResetService([$app->params['supportEmail'] => $app->name . ' robot']);
//        });
    }
}