<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs
     */
    public function index(Request $request)
    {
        $query = Faq::query();

        // Filter by page_slug if provided
        if ($request->filled('page_slug')) {
            $query->where('page_slug', $request->page_slug);
        }

        // Clone query for counting before pagination
        $totalFaqs     = Faq::count();
        $activeFaqs    = Faq::where('is_active', true)->count();
        $inactiveFaqs  = Faq::where('is_active', false)->count();
        $pagesWithFaqs = Faq::distinct('page_slug')->count('page_slug');

        $faqs = $query->orderBy('page_slug')
            ->orderBy('order')
            ->orderBy('id')
            ->paginate(50);

        // Get all pages for the dropdown
        $pages = Page::orderBy('slug')->pluck('title', 'slug');

        // If no pages exist, provide default page slugs
        if ($pages->isEmpty()) {
            $pages = collect([
                'home'     => 'Home',
                'reels'    => 'Reels Downloader',
                'video'    => 'Video Downloader',
                'photo'    => 'Photo Downloader',
                'story'    => 'Story Downloader',
                'carousel' => 'Carousel Downloader',
            ]);
        }

        $stats = [
            'total'    => $totalFaqs,
            'active'   => $activeFaqs,
            'inactive' => $inactiveFaqs,
            'pages'    => $pagesWithFaqs,
        ];

        return view('admin.faqs.index', compact('faqs', 'pages', 'stats'));
    }

    /**
     * Show the form for creating a new FAQ
     */
    public function create(Request $request)
    {
        $pages = Page::orderBy('slug')->pluck('title', 'slug');

        // If no pages exist, provide default page slugs
        if ($pages->isEmpty()) {
            $pages = collect([
                'home'     => 'Home',
                'reels'    => 'Reels Downloader',
                'video'    => 'Video Downloader',
                'photo'    => 'Photo Downloader',
                'story'    => 'Story Downloader',
                'carousel' => 'Carousel Downloader',
            ]);
        }

        $pageSlug = $request->get('page_slug', '');

        return view('admin.faqs.create', compact('pages', 'pageSlug'));
    }

    /**
     * Store a newly created FAQ
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'page_slug' => 'required|string|max:100',
            'question'  => 'required|string|max:500',
            'answer'    => 'required|string',
            'order'     => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['order']     = $validated['order'] ?? 0;

        // Link to page if exists
        $page = Page::where('slug', $validated['page_slug'])->first();
        if ($page) {
            $validated['page_id'] = $page->id;
        }

        Faq::create($validated);

        return redirect()->route('admin.faqs.index', ['page_slug' => $validated['page_slug']])
            ->with('success', 'FAQ created successfully.');
    }

    /**
     * Show the form for editing the specified FAQ
     */
    public function edit(Faq $faq)
    {
        $pages = Page::orderBy('slug')->pluck('title', 'slug');

        // If no pages exist, provide default page slugs
        if ($pages->isEmpty()) {
            $pages = collect([
                'home'     => 'Home',
                'reels'    => 'Reels Downloader',
                'video'    => 'Video Downloader',
                'photo'    => 'Photo Downloader',
                'story'    => 'Story Downloader',
                'carousel' => 'Carousel Downloader',
            ]);
        }

        return view('admin.faqs.edit', compact('faq', 'pages'));
    }

    /**
     * Update the specified FAQ
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'page_slug' => 'required|string|max:100',
            'question'  => 'required|string|max:500',
            'answer'    => 'required|string',
            'order'     => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['order']     = $validated['order'] ?? 0;

        // Link to page if exists
        $page                 = Page::where('slug', $validated['page_slug'])->first();
        $validated['page_id'] = $page?->id;

        $faq->update($validated);

        return redirect()->route('admin.faqs.index', ['page_slug' => $validated['page_slug']])
            ->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ
     */
    public function destroy(Faq $faq)
    {
        $pageSlug = $faq->page_slug;
        $faq->delete();

        return redirect()->route('admin.faqs.index', ['page_slug' => $pageSlug])
            ->with('success', 'FAQ deleted successfully.');
    }

    /**
     * Reorder FAQs via AJAX
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'orders'         => 'required|array',
            'orders.*.id'    => 'required|exists:faqs,id',
            'orders.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $item) {
            Faq::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}