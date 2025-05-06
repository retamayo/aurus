<?php

return [
    'group' => 'Settings',
    'back' => 'Back',
    'actions' => [
        'send' => [
            'label' => 'Send Notification',
            'form' => [
                'template_id' => 'Template',
                'model_id' => 'User',
                'model_type' => 'User Type',
                'privacy' => 'Privacy',
                'public' => 'Public',
                'private' => 'Private',
            ],
            'notification' => 'Notification has been sent',
        ],
    ],
    'templates' => [
        'title' => 'Notifications',
        'single' => 'Template',
        'create' => 'Create Template',
        'edit' => 'Edit Template',
        'actions' => [
            'clone' => 'Clone',
            'try' => 'Try',
            'clone-notification' => 'Template has been cloned',
            'try-notification' => 'Template has been sent',
            'try-notification-danger' => 'Sorry something warrning when sending the notification',
        ],
        'form' => [
            'image' => 'Image',
            'name' => 'Name',
            'key' => 'Key',
            'title' => 'Title',
            'body' => 'Body',
            'url' => 'Url',
            'icon' => 'Icon',
            'providers' => 'Providers',
            'type' => 'Type',
            'action' => 'Action',
            'manual' => 'Manual',
            'system' => 'System',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ],
    ],
    'logs' => [
        'title' => 'Logs',
        'form' => [
            'user' => 'User',
            'title' => 'Title',
            'type' => 'Type',
            'provider' => 'Provider',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ],
    ],
    'settings' => [
        'group' => 'Notifications',
        'email' => [
            'title' => 'Email Settings',
            'description' => 'Update email provider connection settings',
            'mail_mailer' => 'Mailer',
            'mail_host' => 'Host',
            'mail_port' => 'Port',
            'mail_username' => 'Username',
            'mail_password' => 'Password',
            'mail_encryption' => 'Encryption',
            'mail_from_address' => 'From address',
            'mail_from_name' => 'From Name',
        ],
    ],
    'login' => [
        'or' => 'or continue with',
    ],
];
