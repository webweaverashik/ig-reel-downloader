<?php
namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml
     */
    public function index(): Response
    {
        $baseUrl = config('app.url', 'https://igreeldownloader.net');

        // Define all pages with their routes, priorities, and change frequencies
        $pages = [
            [
                'slug'       => 'home',
                'route'      => 'home',
                'priority'   => '1.0',
                'changefreq' => 'daily',
            ],
            [
                'slug'       => 'reels',
                'route'      => 'instagram.reels',
                'priority'   => '0.9',
                'changefreq' => 'weekly',
            ],
            [
                'slug'       => 'video',
                'route'      => 'instagram.video',
                'priority'   => '0.9',
                'changefreq' => 'weekly',
            ],
            [
                'slug'       => 'photo',
                'route'      => 'instagram.photo',
                'priority'   => '0.9',
                'changefreq' => 'weekly',
            ],
            [
                'slug'       => 'story',
                'route'      => 'instagram.story',
                'priority'   => '0.9',
                'changefreq' => 'weekly',
            ],
            [
                'slug'       => 'carousel',
                'route'      => 'instagram.carousel',
                'priority'   => '0.9',
                'changefreq' => 'weekly',
            ],
            [
                'slug'       => 'privacy-policy',
                'route'      => 'privacy-policy',
                'priority'   => '0.6',
                'changefreq' => 'monthly',
            ],
            [
                'slug'       => 'terms',
                'route'      => 'terms',
                'priority'   => '0.6',
                'changefreq' => 'monthly',
            ],
            [
                'slug'       => 'contact',
                'route'      => 'contact',
                'priority'   => '0.6',
                'changefreq' => 'monthly',
            ],
        ];

        // Get active pages from database
        $activePageSlugs = Page::where('is_active', true)
            ->pluck('slug')
            ->toArray();

        // Always include these pages even if not in database
        $alwaysInclude = ['contact'];

        // Build sitemap entries
        $urls    = [];
        $lastmod = now()->toW3cString();

        foreach ($pages as $page) {
            // Check if page is active (or always included, or not in database yet)
            $isActive = in_array($page['slug'], $activePageSlugs)
            || in_array($page['slug'], $alwaysInclude)
            || ! Page::where('slug', $page['slug'])->exists();

            if ($isActive && \Route::has($page['route'])) {
                $urls[] = [
                    'loc'        => route($page['route']),
                    'lastmod'    => $lastmod,
                    'changefreq' => $page['changefreq'],
                    'priority'   => $page['priority'],
                ];
            }
        }

        // Generate XML
        $xml = $this->generateXml($urls);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Generate XML string from URLs array
     */
    private function generateXml(array $urls): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
