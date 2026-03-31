<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Exception;
use App\Domain\CMS\Models\News;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ScrapeNewsFromBapenda extends Command
{
    protected $signature = 'news:scrape-bapenda
        {--pages= : Maximum number of pages to scrape (default: all)}
        {--force : Re-scrape articles that already exist}';

    protected $description = 'Scrape berita dari website resmi Bapenda Bojonegoro (bapenda.bojonegorokab.go.id)';

    private string $baseUrl = 'https://bapenda.bojonegorokab.go.id';

    public function handle(): int
    {
        $maxPages = $this->option('pages') ? (int) $this->option('pages') : 100;
        $force = (bool) $this->option('force');

        $this->info('Memulai scraping berita dari Bapenda Bojonegoro...');

        $totalNew = 0;
        $totalSkipped = 0;
        $page = 1;

        while ($page <= $maxPages) {
            $url = $page === 1
                ? $this->baseUrl . '/berita/'
                : $this->baseUrl . '/berita/index/' . $page;

            $this->line("Fetching halaman {$page}: {$url}");

            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                $this->warn("Gagal mengakses halaman {$page} (HTTP {$response->status()}). Berhenti.");
                break;
            }

            $html = $response->body();
            $articleLinks = $this->extractArticleLinks($html);

            if (empty($articleLinks)) {
                $this->info("Tidak ada artikel ditemukan di halaman {$page}. Selesai.");
                break;
            }

            $allExist = true;

            foreach ($articleLinks as $link) {
                $sourceUrl = $this->baseUrl . $link;

                // Check if already exists
                if (!$force && News::where('source_url', $sourceUrl)->exists()) {
                    $totalSkipped++;
                    $this->line("  [SKIP] Sudah ada: {$sourceUrl}");
                    continue;
                }

                $allExist = false;

                // Fetch detail page
                $article = $this->scrapeArticleDetail($sourceUrl);

                if (!$article) {
                    $this->warn("  [FAIL] Gagal scrape: {$sourceUrl}");
                    continue;
                }

                // Download and compress image to WebP
                $imageUrl = null;
                if (!empty($article['image'])) {
                    $imageUrl = $this->downloadAndCompressImage($article['image']);
                }

                // Create or update news record
                $data = [
                    'title' => $article['title'],
                    'excerpt' => str()->limit(strip_tags($article['content']), 200),
                    'content' => $article['content'],
                    'image_url' => $imageUrl,
                    'published_at' => $article['date'],
                    'category' => 'lainnya',
                    'author' => $article['author'] ?? 'Admin Bapenda',
                    'view_count' => 0,
                    'is_featured' => false,
                    'source_url' => $sourceUrl,
                ];

                if ($force) {
                    $existing = News::where('source_url', $sourceUrl)->first();
                    if ($existing) {
                        $existing->update($data);
                        $this->info("  [UPDATE] {$article['title']}");
                    } else {
                        News::create($data);
                        $this->info("  [NEW] {$article['title']}");
                    }
                } else {
                    News::create($data);
                    $this->info("  [NEW] {$article['title']}");
                }

                $totalNew++;

                // Small delay to be respectful
                usleep(500000); // 0.5 second
            }

            // No early exit - continue through all pages to find any new articles

            // Check if there's a next page
            if (!$this->hasNextPage($html, $page)) {
                $this->info("Halaman terakhir tercapai.");
                break;
            }

            $page++;
            usleep(500000); // 0.5 second between pages
        }

        $this->newLine();
        $this->info("Selesai! Artikel baru: {$totalNew}, Dilewati: {$totalSkipped}");

        return 0;
    }

    /**
     * Extract article links from list page HTML
     */
    private function extractArticleLinks(string $html): array
    {
        $links = [];

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);

        // Find all links to /berita/baca/{id}
        $nodes = $xpath->query('//a[contains(@href, "/berita/baca/")]');

        $seen = [];
        foreach ($nodes as $node) {
            $href = $node->getAttribute('href');
            // Extract the path part
            if (preg_match('#(/berita/baca/\d+)#', $href, $m)) {
                $path = $m[1];
                if (!isset($seen[$path])) {
                    $seen[$path] = true;
                    $links[] = $path;
                }
            }
        }

        return $links;
    }

    /**
     * Scrape a single article detail page
     */
    private function scrapeArticleDetail(string $url): ?array
    {
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            return null;
        }

        $html = $response->body();

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);

        // Extract title - usually the main heading in content area
        $title = $this->extractTitle($xpath);
        if (!$title) {
            return null;
        }

        // Extract content paragraphs
        $content = $this->extractContent($xpath);

        // Extract date (format: dd-mm-yyyy)
        $date = $this->extractDate($xpath);

        // Extract author
        $author = $this->extractAuthor($xpath);

        // Extract image
        $image = $this->extractImage($xpath);

        return [
            'title' => $title,
            'content' => $content,
            'date' => $date,
            'author' => $author,
            'image' => $image,
        ];
    }

    private function extractTitle(DOMXPath $xpath): ?string
    {
        // Bapenda uses <meta property="title" content="..."> for article title
        $metaNodes = $xpath->query('//meta[@property="title"]');
        if ($metaNodes->length > 0) {
            $content = trim($metaNodes->item(0)->getAttribute('content'));
            if (strlen($content) > 5) {
                return $content;
            }
        }

        // Fallback: <div class="header-link-content"> contains the title on detail pages
        $nodes = $xpath->query('//div[contains(@class, "header-link-content")]');
        if ($nodes->length > 0) {
            $text = trim($nodes->item(0)->textContent);
            if (strlen($text) > 5) {
                return $text;
            }
        }

        return null;
    }

    private function extractContent(DOMXPath $xpath): string
    {
        $paragraphs = [];

        // Bapenda: main content is in div.col-lg-8 > paragraphs
        $contentSelectors = [
            '//div[contains(@class, "col-lg-8")]//p',
            '//div[contains(@class, "contents")]//p',
            '//div[contains(@class, "content")]//p',
        ];

        foreach ($contentSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                for ($i = 0; $i < $nodes->length; $i++) {
                    $text = trim($nodes->item($i)->textContent);
                    if (strlen($text) > 15) {
                        $paragraphs[] = $text;
                    }
                }
                if (!empty($paragraphs)) {
                    break;
                }
            }
        }

        if (!empty($paragraphs)) {
            return implode("\n\n", array_map(fn($p) => '<p>' . e($p) . '</p>', $paragraphs));
        }

        // Fallback: use meta description
        $metaNodes = $xpath->query('//meta[@property="description"]');
        if ($metaNodes->length > 1) {
            $desc = trim($metaNodes->item(1)->getAttribute('content'));
            if (strlen($desc) > 20) {
                return '<p>' . e($desc) . '</p>';
            }
        }

        return '';
    }

    private function extractDate(DOMXPath $xpath): ?Carbon
    {
        // Search for date pattern dd-mm-yyyy in the page
        $body = $xpath->query('//body')->item(0);
        if (!$body) {
            return null;
        }

        $text = $body->textContent;

        // Pattern: dd-mm-yyyy
        if (preg_match('/(\d{2})-(\d{2})-(\d{4})/', $text, $m)) {
            try {
                return Carbon::createFromFormat('d-m-Y', $m[0]);
            } catch (Exception $e) {
                // ignore
            }
        }

        // Pattern: dd/mm/yyyy
        if (preg_match('#(\d{2})/(\d{2})/(\d{4})#', $text, $m)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $m[0]);
            } catch (Exception $e) {
                // ignore
            }
        }

        return now();
    }

    private function extractAuthor(DOMXPath $xpath): string
    {
        $body = $xpath->query('//body')->item(0);
        if (!$body) {
            return 'Admin Bapenda';
        }

        $text = $body->textContent;

        // Look for "By Author" pattern
        if (preg_match('/By\s+(\w+)/', $text, $m)) {
            return $m[1];
        }

        return 'Admin Bapenda';
    }

    private function extractImage(DOMXPath $xpath): ?string
    {
        // Bapenda article images are in /uploads/artikel/ directory
        $nodes = $xpath->query('//img[contains(@src, "uploads/artikel")]');
        if ($nodes->length > 0) {
            $src = $nodes->item(0)->getAttribute('src');
            // Make absolute URL if relative
            if (!str_starts_with($src, 'http')) {
                $src = $this->baseUrl . '/' . ltrim($src, '/');
            }
            return $src;
        }

        // Fallback: meta image
        $metaNodes = $xpath->query('//meta[@property="image"]');
        if ($metaNodes->length > 0) {
            $src = trim($metaNodes->item(0)->getAttribute('content'));
            if (strlen($src) > 10) {
                if (!str_starts_with($src, 'http')) {
                    $src = $this->baseUrl . '/' . ltrim($src, '/');
                }
                return $src;
            }
        }

        return null;
    }

    /**
     * Download image from URL, compress and convert to WebP
     */
    private function downloadAndCompressImage(string $imageUrl): ?string
    {
        try {
            // Handle data URIs
            if (str_starts_with($imageUrl, 'data:')) {
                $parts = explode(',', $imageUrl, 2);
                if (count($parts) !== 2) {
                    return null;
                }
                $imageData = base64_decode($parts[1]);
            } else {
                $response = Http::timeout(30)->get($imageUrl);
                if (!$response->successful()) {
                    return null;
                }
                $imageData = $response->body();
            }

            // Create GD image from data
            $sourceImage = @imagecreatefromstring($imageData);
            if (!$sourceImage) {
                return null;
            }

            // Resize to 1200x675 (16:9) cover mode
            $origW = imagesx($sourceImage);
            $origH = imagesy($sourceImage);
            $targetW = 1200;
            $targetH = 675;

            $ratioW = $targetW / $origW;
            $ratioH = $targetH / $origH;
            $ratio = max($ratioW, $ratioH);

            $cropW = (int) ceil($targetW / $ratio);
            $cropH = (int) ceil($targetH / $ratio);
            $cropX = (int) (($origW - $cropW) / 2);
            $cropY = (int) (($origH - $cropH) / 2);

            $resized = imagecreatetruecolor($targetW, $targetH);
            imagecopyresampled($resized, $sourceImage, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
            imagedestroy($sourceImage);

            // Compress to WebP with progressive quality reduction
            $directory = 'news/images';
            $filename = $directory . '/' . str()->random(40) . '.webp';
            $storagePath = public_path($filename);

            if (!is_dir(dirname($storagePath))) {
                mkdir(dirname($storagePath), 0755, true);
            }

            $quality = 85;
            do {
                imagewebp($resized, $storagePath, $quality);
                clearstatcache(true, $storagePath);
                $size = filesize($storagePath);
                $quality -= 10;
            } while ($size > 1048576 && $quality >= 10);

            imagedestroy($resized);

            return $filename;
        } catch (Exception $e) {
            $this->warn("  [IMG] Gagal download/kompress gambar: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Check if there is a next page link
     */
    private function hasNextPage(string $html, int $currentPage): bool
    {
        $nextPage = $currentPage + 1;

        return str_contains($html, '/berita/index/' . $nextPage);
    }
}
