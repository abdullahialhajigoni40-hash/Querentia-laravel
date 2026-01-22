<?php
// Generate complete project tree structure
function generateTreeStructure($dir, $prefix = '', $isLast = true, $maxDepth = 10, $currentDepth = 0, $ignorePatterns = []) {
    // Skip if max depth reached
    if ($currentDepth >= $maxDepth) {
        return '';
    }

    $output = '';
    $ignore = ['.git', 'node_modules', 'vendor', '.env', '.env.local', 'storage/logs', 'storage/framework', '.DS_Store', '.vscode', '.editorconfig'];
    
    $items = [];
    try {
        $entries = @scandir($dir);
        if (!$entries) return $output;
        
        foreach ($entries as $entry) {
            if ($entry == '.' || $entry == '..') continue;
            
            $skip = false;
            foreach ($ignore as $pattern) {
                if (strpos($entry, $pattern) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;
            
            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path) || is_file($path)) {
                $items[] = ['name' => $entry, 'path' => $path, 'isDir' => is_dir($path)];
            }
        }
    } catch (Exception $e) {
        return $output;
    }
    
    // Sort directories first, then files
    usort($items, function($a, $b) {
        if ($a['isDir'] === $b['isDir']) {
            return strcasecmp($a['name'], $b['name']);
        }
        return $b['isDir'] <=> $a['isDir'];
    });
    
    $count = count($items);
    
    foreach ($items as $index => $item) {
        $isLastItem = ($index === $count - 1);
        $itemPrefix = $prefix . ($isLastItem ? '└── ' : '├── ');
        
        if ($item['isDir']) {
            $output .= $itemPrefix . $item['name'] . '/';
        } else {
            $output .= $itemPrefix . $item['name'];
        }
        $output .= "\n";
        
        if ($item['isDir'] && $currentDepth < $maxDepth - 1) {
            $newPrefix = $prefix . ($isLastItem ? '    ' : '│   ');
            $output .= generateTreeStructure($item['path'], $newPrefix, $isLastItem, $maxDepth, $currentDepth + 1, $ignore);
        }
    }
    
    return $output;
}

$rootDir = getcwd();

echo "\n";
echo str_repeat("═", 100) . "\n";
echo " QUERENTIA PROJECT - COMPLETE CODEBASE TREE\n";
echo " Generated: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("═", 100) . "\n\n";
echo "Root: " . $rootDir . "\n\n";

$tree = generateTreeStructure($rootDir, '', true, 8);
echo $tree;

echo "\n" . str_repeat("═", 100) . "\n";
echo " DIRECTORY BREAKDOWN BY FUNCTION\n";
echo str_repeat("═", 100) . "\n\n";

$directories = [
    'app/' => [
        'description' => 'Core Application Logic',
        'subdirs' => [
            'Http/Controllers/' => 'Request handling & business logic',
            'Models/' => 'Database models (Eloquent)',
            'Services/' => 'Reusable business services',
            'Providers/' => 'Service providers for app',
            'Livewire/' => 'Livewire reactive components',
            'Middleware/' => 'HTTP middleware',
            'Exceptions/' => 'Exception handling',
            'Console/' => 'Artisan commands'
        ]
    ],
    'routes/' => [
        'description' => 'URL Routing',
        'files' => [
            'web.php' => 'Web routes (journals, network, auth)',
            'api.php' => 'API routes (save, AI generation)',
            'auth.php' => 'Authentication routes',
            'channels.php' => 'Broadcasting channels'
        ]
    ],
    'resources/views/' => [
        'description' => 'Blade Templates (UI)',
        'subdirs' => [
            'journal/' => 'Journal editor & preview pages',
            'network/' => 'Social networking pages',
            'auth/' => 'Authentication pages',
            'profile/' => 'User profile pages',
            'layouts/' => 'Layout wrappers',
            'components/' => 'Reusable Blade components',
            'payment/' => 'Payment & subscription pages'
        ]
    ],
    'resources/css/' => [
        'description' => 'Stylesheets',
        'files' => [
            'app.css' => 'Tailwind CSS main file'
        ]
    ],
    'resources/js/' => [
        'description' => 'JavaScript',
        'files' => [
            'app.js' => 'Main entry point',
            'bootstrap.js' => 'Bootstrap configuration',
            'ai-streaming.js' => 'AI response streaming'
        ]
    ],
    'database/' => [
        'description' => 'Database',
        'subdirs' => [
            'migrations/' => 'Schema definitions',
            'factories/' => 'Model factories for testing',
            'seeders/' => 'Database seeders'
        ]
    ],
    'config/' => [
        'description' => 'Configuration Files',
        'files' => [
            'app.php' => 'App configuration',
            'database.php' => 'Database connection',
            'filesystems.php' => 'Storage configuration',
            'auth.php' => 'Authentication config',
            'ai.php' => 'AI/DeepSeek config',
            'mail.php' => 'Email configuration'
        ]
    ],
    'public/' => [
        'description' => 'Web Root',
        'files' => [
            'index.php' => 'Application entry point',
            'build/' => 'Compiled assets (JS/CSS)'
        ]
    ],
    'storage/' => [
        'description' => 'Runtime Data',
        'subdirs' => [
            'app/journals/' => 'Uploaded journal files',
            'app/figures/' => 'Uploaded images',
            'app/annexes/' => 'Uploaded annexes',
            'app/profile/' => 'Profile pictures',
            'logs/' => 'Application logs',
            'framework/' => 'Laravel framework cache'
        ]
    ],
    'tests/' => [
        'description' => 'Test Suites',
        'subdirs' => [
            'Feature/' => 'Feature tests',
            'Unit/' => 'Unit tests'
        ]
    ]
];

foreach ($directories as $dir => $info) {
    echo "📁 " . str_pad($dir, 25) . " — " . $info['description'] . "\n";
    
    if (isset($info['subdirs'])) {
        foreach ($info['subdirs'] as $subdir => $desc) {
            echo "   ├─ " . str_pad($subdir, 22) . $desc . "\n";
        }
    }
    
    if (isset($info['files'])) {
        foreach ($info['files'] as $file => $desc) {
            echo "   ├─ " . str_pad($file, 22) . $desc . "\n";
        }
    }
    echo "\n";
}

echo str_repeat("═", 100) . "\n";
echo " KEY FILES & ENTRY POINTS\n";
echo str_repeat("═", 100) . "\n\n";

$keyFiles = [
    'public/index.php' => 'Application entry point',
    'artisan' => 'Artisan CLI tool',
    'composer.json' => 'PHP dependencies',
    'package.json' => 'Node.js dependencies',
    'vite.config.js' => 'Build tool configuration',
    'tailwind.config.js' => 'Tailwind CSS configuration',
    'phpunit.xml' => 'Testing configuration',
    '.env.example' => 'Environment variables template'
];

foreach ($keyFiles as $file => $desc) {
    echo "  🔧 " . str_pad($file, 30) . $desc . "\n";
}

echo "\n" . str_repeat("═", 100) . "\n";
echo " CORE TABLES & MIGRATIONS\n";
echo str_repeat("═", 100) . "\n\n";

$migrations = [
    'users' => 'User accounts & authentication',
    'user_profiles' => 'Extended user information',
    'journals' => 'Academic journals ⭐',
    'posts' => 'Network posts ⭐',
    'comments' => 'Post comments',
    'reviews' => 'Peer reviews',
    'likes' => 'Post engagement',
    'notifications' => 'User notifications',
    'user_connections' => 'User relationships',
    'subscriptions' => 'Subscription plans',
    'transactions' => 'Payment transactions',
    'peer_reviews' => 'Detailed peer reviews',
    'ai_usage_logs' => 'AI API tracking',
    'network_posts' => 'Network-specific posts',
    'journal_versions' => 'Journal version history',
    'review_feedbacks' => 'Review feedback data'
];

foreach ($migrations as $table => $desc) {
    $marker = (strpos($desc, '⭐') !== false) ? '⭐ ' : '  ';
    echo $marker . str_pad($table, 25) . $desc . "\n";
}

echo "\n" . str_repeat("═", 100) . "\n";
echo " MAIN FEATURES & WORKFLOWS\n";
echo str_repeat("═", 100) . "\n\n";

$features = [
    'Journal Editor' => [
        'Path' => 'resources/views/journal/editor.blade.php',
        'Controller' => 'JournalController@create',
        'Features' => 'Real-time editing, AI enhancement, file uploads'
    ],
    'AI Generation' => [
        'Path' => 'app/Services/AIJournalService.php',
        'Endpoint' => '/api/ai/generate-journal',
        'Features' => 'DeepSeek API streaming, section enhancement'
    ],
    'Journal Preview' => [
        'Path' => 'resources/views/journal/preview.blade.php',
        'Controller' => 'JournalController@preview',
        'Features' => 'Formatted display, PDF download, peer review posting'
    ],
    'Social Network' => [
        'Path' => 'resources/views/network/',
        'Controller' => 'NetworkController',
        'Features' => 'Posts, comments, peer feedback, connections'
    ],
    'User Authentication' => [
        'Path' => 'routes/auth.php',
        'Controllers' => 'Auth/RegisteredUserController, AuthenticatedSessionController',
        'Features' => 'Login, registration, password reset'
    ],
    'File Management' => [
        'Path' => 'app/Http/Controllers/FileUploadController.php',
        'Endpoint' => '/api/upload/*',
        'Features' => 'Figures, annexes, profile pictures'
    ],
    'Payments' => [
        'Path' => 'app/Http/Controllers/PaymentController.php',
        'Provider' => 'Paystack',
        'Features' => 'Subscriptions, transactions tracking'
    ]
];

$i = 1;
foreach ($features as $feature => $info) {
    echo "$i. " . str_pad($feature, 25) . "\n";
    foreach ($info as $key => $value) {
        echo "   • " . str_pad($key . ':', 15) . $value . "\n";
    }
    echo "\n";
    $i++;
}

echo str_repeat("═", 100) . "\n";
echo " TECHNOLOGY STACK\n";
echo str_repeat("═", 100) . "\n\n";

echo "Backend:\n";
echo "  • Laravel 11 - PHP web framework\n";
echo "  • MySQL - Database\n";
echo "  • DeepSeek API - AI generation\n";
echo "  • Guzzle HTTP - API client\n";
echo "  • DomPDF - PDF generation\n";
echo "  • Paystack - Payment gateway\n\n";

echo "Frontend:\n";
echo "  • Alpine.js v3 - Reactive components\n";
echo "  • Tailwind CSS - Styling\n";
echo "  • Vite - Build tool\n";
echo "  • Font Awesome - Icons\n\n";

echo "Development:\n";
echo "  • PHPUnit - Testing\n";
echo "  • Composer - PHP package manager\n";
echo "  • npm - JavaScript package manager\n";
echo "  • Artisan - Laravel CLI\n\n";

echo str_repeat("═", 100) . "\n";
echo " WORKFLOW DIAGRAM\n";
echo str_repeat("═", 100) . "\n\n";

echo "User Journey:\n\n";
echo "1. Register/Login\n";
echo "   └─ routes/auth.php → Auth/RegisteredUserController\n\n";

echo "2. Access Dashboard\n";
echo "   └─ routes/web.php → HomeController → resources/views/dashboard.blade.php\n\n";

echo "3. Create Journal\n";
echo "   └─ routes/web.php → JournalController@create\n";
echo "   └─ resources/views/journal/editor.blade.php (Alpine.js)\n\n";

echo "4. Save Journal Sections\n";
echo "   └─ /api/journal/save (POST)\n";
echo "   └─ JournalController@saveJournal\n";
echo "   └─ app/Models/Journal (save to DB)\n\n";

echo "5. Generate AI Content\n";
echo "   └─ /api/ai/generate-journal (POST with streaming)\n";
echo "   └─ JournalController@generateAIJournal\n";
echo "   └─ AIJournalService::generate() → DeepSeek API\n";
echo "   └─ SSE streaming back to editor\n\n";

echo "6. Preview Journal\n";
echo "   └─ /journal/{id}/preview\n";
echo "   └─ JournalController@preview\n";
echo "   └─ resources/views/journal/preview.blade.php\n\n";

echo "7. Post for Peer Review\n";
echo "   └─ /journal/{id}/post-review (POST)\n";
echo "   └─ JournalController@postForReview\n";
echo "   └─ app/Models/Post (create post)\n\n";

echo "8. Share on Network\n";
echo "   └─ resources/views/network/home.blade.php\n";
echo "   └─ Comments, likes, feedback\n\n";

echo str_repeat("═", 100) . "\n";
echo " TOTAL PROJECT STATISTICS\n";
echo str_repeat("═", 100) . "\n\n";

// Count files
$phpCount = 0;
$bladeCount = 0;
$jsCount = 0;
$cssCount = 0;
$migrationCount = 0;

$pathsToScan = [
    'app' => ['php' => &$phpCount],
    'routes' => ['php' => &$phpCount],
    'resources/views' => ['blade' => &$bladeCount],
    'resources/js' => ['js' => &$jsCount],
    'resources/css' => ['css' => &$cssCount],
    'database/migrations' => ['php' => &$migrationCount]
];

foreach ($pathsToScan as $path => $types) {
    $fullPath = $rootDir . DIRECTORY_SEPARATOR . $path;
    if (is_dir($fullPath)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                foreach ($types as $ext => &$count) {
                    if ($file->getExtension() === $ext || ($ext === 'blade' && strpos($file->getFilename(), '.blade.php') !== false)) {
                        $count++;
                    }
                }
            }
        }
    }
}

echo "PHP Files (Controllers, Models, Services):\n";
echo "  • Controllers: ~15 files\n";
echo "  • Models: ~15 files\n";
echo "  • Services: ~5 files\n";
echo "  • Total PHP: ~" . $phpCount . " files\n\n";

echo "Blade Templates:\n";
echo "  • Views: ~" . $bladeCount . " files\n\n";

echo "JavaScript:\n";
echo "  • Alpine.js components: ~" . $jsCount . " files\n\n";

echo "CSS:\n";
echo "  • Tailwind CSS: ~" . $cssCount . " files\n\n";

echo "Database:\n";
echo "  • Migrations: ~" . $migrationCount . " files\n";
echo "  • Tables: 16 core tables\n\n";

echo "Total Codebase:\n";
echo "  • ~" . ($phpCount + $bladeCount + $jsCount + $cssCount) . " frontend/backend files\n";
echo "  • ~" . $migrationCount . " database migrations\n";
echo "  • ~100+ dependencies (Composer + npm)\n\n";

echo str_repeat("═", 100) . "\n";
echo " Generated successfully!\n";
echo str_repeat("═", 100) . "\n\n";

?>
