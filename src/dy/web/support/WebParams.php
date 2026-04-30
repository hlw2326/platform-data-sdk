<?php

namespace Hlw\Collect\Dy\Web\Support;

class WebParams
{
    public static function build(string $versionCode = '170400', string $versionName = '29.1.0'): array
    {
        return [
            'update_version_code' => $versionCode,
            'pc_client_type' => '1',
            'pc_libra_divert' => 'Windows',
            'support_h265' => '1',
            'support_dash' => '1',
            'cpu_core_num' => '12',
            'version_code' => $versionCode === '170400' ? '290100' : $versionCode,
            'version_name' => $versionName,
            'browser_online' => 'true',
            'engine_name' => 'Blink',
            'engine_version' => '146.0.0.0',
            'os_name' => 'Windows',
            'os_version' => '10',
            'device_memory' => '8',
            'platform' => 'PC',
            'downlink' => '10',
            'effective_type' => '4g',
            'round_trip_time' => '50',
        ];
    }
}
