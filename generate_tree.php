<?php
// Generate project tree structure
function generateTree($dir, $prefix = '', $isLast = true, $ignorePatterns = []) {
    $ignore = ['.git', 'node_modules', 'vendor', '.env', '.env.local', 'storage/logs', 'storage/framework', '.DS_Store'];
    
    $items = [];
    try {
        $entries = scandir($dir);
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
            
            $items[] = $entry;
        }
    } catch (Exception $e) {
        return;
    }
    
    sort($items);
    $count = count($items);
    
    foreach ($items as $index => $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $isLastItem = ($index === $count - 1);
        
        echo $prefix;
        echo $isLastItem ? '└── ' : '├── ';
        echo $item;
        
        if (is_dir($path)) {
            echo '/';
        }
        echo "\n";
        
        if (is_dir($path)) {
            $newPrefix = $prefix . ($isLastItem ? '    ' : '│   ');
            generateTree($path, $newPrefix, $isLastItem, $ignore);
        }
    }
}

$rootDir = getcwd();
echo "QUERENTIA PROJECT STRUCTURE\n";
echo str_repeat("=", 80) . "\n";
echo $rootDir . "\n";
echo str_repeat("=", 80) . "\n\n";

generateTree($rootDir);

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY DIRECTORIES & FILES:\n";
echo str_repeat("=", 80) . "\n";

$structure = [
    "app/" => [
        "Console/" => "Artisan commands",
        "Exceptions/" => "Exception handling",
        "Http/Controllers/" => "Request controllers",
        "Http/Middleware/" => "HTTP middleware",
        "Http/Requests/" => "Form request validation",
        "Livewire/" => "Livewire components",
        "Models/" => "Eloquent models",
        "Providers/" => "Service providers",
        "Services/" => "Business logic services"
    ],
    "routes/" => [
        "web.php" => "Web routes",
        "api.php" => "API routes",
        "auth.php" => "Authentication routes"
    ],
    "resources/views/" => [
        "journal/" => "Journal editor & preview pages",
        "layouts/" => "Layout templates",
        "components/" => "Reusable components"
    ],
    "database/" => [
        "migrations/" => "Database schema",
        "factories/" => "Model factories",
        "seeders/" => "Data seeders"
    ],
    "config/" => [
        "app.php" => "App configuration",
        "database.php" => "Database config",
        "filesystems.php" => "Storage config"
    ],
    "public/" => [
        "index.php" => "Application entry point",
        "storage/" => "Symlink to storage/app/public"
    ]
];

foreach ($structure as $dir => $items) {
    echo "\n📁 $dir\n";
    if (is_array($items)) {
        foreach ($items as $name => $desc) {
            echo "   • $name - $desc\n";
        }
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "IMPORTANT FILES:\n";
echo str_repeat("=", 80) . "\n";

$importantFiles = [
    "artisan" => "Artisan CLI",
    "composer.json" => "PHP dependencies",
    "package.json" => "Node.js dependencies",
    "vite.config.js" => "Vite build configuration",
    "tailwind.config.js" => "Tailwind CSS configuration",
    "phpunit.xml" => "PHPUnit testing configuration",
    ".env.example" => "Environment variables template"
];

foreach ($importantFiles as $file => $desc) {
    echo "• $file - $desc\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "TOTAL STATISTICS\n";
echo str_repeat("=", 80) . "\n";

$phpFiles = shell_exec('find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" 2>/dev/null | wc -l');
$bladeFiles = shell_exec('find . -name "*.blade.php" 2>/dev/null | wc -l');
$jsFiles = shell_exec('find . -name "*.js" -not -path "./vendor/*" -not -path "./node_modules/*" 2>/dev/null | wc -l');
$cssFiles = shell_exec('find . -name "*.css" -not -path "./node_modules/*" 2>/dev/null | wc -l');

echo "PHP Files (excl. vendor): " . trim($phpFiles) . "\n";
echo "Blade Templates: " . trim($bladeFiles) . "\n";
echo "JavaScript Files: " . trim($jsFiles) . "\n";
echo "CSS Files: " . trim($cssFiles) . "\n";

?>
