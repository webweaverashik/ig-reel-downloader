<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of pages
     */
    public function index()
    {
        $pages = Page::orderBy('slug')->paginate(20);
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created page
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug'             => 'required|string|max:100|unique:pages,slug',
            'title'            => 'required|string|max:255',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'hero_title'       => 'nullable|string|max:255',
            'hero_highlight'   => 'nullable|string|max:255',
            'subtitle'         => 'nullable|string|max:500',
            'badge'            => 'nullable|string|max:100',
            'placeholder'      => 'nullable|string|max:255',
            'formats'          => 'nullable|string',
            'content'          => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        // Convert formats to array
        if (! empty($validated['formats'])) {
            $validated['formats'] = array_map('trim', explode(',', $validated['formats']));
        }

        $validated['is_active'] = $request->boolean('is_active');

        Page::create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    /**
     * Show the form for editing the specified page
     */
    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified page
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'slug'             => 'required|string|max:100|unique:pages,slug,' . $page->id,
            'title'            => 'required|string|max:255',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'hero_title'       => 'nullable|string|max:255',
            'hero_highlight'   => 'nullable|string|max:255',
            'subtitle'         => 'nullable|string|max:500',
            'badge'            => 'nullable|string|max:100',
            'placeholder'      => 'nullable|string|max:255',
            'formats'          => 'nullable|string',
            'content'          => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        // Convert formats to array
        if (! empty($validated['formats'])) {
            $validated['formats'] = array_map('trim', explode(',', $validated['formats']));
        } else {
            $validated['formats'] = [];
        }

        $validated['is_active'] = $request->boolean('is_active');

        $page->update($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page
     */
    public function destroy(Page $page)
    {
        // Prevent deletion of core pages
        $corePages = ['home', 'reels', 'video', 'photo', 'story', 'carousel', 'privacy-policy', 'terms'];

        if (in_array($page->slug, $corePages)) {
            return back()->with('error', 'Cannot delete core pages.');
        }

        $page->delete();

        return back()->with('success', 'Page deleted successfully.');
    }
}
