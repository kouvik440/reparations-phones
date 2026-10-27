<?php

return [
    'ssr' => ['enabled' => false],
    'ensure_pages_exist' => true,
    'page_paths' => [resource_path('js/Pages')],
    'page_extensions' => ['jsx'],
    'testing' => [
        'ensure_pages_exist' => true,
        'page_paths' => [resource_path('js/Pages')],
        'page_extensions' => ['jsx'],
    ],
];
