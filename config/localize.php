<?php

$available = array_map('basename', glob(resource_path('lang/*/')));

$settings = [
    'en' => [
        'name' => 'English',
        'display' => 'English'
    ],
    'en-US' => [
        'name' => 'US English',
        'group' => 'en',
        'display' => 'English (US)',
    ],
    'en-UK' => [
        'name' => 'UK English',
        'group' => 'en',
        'display' => 'English (UK)',
    ],
    'zh' => [
        'name' => 'Chinese',
        'display' => '中文',
    ],
    'zh-CN' => [
        'name' => 'Simplified Chinese',
        'group' => 'zh',
        'display' => '简体中文',
    ],
    'zh-TW' => [
        'name' => 'Traditional Chinese',
        'group' => 'zh',
        'display' => '繁體中文',
    ],
];

return compact('available', 'settings');
