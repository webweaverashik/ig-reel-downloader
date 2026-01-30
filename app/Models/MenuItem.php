<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'page_id',
        'title',
        'url',
        'icon',
        'target',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clear menu cache when menu item is created
        static::created(function ($item) {
            if ($item->menu) {
                Menu::clearCache($item->menu->slug);
            }
        });

        // Clear menu cache when menu item is updated
        static::updated(function ($item) {
            if ($item->menu) {
                Menu::clearCache($item->menu->slug);
            }
        });

        // Clear menu cache when menu item is deleted
        static::deleted(function ($item) {
            if ($item->menu) {
                Menu::clearCache($item->menu->slug);
            }
        });
    }

    /**
     * Get the menu this item belongs to
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the page this item links to
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the URL for this menu item
     */
    public function getUrl(): string
    {
        // If custom URL is set, use it
        if ($this->url) {
            return $this->url;
        }

        // If linked to a page, generate URL based on page slug
        if ($this->page) {
            return $this->generatePageUrl($this->page->slug);
        }

        return '#';
    }

    /**
     * Generate URL based on page slug
     */
    protected function generatePageUrl(string $slug): string
    {
        $routeMap = [
            'home' => 'home',
            'reels' => 'instagram.reels',
            'video' => 'instagram.video',
            'photo' => 'instagram.photo',
            'story' => 'instagram.story',
            'carousel' => 'instagram.carousel',
            'highlights' => 'instagram.highlights',
            'privacy-policy' => 'privacy-policy',
            'terms' => 'terms',
            'contact' => 'contact',
        ];

        $routeName = $routeMap[$slug] ?? null;

        if ($routeName && \Route::has($routeName)) {
            return route($routeName);
        }

        return url($slug);
    }

    /**
     * Check if this menu item is for the current page
     */
    public function isCurrentPage(): bool
    {
        $currentUrl = request()->url();
        $itemUrl = $this->getUrl();

        return $currentUrl === $itemUrl;
    }
}