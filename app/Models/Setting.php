<?php

namespace App\Models;

use App\Services\FirebaseService;

class Setting
{
    protected $firebaseService;
    
    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public static function get($key, $default = null)
    {
        $instance = new static();
        $setting = $instance->firebaseService->getSetting($key);
        return $setting ? $setting['value'] : $default;
    }

    public static function set($key, $value)
    {
        $instance = new static();
        return $instance->firebaseService->setSetting($key, $value);
    }

    public static function all()
    {
        $instance = new static();
        $settings = $instance->firebaseService->getAllSettings();
        
        // Convert to simple key-value pairs
        $result = [];
        foreach ($settings as $key => $data) {
            $result[$key] = $data['value'] ?? null;
        }
        
        return $result;
    }

    public static function updateMultiple(array $settings)
    {
        $instance = new static();
        $success = true;
        
        foreach ($settings as $key => $value) {
            if (!$instance->firebaseService->setSetting($key, $value)) {
                $success = false;
            }
        }
        
        return $success;
    }

    // Convenience methods for common settings
    public static function getSiteName()
    {
        return self::get('site_name', 'LodgeEase');
    }

    public static function getCurrency()
    {
        return self::get('currency', 'USD');
    }

    public static function getTaxRate()
    {
        return self::get('tax_rate', 10);
    }

    public static function getCheckInTime()
    {
        return self::get('check_in_time', '14:00');
    }

    public static function getCheckOutTime()
    {
        return self::get('check_out_time', '11:00');
    }

    public static function getCancellationPolicy()
    {
        return self::get('cancellation_policy', '24 hours before check-in');
    }

    public static function getContactEmail()
    {
        return self::get('contact_email', 'contact@lodgeease.com');
    }

    public static function getContactPhone()
    {
        return self::get('contact_phone', '+1-234-567-8900');
    }
}
