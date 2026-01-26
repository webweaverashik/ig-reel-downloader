<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'url',
        'message',
        'ip_address',
        'user_agent',
        'status',
        'admin_notes',
        'read_at',
        'replied_at',
    ];

    protected $casts = [
        'read_at'    => 'datetime',
        'replied_at' => 'datetime',
    ];

    /**
     * Subject labels
     */
    public static function subjectLabels(): array
    {
        return [
            'general'     => 'General Inquiry',
            'support'     => 'Technical Support',
            'bug'         => 'Bug Report',
            'feature'     => 'Feature Request',
            'feedback'    => 'Feedback',
            'partnership' => 'Partnership/Business',
            'dmca'        => 'DMCA/Copyright',
            'privacy'     => 'Privacy Concern',
            'other'       => 'Other',
        ];
    }

    /**
     * Get subject label
     */
    public function getSubjectLabelAttribute(): string
    {
        return static::subjectLabels()[$this->subject] ?? $this->subject;
    }

    /**
     * Scope for new messages
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update([
                'read_at' => now(),
                'status'  => 'read',
            ]);
        }
    }

    /**
     * Mark as replied
     */
    public function markAsReplied(): void
    {
        $this->update([
            'replied_at' => now(),
            'status'     => 'replied',
        ]);
    }

    /**
     * Archive message
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }
}
