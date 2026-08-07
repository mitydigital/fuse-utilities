<?php

return [
    'blade' => [
        'render_comments' => (bool) env('RENDER_BLADE_COMMENTS', env('APP_ENV', 'production') !== 'production'),
    ],

    'image' => [
        'default_width' => 350,

        'breakpoints' => [
            'sm' => 640,
            'md' => 768,
            'lg' => 1024,
            'xl' => 1280,
            '2xl' => 1536,
        ],

        'default_fit' => 'crop_focal',
        'densities' => [1, 2],
    ],
];
