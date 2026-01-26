<?php
namespace App\Console\Commands;

use App\Models\Faq;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Console\Command;

class CheckDatabaseData extends Command
{
    protected $signature   = 'db:check';
    protected $description = 'Check if database has been seeded with admin data';

    public function handle()
    {
        $this->info('Checking database data...');
        $this->newLine();

        // Check Users
        $userCount  = User::count();
        $adminCount = User::whereIn('role', ['admin', 'super_admin'])->count();
        $this->info("Users: {$userCount} total, {$adminCount} admins");

        // Check Pages
        $pageCount = Page::count();
        $this->info("Pages: {$pageCount}");

        if ($pageCount > 0) {
            $this->table(['Slug', 'Title', 'Active'], Page::all(['slug', 'title', 'is_active'])->toArray());
        }

        // Check FAQs
        $faqCount       = Faq::count();
        $activeFaqCount = Faq::where('is_active', true)->count();
        $this->info("FAQs: {$faqCount} total, {$activeFaqCount} active");

        if ($faqCount > 0) {
            $faqsByPage = Faq::selectRaw('page_slug, count(*) as count')->groupBy('page_slug')->pluck('count', 'page_slug')->toArray();

            $this->table(['Page Slug', 'FAQ Count'], collect($faqsByPage)->map(fn($count, $slug) => ['slug' => $slug, 'count' => $count])->values()->toArray());
        }

        // Check Settings
        $settingCount = SiteSetting::count();
        $this->info("Settings: {$settingCount}");

        $this->newLine();

        if ($pageCount === 0 || $faqCount === 0) {
            $this->warn('Database appears to be empty. Run: php artisan db:seed');
        } else {
            $this->info('Database has been seeded successfully!');
        }

        return 0;
    }
}