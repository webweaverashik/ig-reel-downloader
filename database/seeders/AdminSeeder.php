<?php
namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating super admin user...');

        // Create super admin user
        User::updateOrCreate(
            ['email' => 'admin@igreeldownloader.net'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('admin123456'),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Creating site settings...');
        $this->createSettings();

        $this->command->info('Creating pages...');
        $this->createPages();

        $this->command->info('Creating FAQs...');
        $this->createFaqs();

        $this->command->info('Admin seeder completed successfully!');
    }

    private function createSettings(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'IGReelDownloader.net', 'type' => 'text', 'group' => 'general', 'label' => 'Site Name', 'order' => 1],
            ['key' => 'site_tagline', 'value' => 'Best Instagram Downloader', 'type' => 'text', 'group' => 'general', 'label' => 'Site Tagline', 'order' => 2],
            ['key' => 'site_description', 'value' => 'Download Instagram Reels, Videos, Photos, Stories in HD quality. Free, fast, and no login required.', 'type' => 'textarea', 'group' => 'general', 'label' => 'Site Description', 'order' => 3],
            ['key' => 'footer_text', 'value' => 'We respect intellectual property rights. Please download content for personal use only.', 'type' => 'textarea', 'group' => 'general', 'label' => 'Footer Text', 'order' => 4],
            ['key' => 'copyright_text', 'value' => '© ' . date('Y') . ' IGReelDownloader.net. All rights reserved. Not affiliated with Instagram or Meta.', 'type' => 'text', 'group' => 'general', 'label' => 'Copyright Text', 'order' => 5],

            // SEO Settings
            ['key' => 'default_meta_title', 'value' => 'IG Reel Downloader - Best Instagram Downloader | IGReelDownloader.net', 'type' => 'text', 'group' => 'seo', 'label' => 'Default Meta Title', 'order' => 1],
            ['key' => 'default_meta_description', 'value' => 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.', 'type' => 'textarea', 'group' => 'seo', 'label' => 'Default Meta Description', 'order' => 2],
            ['key' => 'default_meta_keywords', 'value' => 'instagram downloader, reels downloader, ig video downloader, instagram photo downloader, story downloader', 'type' => 'textarea', 'group' => 'seo', 'label' => 'Default Meta Keywords', 'order' => 3],
            ['key' => 'google_analytics_id', 'value' => '', 'type' => 'text', 'group' => 'seo', 'label' => 'Google Analytics ID', 'order' => 4],
            ['key' => 'google_site_verification', 'value' => '', 'type' => 'text', 'group' => 'seo', 'label' => 'Google Site Verification', 'order' => 5],
            ['key' => 'google_tag_manager_id', 'value' => '', 'type' => 'text', 'group' => 'seo', 'label' => 'Google Tag Manager ID', 'order' => 6],

            // Contact Settings
            ['key' => 'contact_email', 'value' => 'support@igreeldownloader.net', 'type' => 'text', 'group' => 'contact', 'label' => 'Contact Email', 'order' => 1],
            ['key' => 'dmca_email', 'value' => 'dmca@igreeldownloader.net', 'type' => 'text', 'group' => 'contact', 'label' => 'DMCA Email', 'order' => 2],
            ['key' => 'privacy_email', 'value' => 'privacy@igreeldownloader.net', 'type' => 'text', 'group' => 'contact', 'label' => 'Privacy Email', 'order' => 3],
            ['key' => 'response_time_general', 'value' => '24-48 hours', 'type' => 'text', 'group' => 'contact', 'label' => 'Response Time (General)', 'order' => 4],
            ['key' => 'response_time_support', 'value' => '1-3 days', 'type' => 'text', 'group' => 'contact', 'label' => 'Response Time (Support)', 'order' => 5],
            ['key' => 'response_time_dmca', 'value' => '3-5 days', 'type' => 'text', 'group' => 'contact', 'label' => 'Response Time (DMCA)', 'order' => 6],

            // Social Settings
            ['key' => 'twitter_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'Twitter URL', 'order' => 1],
            ['key' => 'facebook_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'Facebook URL', 'order' => 2],
            ['key' => 'instagram_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'Instagram URL', 'order' => 3],
            ['key' => 'youtube_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'YouTube URL', 'order' => 4],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('  - Created ' . count($settings) . ' settings');
    }

    private function createPages(): void
    {
        $pages = [
            [
                'slug'             => 'home',
                'title'            => 'IG Reel Downloader',
                'meta_title'       => 'IG Reel Downloader - Best Instagram Downloader | IGReelDownloader.net',
                'meta_description' => 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.',
                'meta_keywords'    => 'instagram downloader, reels downloader, ig downloader, free instagram downloader',
                'hero_title'       => 'IG Reel Downloader',
                'hero_highlight'   => 'Best Instagram Downloader',
                'subtitle'         => 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.',
                'badge'            => '100% Free & Unlimited Downloads',
                'placeholder'      => 'Paste Instagram URL here (Reels, Videos, Photos)...',
                'formats'          => ['Instagram Reels Downloader', 'Instagram Video Downloader', 'Instagram Photo Downloader', 'Instagram Story Downloader', 'Instagram Carousel Downloader', 'Instagram Highlights Downloader'],
                'is_active'        => true,
            ],
            [
                'slug'             => 'reels',
                'title'            => 'Instagram Reels Downloader',
                'meta_title'       => 'Instagram Reels Downloader - Download Reels in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Reels in HD quality. Free, fast, and no login required. Save your favorite Reels instantly with IG Reel Downloader.',
                'meta_keywords'    => 'instagram reels downloader, download reels, ig reels saver, reels video download',
                'hero_title'       => 'Instagram Reels Downloader',
                'hero_highlight'   => 'Download Reels in HD',
                'subtitle'         => 'Download any Instagram Reels in HD quality. Fast, free, and no login required. Save your favorite Reels instantly.',
                'badge'            => 'Free & Unlimited Reels Downloads',
                'placeholder'      => 'Paste Instagram Reel URL here...',
                'formats'          => ['HD Video', 'MP4 Format', 'No Watermark', 'Fast Download', 'Original Audio'],
                'is_active'        => true,
            ],
            [
                'slug'             => 'video',
                'title'            => 'Instagram Video Downloader',
                'meta_title'       => 'Instagram Video Downloader - Download IG Videos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Videos in HD quality. Free, fast, and works on all devices. Save IGTV and video posts instantly.',
                'meta_keywords'    => 'instagram video downloader, download ig video, igtv downloader, instagram video saver',
                'hero_title'       => 'Instagram Video Downloader',
                'hero_highlight'   => 'Download Videos in HD',
                'subtitle'         => 'Download any Instagram video in original HD quality. Fast, free, and works on all devices. Save IGTV and video posts instantly.',
                'badge'            => 'Free HD Video Downloads',
                'placeholder'      => 'Paste Instagram Video URL here...',
                'formats'          => ['IGTV Support', 'Video Feed', 'HD 1080p', 'MP4 Files', 'High Bitrate'],
                'is_active'        => true,
            ],
            [
                'slug'             => 'photo',
                'title'            => 'Instagram Photo Downloader',
                'meta_title'       => 'Instagram Photo Downloader - Download IG Photos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'meta_keywords'    => 'instagram photo downloader, download ig photos, instagram image saver, ig picture download',
                'hero_title'       => 'Instagram Photo Downloader',
                'hero_highlight'   => 'Download Photos in HD',
                'subtitle'         => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'badge'            => 'Free HD Photo Downloads',
                'placeholder'      => 'Paste Instagram Photo URL here...',
                'formats'          => ['High Res JPG', 'Profile Images', 'Original Quality', 'Post Photos', 'Direct Download'],
                'is_active'        => true,
            ],
            [
                'slug'             => 'story',
                'title'            => 'Instagram Story Downloader',
                'meta_title'       => 'Instagram Story Downloader - Download IG Stories | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'meta_keywords'    => 'instagram story downloader, download ig stories, story saver, anonymous story viewer',
                'hero_title'       => 'Instagram Story Downloader',
                'hero_highlight'   => 'Download Stories Anonymously',
                'subtitle'         => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'badge'            => 'Anonymous Story Downloads',
                'placeholder'      => 'Paste Instagram Story URL here...',
                'formats'          => ['Anonymous View', 'Expired Stories', 'Story Videos', 'HD Photos', 'Instant Save'],
                'is_active'        => true,
            ],
            [
                'slug'             => 'carousel',
                'title'            => 'Instagram Carousel Downloader',
                'meta_title'       => 'Instagram Carousel Downloader - Download Multiple Photos/Videos | IGReelDownloader.net',
                'meta_description' => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'meta_keywords'    => 'instagram carousel downloader, download multiple photos, bulk download, carousel saver',
                'hero_title'       => 'Instagram Carousel Downloader',
                'hero_highlight'   => 'Download All Carousel Items',
                'subtitle'         => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'badge'            => 'Bulk Carousel Downloads',
                'placeholder'      => 'Paste Instagram Carousel URL here...',
                'formats'          => ['Bulk Download', 'Multi-Photo', 'Video Slides', 'ZIP Archive', 'Sequential Order'],
                'is_active'        => true,
            ],
            [
                'slug'             => 'highlights',
                'title'            => 'Instagram Highlights Downloader',
                'meta_title'       => 'Instagram Highlights Downloader - Download IG Highlights | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Highlights to your device. Save permanent stories from profiles in HD quality.',
                'meta_keywords'    => 'instagram highlights downloader, download ig highlights, story archive saver, instagram highlight viewer',
                'hero_title'       => 'Instagram Highlights Downloader',
                'hero_highlight'   => 'Download Highlights',
                'subtitle'         => 'Download Instagram Highlights easily. Save your favorite profile highlights forever.',
                'badge'            => 'Free Highlights Downloader',
                'placeholder'      => 'Paste Instagram Highlight URL here...',
                'formats'          => ['Story Highlights', 'Profile Archive', 'High Definition', 'Permanent Access', 'Anonymous'],
                'is_active'        => true,
            ],
            [
                'slug'             => 'privacy-policy',
                'title'            => 'Privacy Policy',
                'meta_title'       => 'Privacy Policy - IGReelDownloader.net',
                'meta_description' => 'Read our Privacy Policy to understand how IGReelDownloader.net collects, uses, and protects your information.',
                'content'          => '',
                'is_active'        => true,
            ],
            [
                'slug'             => 'terms',
                'title'            => 'Terms of Service',
                'meta_title'       => 'Terms of Service - IGReelDownloader.net',
                'meta_description' => 'Read our Terms of Service to understand the rules and guidelines for using IGReelDownloader.net.',
                'content'          => '',
                'is_active'        => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }

        $this->command->info('  - Created ' . count($pages) . ' pages');
    }

    private function createFaqs(): void
    {
        $faqs = [
            // Home page FAQs
            ['page_slug' => 'home', 'question' => 'What is IG Reel Downloader?', 'answer' => 'IG Reel Downloader is the best free online tool to download Instagram Reels, Videos, Photos, Stories, and Carousel posts in HD quality. No login or registration required.', 'order' => 1],
            ['page_slug' => 'home', 'question' => 'How do I download Instagram content?', 'answer' => 'Simply copy the Instagram URL (Reel, Video, Photo, Story, or Carousel), paste it in the input field above, and click Download. Your content will be ready in seconds.', 'order' => 2],
            ['page_slug' => 'home', 'question' => 'Is IG Reel Downloader free to use?', 'answer' => 'Yes! IG Reel Downloader is completely free with no hidden charges, no subscription fees, and unlimited downloads.', 'order' => 3],
            ['page_slug' => 'home', 'question' => 'What quality can I download in?', 'answer' => 'We always provide the highest quality available - typically 1080p HD for videos and original resolution for photos.', 'order' => 4],
            ['page_slug' => 'home', 'question' => 'Do I need to login to download?', 'answer' => "No, you don't need to login or create an account. Just paste the URL and download instantly.", 'order' => 5],
            ['page_slug' => 'home', 'question' => 'Can I download from private accounts?', 'answer' => "No, only public content can be downloaded. Private account content requires the owner's permission.", 'order' => 6],
            ['page_slug' => 'home', 'question' => 'Does IG Reel Downloader work on mobile?', 'answer' => 'Yes! Our downloader works perfectly on all devices including smartphones, tablets, and desktop computers.', 'order' => 7],
            ['page_slug' => 'home', 'question' => 'Is it safe to use IG Reel Downloader?', 'answer' => "Absolutely! We don't store any of your data or downloaded content. Your privacy is our top priority.", 'order' => 8],

            // Reels page FAQs
            ['page_slug' => 'reels', 'question' => 'How do I download Instagram Reels?', 'answer' => 'Simply copy the Reel URL from Instagram, paste it in the input field above, and click Download. Your Reel will be ready in seconds.', 'order' => 1],
            ['page_slug' => 'reels', 'question' => 'Is downloading Reels free?', 'answer' => 'Yes, our Instagram Reels downloader is completely free with no hidden charges or subscription fees.', 'order' => 2],
            ['page_slug' => 'reels', 'question' => 'What quality can I download Reels in?', 'answer' => 'We always provide the highest quality available, typically 1080p HD or the original upload quality.', 'order' => 3],
            ['page_slug' => 'reels', 'question' => 'Do I need to login to download Reels?', 'answer' => "No, you don't need to login or create an account. Just paste the URL and download instantly.", 'order' => 4],
            ['page_slug' => 'reels', 'question' => 'Can I download Reels on mobile?', 'answer' => 'Yes! Our downloader works perfectly on all devices including smartphones and tablets.', 'order' => 5],
            ['page_slug' => 'reels', 'question' => 'Are Reels downloaded without watermark?', 'answer' => 'Yes, we download Reels in their original quality without any added watermarks.', 'order' => 6],

            // Video page FAQs
            ['page_slug' => 'video', 'question' => 'How do I download Instagram videos?', 'answer' => 'Copy the video URL from Instagram, paste it above, and click Download. We support all Instagram video formats including IGTV.', 'order' => 1],
            ['page_slug' => 'video', 'question' => 'What video formats are supported?', 'answer' => 'We support all Instagram video types: regular video posts, IGTV, and video content from carousel posts.', 'order' => 2],
            ['page_slug' => 'video', 'question' => 'Is the video quality preserved?', 'answer' => 'Yes, we always download videos in the highest available quality, up to 1080p HD.', 'order' => 3],
            ['page_slug' => 'video', 'question' => 'Can I download private account videos?', 'answer' => "No, only public videos can be downloaded. Private account content requires the owner's permission.", 'order' => 4],
            ['page_slug' => 'video', 'question' => 'Are there any download limits?', 'answer' => 'No limits! Download as many videos as you want, completely free.', 'order' => 5],
            ['page_slug' => 'video', 'question' => 'What is the video format?', 'answer' => 'Videos are downloaded in MP4 format, which is compatible with all devices and media players.', 'order' => 6],

            // Photo page FAQs
            ['page_slug' => 'photo', 'question' => 'How do I download Instagram photos?', 'answer' => 'Copy the photo post URL from Instagram, paste it in the field above, and click Download to save it in full resolution.', 'order' => 1],
            ['page_slug' => 'photo', 'question' => 'What image quality will I get?', 'answer' => 'We download photos in their original full resolution, exactly as uploaded by the creator.', 'order' => 2],
            ['page_slug' => 'photo', 'question' => 'Can I download multiple photos from a post?', 'answer' => 'Yes! For carousel posts with multiple photos, we provide a "Download All" option to save everything at once.', 'order' => 3],
            ['page_slug' => 'photo', 'question' => 'What format are photos saved in?', 'answer' => 'Photos are saved in their original format, typically JPG or PNG, maintaining full quality.', 'order' => 4],
            ['page_slug' => 'photo', 'question' => 'Can I download profile pictures?', 'answer' => 'Yes, you can download profile pictures in full resolution using our tool.', 'order' => 5],
            ['page_slug' => 'photo', 'question' => 'Is there a size limit for photos?', 'answer' => 'No, we download photos in their original size without any compression.', 'order' => 6],

            // Story page FAQs
            ['page_slug' => 'story', 'question' => 'How do I download Instagram Stories?', 'answer' => 'Copy the story URL from Instagram (or the story highlight URL), paste it above, and click Download.', 'order' => 1],
            ['page_slug' => 'story', 'question' => 'Will the user know I downloaded their story?', 'answer' => "Our tool downloads stories anonymously. The user won't be notified that you saved their content.", 'order' => 2],
            ['page_slug' => 'story', 'question' => 'Can I download story highlights?', 'answer' => 'Yes! You can download both regular stories and story highlights using our tool.', 'order' => 3],
            ['page_slug' => 'story', 'question' => 'What if the story has expired?', 'answer' => 'Unfortunately, expired stories cannot be downloaded. You need to save them before they disappear after 24 hours.', 'order' => 4],
            ['page_slug' => 'story', 'question' => 'Are story videos and photos supported?', 'answer' => 'Yes, we support both photo and video stories in their original quality.', 'order' => 5],
            ['page_slug' => 'story', 'question' => 'Can I download stories from private accounts?', 'answer' => 'No, only stories from public accounts can be downloaded.', 'order' => 6],

            // Carousel page FAQs
            ['page_slug' => 'carousel', 'question' => 'What is a carousel post?', 'answer' => 'A carousel is an Instagram post containing multiple photos or videos that you can swipe through. We can download all items at once.', 'order' => 1],
            ['page_slug' => 'carousel', 'question' => 'How many items can I download at once?', 'answer' => 'Instagram allows up to 10 items per carousel, and we can download all of them in a single click.', 'order' => 2],
            ['page_slug' => 'carousel', 'question' => 'Will I get all items from the carousel?', 'answer' => 'Yes! We detect and download every photo and video in the carousel, providing them in a convenient ZIP file.', 'order' => 3],
            ['page_slug' => 'carousel', 'question' => 'What if the carousel has both photos and videos?', 'answer' => 'No problem! We handle mixed carousel posts and download all content types in their original quality.', 'order' => 4],
            ['page_slug' => 'carousel', 'question' => 'Can I download individual items?', 'answer' => 'Yes, you can choose to download items individually or use "Download All" to get everything as a ZIP.', 'order' => 5],
            ['page_slug' => 'carousel', 'question' => 'What format is the download?', 'answer' => 'Individual items download in their original format. "Download All" creates a ZIP archive.', 'order' => 6],

            // Highlights page FAQs
            ['page_slug' => 'highlights', 'question' => 'How do I download Instagram Highlights?', 'answer' => 'Copy the highlight URL from an Instagram profile, paste it above, and click Download.', 'order' => 1],
            ['page_slug' => 'highlights', 'question' => 'Can I download all highlights at once?', 'answer' => 'Currently we support downloading individual highlight items. You can paste the link to a specific highlight story.', 'order' => 2],
            ['page_slug' => 'highlights', 'question' => 'Is it anonymous?', 'answer' => 'Yes, the user will not know you viewed or downloaded their highlight.', 'order' => 3],
            ['page_slug' => 'highlights', 'question' => 'Do I need an account?', 'answer' => 'No, you don\'t need to login to download public highlights.', 'order' => 4],
            ['page_slug' => 'highlights', 'question' => 'What about private accounts?', 'answer' => 'We can only download highlights from public Instagram accounts.', 'order' => 5],
        ];

        $createdCount = 0;
        foreach ($faqs as $faq) {
            // Get page_id if page exists
            $page             = Page::where('slug', $faq['page_slug'])->first();
            $faq['page_id']   = $page?->id;
            $faq['is_active'] = true;

            Faq::updateOrCreate(
                ['page_slug' => $faq['page_slug'], 'question' => $faq['question']],
                $faq
            );
            $createdCount++;
        }

        $this->command->info('  - Created ' . $createdCount . ' FAQs');
    }
}
