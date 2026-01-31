<?php
namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    /**
     * Display blog listing page
     */
    public function index()
    {
        $posts = BlogPost::published()
            ->latest('published_at')
            ->paginate(12);

        return view('blog.index', compact('posts'));
    }

    /**
     * Display a single blog post
     */
    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Get previous post (older)
        $previousPost = BlogPost::published()
            ->where('published_at', '<', $post->published_at)
            ->latest('published_at')
            ->first();

        // Get next post (newer)
        $nextPost = BlogPost::published()
            ->where('published_at', '>', $post->published_at)
            ->oldest('published_at')
            ->first();

        // Get related posts (excluding current)
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'previousPost', 'nextPost', 'relatedPosts'));
    }
}
