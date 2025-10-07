<?php

namespace App\Models;

use App\Services\FirebaseService;
use Illuminate\Support\Collection;

class Admin
{
    protected $firebaseService;
    protected $fillable = [
        'email', 'password', 'name', 'role', 'status', 'permissions'
    ];

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public static function create(array $data)
    {
        $instance = new static();
        return $instance->firebaseService->createAdmin($data);
    }

    public static function find($id)
    {
        $instance = new static();
        return $instance->firebaseService->getAdmin($id);
    }

    public static function findByEmail($email)
    {
        $instance = new static();
        return $instance->firebaseService->getAdminByEmail($email);
    }

    public static function findByUsername($username)
    {
        $instance = new static();
        return $instance->firebaseService->getAdminByUsername($username);
    }

    public static function createDefaultAdmin()
    {
        $instance = new static();
        return $instance->firebaseService->createDefaultAdmin();
    }

    public static function all()
    {
        $instance = new static();
        $admins = $instance->firebaseService->getAllAdmins();
        return collect($admins);
    }

    public static function updateAdmin($id, array $data)
    {
        $instance = new static();
        return $instance->firebaseService->updateAdmin($id, $data);
    }

    public static function deleteAdmin($id)
    {
        $instance = new static();
        return $instance->firebaseService->deleteAdmin($id);
    }

    public static function authenticate($identifier, $password)
    {
        // Try to find admin by username first, then by email
        $admin = self::findByUsername($identifier);
        if (!$admin) {
            $admin = self::findByEmail($identifier);
        }
        
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        
        return null;
    }

    public static function logActivity($adminId, $action, $description)
    {
        $instance = new static();
        return $instance->firebaseService->logActivity([
            'admin_id' => $adminId,
            'action' => $action,
            'description' => $description
        ]);
    }
}
