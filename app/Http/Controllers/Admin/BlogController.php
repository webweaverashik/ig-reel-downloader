<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = BlogPost::with('user')->latest()->paginate(10);

        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.blog.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:blog_posts,slug',
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|image|max:2048',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'is_active'        => 'boolean',
        ]);

        $validated['user_id']      = auth()->id();
        $validated['is_active']    = $request->boolean('is_active');
        $validated['published_at'] = $validated['is_active'] ? now() : null;

        // Clean and sanitize content
        $validated['content'] = $this->sanitizeContent($validated['content']);

        // Handle Image Upload
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $this->uploadImage($request->file('featured_image'));
        }

        BlogPost::create($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $blog = BlogPost::findOrFail($id);

        return view('admin.blog.edit', compact('blog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $blog = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:blog_posts,slug,' . $blog->id,
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|image|max:2048',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'is_active'        => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Update published_at if activating for the first time
        if ($validated['is_active'] && ! $blog->published_at) {
            $validated['published_at'] = now();
        }

        // Clean and sanitize content
        $validated['content'] = $this->sanitizeContent($validated['content']);

        // Handle Image Upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($blog->featured_image && file_exists(public_path('uploads/' . $blog->featured_image))) {
                @unlink(public_path('uploads/' . $blog->featured_image));
            }

            $validated['featured_image'] = $this->uploadImage($request->file('featured_image'));
        }

        $blog->update($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = BlogPost::findOrFail($id);

        if ($blog->featured_image && file_exists(public_path('uploads/' . $blog->featured_image))) {
            @unlink(public_path('uploads/' . $blog->featured_image));
        }

        $blog->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted successfully.');
    }

    /**
     * Upload and store image
     */
    private function uploadImage($file): string
    {
        $filename = 'blog_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path     = public_path('uploads/blog');

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $filename);

        return 'blog/' . $filename;
    }

    /**
     * Sanitize HTML content from editor
     */
    private function sanitizeContent(string $content): string
    {
        // Remove any script tags for security
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);

        // Remove onclick, onerror and other event handlers
        $content = preg_replace('/\s*on\w+="[^"]*"/i', '', $content);
        $content = preg_replace("/\s*on\w+='[^']*'/i", '', $content);

        return $content;
    }
}