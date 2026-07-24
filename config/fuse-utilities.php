<?php

return [
    'blade' => [
        'render_comments' => (bool) env('RENDER_BLADE_COMMENTS', env('APP_ENV', 'production') !== 'production'),
    ],
];
