<?php

$data = json_decode(file_get_contents(__DIR__ . '/Modules/Blog/resources/data/posts.json'), true) ?? [];
$pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("DELETE FROM posts; DELETE FROM sqlite_sequence WHERE name='posts';");
$stmt = $pdo->prepare("INSERT INTO posts (slug,title,excerpt,content,cover_image,og_image,author,published_at,status,tags,reading_time,meta_title,meta_description,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$now = date('c');
$count = 0;
foreach ($data as $d) {
    if (empty($d['slug']) || $d['slug'] === 'string') {
        continue;
    }
    $cover = $d['cover_image'] ?? ($d['coverImage'] ?? null);
    $row = [
        $d['slug'],
        $d['title'] ?? 'Untitled',
        $d['excerpt'] ?? null,
        $d['content'] ?? null,
        $cover,
        $d['og_image'] ?? $cover,
        $d['author'] ?? 'Automaton Soft',
        $d['published_at'] ?? null,
        $d['status'] ?? 'published',
        json_encode($d['tags'] ?? []),
        $d['reading_time'] ?? null,
        $d['meta_title'] ?? ($d['title'] ?? null),
        $d['meta_description'] ?? ($d['excerpt'] ?? null),
        $now,
        $now,
    ];
    $stmt->execute($row);
    $count++;
}
echo "Inserted posts: {$count}\n";
