<?php
$files = [
    'resources/views/livewire/akademik/mahasiswa-index.blade.php' => [
        "{{ \$mhs->calonMahasiswa?->nama ?? '-' }}" => "{{ ucwords(strtolower(\$mhs->calonMahasiswa?->nama ?? '-')) }}"
    ],
    'resources/views/livewire/data-individu.blade.php' => [
        "{{ \$row->nama ?? '-' }}" => "{{ ucwords(strtolower(\$row->nama ?? '-')) }}"
    ],
    'resources/views/livewire/manajemen-pmb/penetapan-registrasi-index.blade.php' => [
        "{{ \$cama->nama }}" => "{{ ucwords(strtolower(\$cama->nama)) }}"
    ],
    'resources/views/livewire/manajemen-pmb/entri-data-pmb.blade.php' => [
        "{{ \$row->nama }}" => "{{ ucwords(strtolower(\$row->nama)) }}"
    ],
    'resources/views/livewire/input/input-manual.blade.php' => [
        "{{ \$row->nama }}" => "{{ ucwords(strtolower(\$row->nama)) }}"
    ],
    'resources/views/livewire/pengaturan/user-index.blade.php' => [
        "{{ \$user->name }}" => "{{ ucwords(strtolower(\$user->name)) }}"
    ]
];

foreach ($files as $file => $replacements) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
        if ($content !== $newContent) {
            file_put_contents($path, $newContent);
            echo "Updated $file\n";
        }
    }
}
