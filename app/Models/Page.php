<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'hero_title',
        'hero_highlight',
        'subtitle',
        'badge',
        'placeholder',
        'formats',
        'content',
        'is_active',
    ];

    protected $casts = [
        'formats'   => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get FAQs for this page
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('order');
    }

    /**
     * Get active FAQs
     */
    public function activeFaqs(): HasMany
    {
        return $this->hasMany(Faq::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Find page by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Get page config for downloader pages
     */
    public function getConfig(): array
    {
        return [
            'title'            => $this->meta_title ?: $this->title,
            'meta_description' => $this->meta_description,
            'meta_keywords'    => $this->meta_keywords,
            'hero_title'       => $this->hero_title ?: $this->title,
            'hero_highlight'   => $this->hero_highlight,
            'subtitle'         => $this->subtitle,
            'badge'            => $this->badge,
            'placeholder'      => $this->placeholder,
            'formats'          => $this->formats ?: [],
            'faqs'             => $this->activeFaqs->map(fn($faq) => [
                'q' => $faq->question,
                'a' => $faq->answer,
            ])->toArray(),
        ];
    }
}
