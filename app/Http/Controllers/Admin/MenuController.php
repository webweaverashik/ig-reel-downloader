<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of menus
     */
    public function index()
    {
        $menus = Menu::withCount('items')->orderBy('id')->get();

        return view('admin.menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new menu
     */
    public function create()
    {
        return view('admin.menus.create');
    }

    /**
     * Store a newly created menu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|max:100|unique:menus,slug',
            'location'    => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Menu::create($validated);

        // Clear all menu caches
        Menu::clearCache();

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully.');
    }

    /**
     * Show the form for editing the specified menu
     */
    public function edit(Menu $menu)
    {
        $menu->load(['items' => function ($query) {
            $query->orderBy('order');
        }, 'items.page']);

        $pages = Page::where('is_active', true)->orderBy('title')->get();

        return view('admin.menus.edit', compact('menu', 'pages'));
    }

    /**
     * Update the specified menu
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|max:100|unique:menus,slug,' . $menu->id,
            'location'    => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldSlug = $menu->slug;
        $menu->update($validated);

        // Clear cache for both old and new slugs
        Menu::clearCache($oldSlug);
        Menu::clearCache($menu->slug);

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified menu
     */
    public function destroy(Menu $menu)
    {
        // Prevent deletion of core menus
        $coreMenus = ['main', 'footer-downloaders', 'footer-legal'];

        if (in_array($menu->slug, $coreMenus)) {
            return back()->with('error', 'Cannot delete core menus.');
        }

        $slug = $menu->slug;
        $menu->delete();

        // Clear cache
        Menu::clearCache($slug);

        return back()->with('success', 'Menu deleted successfully.');
    }

    /**
     * Add item to menu
     */
    public function addItem(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:100',
            'page_id'   => 'nullable|exists:pages,id',
            'url'       => 'nullable|string|max:500',
            'icon'      => 'nullable|string|max:50',
            'target'    => 'required|in:_self,_blank',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['menu_id']   = $menu->id;
        $validated['order']     = $menu->items()->max('order') + 1;

        // If page is selected, clear custom URL
        if (! empty($validated['page_id'])) {
            $validated['url'] = null;
        }

        MenuItem::create($validated);

        // Clear menu cache
        Menu::clearCache($menu->slug);

        return back()->with('success', 'Menu item added successfully.');
    }

    /**
     * Update menu item
     */
    public function updateItem(Request $request, MenuItem $item)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:100',
            'page_id'   => 'nullable|exists:pages,id',
            'url'       => 'nullable|string|max:500',
            'icon'      => 'nullable|string|max:50',
            'target'    => 'required|in:_self,_blank',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // If page is selected, clear custom URL
        if (! empty($validated['page_id'])) {
            $validated['url'] = null;
        }

        $menuSlug = $item->menu->slug;
        $item->update($validated);

        // Clear menu cache
        Menu::clearCache($menuSlug);

        return back()->with('success', 'Menu item updated successfully.');
    }

    /**
     * Delete menu item
     */
    public function deleteItem(MenuItem $item)
    {
        $menuSlug = $item->menu->slug;
        $item->delete();

        // Clear menu cache
        Menu::clearCache($menuSlug);

        return back()->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Reorder menu items via AJAX
     */
    public function reorderItems(Request $request, Menu $menu)
    {
        $request->validate([
            'items'   => 'required|array',
            'items.*' => 'exists:menu_items,id',
        ]);

        foreach ($request->items as $order => $itemId) {
            MenuItem::where('id', $itemId)->update(['order' => $order]);
        }

        // Clear menu cache
        Menu::clearCache($menu->slug);

        return response()->json(['success' => true]);
    }
}