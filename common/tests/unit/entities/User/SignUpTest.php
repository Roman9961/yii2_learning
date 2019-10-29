<?php

namespace common\tests\unit\entities\User;

use Codeception\Test\Unit;
use common\entities\User;

class SignUpTest extends Unit
{
    public function testSuccess()
    {
        $user = User::signup(
            $username = 'username',
            $email = 'email@domain.com',
            $password = 'password'
        );

        $user->save();

        $this->assertEquals($username, $user->username);
        $this->assertEquals($email, $user->email);
        $this->assertNotEmpty($user->password_hash);
        $this->assertNotEquals($password, $user->password_hash);
        $this->assertNotEmpty($user->created_at);
        $this->assertNotEmpty($user->auth_key);
        $this->assertNotEmpty($user->verification_token);
        $this->assertFalse($user->isActive());
    }
}