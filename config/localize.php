<?php

$available = array_map('basename', glob(resource_path('lang/*/')));

$names = [
    'en' => 'English',
];

return compact('available', 'names');
