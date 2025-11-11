<?php

declare(strict_types=1);

namespace IndianConsular\Models;

class SystemConfig extends BaseModel
{
    protected string $table = 'system_config';
    protected string $primaryKey = 'id';

    public function getConfig(string $key): ?array
    {
        return $this->findBy('config_key', $key);
    }

    public function updateConfig(string $key, array $value): bool
    {
        $json = json_encode($value);
        if ($json === false) return false;

        $existing = $this->getConfig($key);
        if ($existing) {
            return $this->updateBy('config_key', $key, ['config_value' => $json]);
        } else {
            return $this->insert([
                'config_key' => $key,
                'config_value' => $json,
                'is_public' => 0
            ]) > 0;
        }
    }
}