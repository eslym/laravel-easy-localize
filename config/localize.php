<?php

$available = array_map('basename', glob(resource_path('lang/*/')));

return compact('available');
