<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $fillable = [
        'page_id',
        'page_slug',
        'question',
        'answer',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the page this FAQ belongs to
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Scope for active FAQs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for FAQs by page slug
     */
    public function scopeForPage($query, string $slug)
    {
        return $query->where('page_slug', $slug)->orWhereHas('page', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    /**
     * Get FAQs for a specific page
     */
    public static function getForPage(string $slug): array
    {
        return static::where('page_slug', $slug)
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(fn($faq) => ['q' => $faq->question, 'a' => $faq->answer])
            ->toArray();
    }
}
