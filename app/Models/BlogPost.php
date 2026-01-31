<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'published_at',
        'user_id',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get the user who owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active and published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Get the featured image URL.
     */
    public function getImageUrl(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        if (filter_var($this->featured_image, FILTER_VALIDATE_URL)) {
            return $this->featured_image;
        }

        return asset('uploads/' . $this->featured_image);
    }
}
