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
        $query = Faq::with('page');

        if ($request->filled('page_slug')) {
            $query->where('page_slug', $request->page_slug);
        }

        $faqs  = $query->orderBy('page_slug')->orderBy('order')->paginate(20);
        $pages = Page::orderBy('slug')->pluck('title', 'slug');

        return view('admin.faqs.index', compact('faqs', 'pages'));
    }

    /**
     * Show the form for creating a new FAQ
     */
    public function create()
    {
        $pages    = Page::orderBy('slug')->pluck('title', 'slug');
        $pageSlug = request('page_slug');
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
            'order'     => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
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
            'order'     => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

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
     * Reorder FAQs
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
