<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of messages
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subject
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->paginate(20);

        $statusCounts = [
            'all'      => ContactMessage::count(),
            'new'      => ContactMessage::where('status', 'new')->count(),
            'read'     => ContactMessage::where('status', 'read')->count(),
            'replied'  => ContactMessage::where('status', 'replied')->count(),
            'archived' => ContactMessage::where('status', 'archived')->count(),
        ];

        return view('admin.messages.index', compact('messages', 'statusCounts'));
    }

    /**
     * Display the specified message
     */
    public function show(ContactMessage $message)
    {
        // Mark as read when viewed
        $message->markAsRead();

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Update message status
     */
    public function updateStatus(Request $request, ContactMessage $message)
    {
        $request->validate([
            'status' => 'required|in:new,read,replied,archived',
        ]);

        $message->update(['status' => $request->status]);

        if ($request->status === 'replied') {
            $message->update(['replied_at' => now()]);
        }

        return back()->with('success', 'Message status updated.');
    }

    /**
     * Update admin notes
     */
    public function updateNotes(Request $request, ContactMessage $message)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $message->update(['admin_notes' => $request->admin_notes]);

        return back()->with('success', 'Notes saved.');
    }

    /**
     * Remove the specified message
     */
    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }

    /**
     * Bulk action
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:archive,delete,mark_read',
            'ids'    => 'required|array',
            'ids.*'  => 'exists:contact_messages,id',
        ]);

        $messages = ContactMessage::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'archive':
                $messages->update(['status' => 'archived']);
                $msg = 'Messages archived.';
                break;
            case 'delete':
                $messages->delete();
                $msg = 'Messages deleted.';
                break;
            case 'mark_read':
                $messages->update(['status' => 'read', 'read_at' => now()]);
                $msg = 'Messages marked as read.';
                break;
            default:
                $msg = 'Action completed.';
        }

        return back()->with('success', $msg);
    }
}