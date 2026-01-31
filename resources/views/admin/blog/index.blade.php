@extends('admin.layouts.app')

@section('title', 'Blog Posts')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Blog Posts</h1>
            <p class="text-gray-500 dark:text-gray-400">Manage your blog content</p>
        </div>
        <a href="{{ route('admin.blog.create') }}"
            class="instagram-gradient text-white font-medium px-4 py-2 rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center sm:justify-start w-full sm:w-auto">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Post
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Post</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                            Author</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                            Published</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($posts as $post)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    @if ($post->featured_image)
                                        <img src="{{ $post->getImageUrl() }}" alt=""
                                            class="w-10 h-10 rounded-lg object-cover hidden sm:block">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hidden sm:flex">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white line-clamp-1">
                                            {{ $post->title }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                            {{ $post->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell text-sm text-gray-500 dark:text-gray-400">
                                {{ $post->user->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($post->is_active)
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">Published</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 hidden sm:table-cell text-sm text-gray-500 dark:text-gray-400">
                                {{ $post->published_at ? $post->published_at->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                    class="p-1.5 text-gray-500 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors inline-block"
                                    title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.blog.edit', $post->id) }}"
                                    class="p-1.5 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors inline-block"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                                        title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No blog posts found. <a href="{{ route('admin.blog.create') }}"
                                    class="text-violet-600 hover:underline">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
