<?php

return [
    'resource' => [
        'label' => 'Link',
        'plural_label' => 'Links',
    ],

    'form' => [
        'original_url' => 'Original URL',
        'original_url_hint' => 'Enter the URL you want to shorten.',
    ],

    'table' => [
        'short_url' => 'Short link',
        'original_url' => 'Original URL',
        'clicks_count' => 'Clicks',
        'created_at' => 'Created at',
        'copy_success' => 'Copied to clipboard',
    ],

    'filters' => [
        'from' => 'Created from',
        'until' => 'Created until',
    ],

    'actions' => [
        'create' => 'Create link',
        'create_another' => 'Create & create another',
        'statistics' => 'View statistics',
        'close' => 'Close',
    ],

    'delete' => [
        'heading' => 'Delete this link?',
        'description' => 'All click statistics for this link will also be deleted.',
    ],

    'empty' => [
        'heading' => 'No links yet',
        'description' => 'Create your first short link.',
    ],

    'pages' => [
        'create' => [
            'heading' => 'Create link',
        ],
        'list' => [
            'heading' => 'My links',
        ],
    ],

    'statistics' => [
        'short_url' => 'Short link',
        'total_clicks' => 'Total clicks',
        'history' => 'Click history',
        'history_description' => 'The latest clicks are shown in reverse chronological order.',
        'ip_address' => 'IP address',
        'user_agent' => 'User Agent',
        'clicked_at' => 'Clicked at',
        'no_clicks' => 'This link has not received any clicks yet.',
    ],

    'notifications' => [
        'created' => 'Link created successfully',
        'unauthorized' => [
            'title' => 'Authentication required',
            'body' => 'You must sign in to create a link.',
        ],
        'generation_failed' => [
            'title' => 'Could not generate link',
        ],
        'database_error' => [
            'title' => 'Database error',
            'body' => 'Could not save the link. Please try again later.',
        ],
        'unexpected_error' => [
            'title' => 'Something went wrong',
            'body' => 'Could not create the link. Please contact the administrator.',
        ],
    ],

    'errors' => [
        'generation_timeout' => 'The link generation request timed out.',
        'database_full' => 'No available short codes remain. The database is full.',
    ],
];
