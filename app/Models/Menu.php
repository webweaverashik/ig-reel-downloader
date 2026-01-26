<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get menu items
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /**
     * Get active menu items
     */
    public function activeItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Get menu by slug with caching
     */
    public static function getBySlug(string $slug): ?self
    {
        return Cache::remember("menu_{$slug}", 3600, function () use ($slug) {
            return static::where('slug', $slug)
                ->where('is_active', true)
                ->with(['activeItems.page'])
                ->first();
        });
    }

    /**
     * Get menu items for display
     */
    public static function getItems(string $slug): array
    {
        $menu = static::getBySlug($slug);

        if (! $menu) {
            return [];
        }

        return $menu->activeItems->map(function ($item) {
            return [
                'title'     => $item->title,
                'url'       => $item->getUrl(),
                'icon'      => $item->icon,
                'target'    => $item->target,
                'is_active' => $item->isCurrentPage(),
            ];
        })->toArray();
    }

    /**
     * Clear menu cache
     */
    public static function clearCache(?string $slug = null): void
    {
        if ($slug) {
            Cache::forget("menu_{$slug}");
        } else {
            $menus = static::all();
            foreach ($menus as $menu) {
                Cache::forget("menu_{$menu->slug}");
            }
        }
    }
}
