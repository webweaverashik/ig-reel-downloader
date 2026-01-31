<?php
namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating menus...');

        // Create Main Menu
        $mainMenu = Menu::updateOrCreate(
            ['slug' => 'main'],
            [
                'name'        => 'Main Menu',
                'location'    => 'header',
                'description' => 'Main navigation menu displayed in the header',
                'is_active'   => true,
            ]
        );

        // Create Footer Downloaders Menu
        $footerDownloaders = Menu::updateOrCreate(
            ['slug' => 'footer-downloaders'],
            [
                'name'        => 'Footer Downloaders',
                'location'    => 'footer',
                'description' => 'Downloader links displayed in the footer',
                'is_active'   => true,
            ]
        );

        // Create Footer Legal Menu
        $footerLegal = Menu::updateOrCreate(
            ['slug' => 'footer-legal'],
            [
                'name'        => 'Footer Legal',
                'location'    => 'footer',
                'description' => 'Legal pages displayed in the footer',
                'is_active'   => true,
            ]
        );

        $this->command->info('Creating menu items...');

        // Main Menu Items
        $mainMenuItems = [
            ['title' => 'Reels', 'page_slug' => 'reels', 'icon' => null, 'order' => 1],
            ['title' => 'Video', 'page_slug' => 'video', 'icon' => null, 'order' => 2],
            ['title' => 'Photo', 'page_slug' => 'photo', 'icon' => null, 'order' => 3],
            ['title' => 'Story', 'page_slug' => 'story', 'icon' => null, 'order' => 4],
            ['title' => 'Carousel', 'page_slug' => 'carousel', 'icon' => null, 'order' => 5],
            ['title' => 'Highlights', 'page_slug' => 'highlights', 'icon' => null, 'order' => 6],
            ['title' => 'Blog', 'page_slug' => 'blog', 'icon' => null, 'order' => 7],
        ];

        foreach ($mainMenuItems as $item) {
            $page = Page::where('slug', $item['page_slug'])->first();
            MenuItem::updateOrCreate(
                ['menu_id' => $mainMenu->id, 'title' => $item['title']],
                [
                    'page_id'   => $page?->id,
                    'icon'      => $item['icon'],
                    'order'     => $item['order'],
                    'target'    => '_self',
                    'is_active' => true,
                ]
            );
        }

        // Footer Downloaders Items
        $footerDownloaderItems = [
            ['title' => 'Reels Downloader', 'page_slug' => 'reels', 'order' => 1],
            ['title' => 'Video Downloader', 'page_slug' => 'video', 'order' => 2],
            ['title' => 'Photo Downloader', 'page_slug' => 'photo', 'order' => 3],
            ['title' => 'Story Downloader', 'page_slug' => 'story', 'order' => 4],
            ['title' => 'Carousel Downloader', 'page_slug' => 'carousel', 'order' => 5],
            ['title' => 'Highlights Downloader', 'page_slug' => 'highlights', 'order' => 6],
        ];

        foreach ($footerDownloaderItems as $item) {
            $page = Page::where('slug', $item['page_slug'])->first();
            MenuItem::updateOrCreate(
                ['menu_id' => $footerDownloaders->id, 'title' => $item['title']],
                [
                    'page_id'   => $page?->id,
                    'order'     => $item['order'],
                    'target'    => '_self',
                    'is_active' => true,
                ]
            );
        }

        // Footer Legal Items
        $footerLegalItems = [
            ['title' => 'Privacy Policy', 'page_slug' => 'privacy-policy', 'order' => 1],
            ['title' => 'Terms of Service', 'page_slug' => 'terms', 'order' => 2],
            ['title' => 'Contact Us', 'page_slug' => 'contact', 'order' => 3],
        ];

        foreach ($footerLegalItems as $item) {
            $page = Page::where('slug', $item['page_slug'])->first();

            // For contact, we might not have a page, so use custom URL
            $pageId = $page?->id;
            $url    = null;

            if (! $pageId && $item['page_slug'] === 'contact') {
                $url = '/contact';
            }

            MenuItem::updateOrCreate(
                ['menu_id' => $footerLegal->id, 'title' => $item['title']],
                [
                    'page_id'   => $pageId,
                    'url'       => $url,
                    'order'     => $item['order'],
                    'target'    => '_self',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('  - Created 3 menus with ' . MenuItem::count() . ' items');
    }
}
