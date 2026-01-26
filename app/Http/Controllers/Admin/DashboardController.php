<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Page;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_pages'     => Page::count(),
            'total_faqs'      => Faq::count(),
            'total_users'     => User::count(),
            'new_messages'    => ContactMessage::new ()->count(),
            'unread_messages' => ContactMessage::unread()->count(),
        ];

        $recentMessages = ContactMessage::latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentMessages', 'recentUsers'));
    }
}
