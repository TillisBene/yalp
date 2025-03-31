<?php

namespace utils;

class GetDevice {
    private $device_id;
    private $user_id;
    private $device_name;
    private $device_type;
    private $last_ip;
    private $last_login;
    private $is_active;
    private $user_agent;
    private $refresh_token;

    public function __construct() {
        $this->last_login = date('Y-m-d H:i:s');
        $this->is_active = true;
    }

    // Getters
    public function getDeviceId() { return $this->device_id; }
    public function getUserId() { return $this->user_id; }
    public function getDeviceName() { return $this->device_name; }
    public function getDeviceType() { return $this->device_type; }
    public function getLastIp() { return $this->last_ip; }
    public function getLastLogin() { return $this->last_login; }
    public function getIsActive() { return $this->is_active; }
    public function getUserAgent() { return $this->user_agent; }
    public function getRefreshToken() { return $this->refresh_token; }

    // Setters
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setDeviceName($device_name) { $this->device_name = $device_name; }
    public function setDeviceType($device_type) { $this->device_type = $device_type; }
    public function setLastIp($last_ip) { $this->last_ip = $last_ip; }
    public function setLastLogin($last_login) { $this->last_login = $last_login; }
    public function setIsActive($is_active) { $this->is_active = $is_active; }
    public function setUserAgent($user_agent) { $this->user_agent = $user_agent; }
    public function setRefreshToken($refresh_token) { $this->refresh_token = $refresh_token; }

    public function toArray() {
        return [
            'device_id' => $this->device_id,
            'user_id' => $this->user_id,
            'device_name' => $this->device_name,
            'device_type' => $this->device_type,
            'last_ip' => $this->last_ip,
            'last_login' => $this->last_login,
            'is_active' => $this->is_active,
            'user_agent' => $this->user_agent,
            'refresh_token' => $this->refresh_token
        ];
    }

    public static function fromArray($data) {
        $device = new self();
        if (isset($data['device_id'])) $device->device_id = $data['device_id'];
        if (isset($data['user_id'])) $device->user_id = $data['user_id'];
        if (isset($data['device_name'])) $device->device_name = $data['device_name'];
        if (isset($data['device_type'])) $device->device_type = $data['device_type'];
        if (isset($data['last_ip'])) $device->last_ip = $data['last_ip'];
        if (isset($data['last_login'])) $device->last_login = $data['last_login'];
        if (isset($data['is_active'])) $device->is_active = $data['is_active'];
        if (isset($data['user_agent'])) $device->user_agent = $data['user_agent'];
        if (isset($data['refresh_token'])) $device->refresh_token = $data['refresh_token'];
        return $device;
    }
}