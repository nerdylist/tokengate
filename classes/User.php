<?php

require_once __DIR__ . '/Model.php';

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['email', 'password_hash', 'name', 'is_admin', 'is_verified', 'last_login'];

    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function verifyPassword($inputPassword, $userData)
    {
        if (!isset($userData['password_hash'])) {
            return false;
        }
        return password_verify($inputPassword, $userData['password_hash']);
    }

    public static function createUser($email, $password, $name = '', $isAdmin = false)
    {
        $user = new self();
        
        $existing = $user->findByEmail($email);
        if ($existing) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        if (empty($name)) {
            $name = explode('@', $email)[0];
        }

        $data = [
            'email' => $email,
            'password_hash' => $passwordHash,
            'name' => $name,
            'is_admin' => $isAdmin ? 1 : 0,
            'is_verified' => 0
        ];

        $userId = $user->create($data);
        
        if ($userId) {
            return $user->find($userId);
        }
        
        return false;
    }

    public function isAdmin($userData)
    {
        return isset($userData['is_admin']) && $userData['is_admin'] == 1;
    }

    public function updateLastLogin($userId)
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }
}
