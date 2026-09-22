<?php
$dir = new RecursiveDirectoryIterator(__DIR__.'/../app/Livewire');
$ite = new RecursiveIteratorIterator($dir);
foreach ($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $newContent = preg_replace('/public\s+int\s+\$perPage\s*=\s*\d+;/', 'public int $perPage = 10;', $content);
        $newContent = preg_replace('/public\s+\$perPage\s*=\s*\d+;/', 'public $perPage = 10;', $newContent);
        $newContent = preg_replace('/paginate\(\d+/', 'paginate(10', $newContent);
        if ($content !== $newContent) {
            file_put_contents($file->getPathname(), $newContent);
            echo 'Updated ' . $file->getPathname() . PHP_EOL;
        }
    }
}
