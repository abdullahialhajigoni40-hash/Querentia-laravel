<?php
// create_views.php
$views = [
    'notifications.blade.php',
    'blog.blade.php',
    'groups.blade.php',
    'submissions.blade.php',
    'ai-studio.blade.php',
    'settings.blade.php',
    'subscription.blade.php'
];

foreach ($views as $view) {
    $path = 'resources/views/' . $view;
    if (!file_exists($path)) {
        file_put_contents($path, '');
        echo "Created: $path\n";
    } else {
        echo "Exists: $path\n";
    }
}