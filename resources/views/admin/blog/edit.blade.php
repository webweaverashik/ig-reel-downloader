@extends('admin.layouts.app')

@section('title', 'Edit Blog Post')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.blog.index') }}"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Blog
        </a>
        <h1 class="text-2xl font-bold mt-2">Edit Post: {{ $blog->title }}</h1>
    </div>

    <form action="{{ route('admin.blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data" id="blogForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $blog->title) }}"
                                required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                                placeholder="Post Title">
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Slug <span class="text-red-500">*</span></label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $blog->slug) }}"
                                required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                                placeholder="post-slug">
                            @error('slug')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Excerpt</label>
                            <textarea name="excerpt" rows="3"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                                placeholder="Short summary of the post...">{{ old('excerpt', $blog->excerpt) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Content Editor -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Post Content</h2>
                    <input type="hidden" name="content" id="content" value="{{ old('content', $blog->content) }}">
                    <textarea id="editor" class="hidden">{{ old('content', $blog->content) }}</textarea>
                    @error('content')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Publish</h2>

                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm">Status</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                {{ old('is_active', $blog->is_active) ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 dark:peer-focus:ring-violet-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600">
                            </div>
                            <span class="ml-2 text-sm">Published</span>
                        </label>
                    </div>

                    <div class="text-xs text-gray-500 mb-4">
                        Published: {{ $blog->published_at ? $blog->published_at->format('M d, Y H:i') : 'Not published' }}
                    </div>

                    <button type="submit"
                        class="w-full instagram-gradient text-white font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                        Update Post
                    </button>

                    <a href="{{ route('blog.show', $blog->slug) }}" target="_blank"
                        class="w-full mt-2 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-center block hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        View Post
                    </a>
                </div>

                <!-- Featured Image -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Featured Image</h2>

                    <div id="imagePreview" class="mb-4 {{ $blog->featured_image ? '' : 'hidden' }}">
                        <img src="{{ $blog->getImageUrl() }}" alt="Featured Image" class="w-full h-auto rounded-lg">
                    </div>

                    <div class="mb-4">
                        <input type="file" name="featured_image" id="featured_image" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 dark:file:bg-violet-900/30 dark:file:text-violet-300">
                    </div>
                    @error('featured_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SEO -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">SEO Settings</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none text-sm"
                                placeholder="SEO Title">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Meta Description</label>
                            <textarea name="meta_description" rows="3"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none text-sm resize-none"
                                placeholder="SEO Description">{{ old('meta_description', $blog->meta_description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Keywords</label>
                            <textarea name="meta_keywords" rows="2"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none text-sm resize-none"
                                placeholder="keyword1, keyword2">{{ old('meta_keywords', $blog->meta_keywords) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-red-200 dark:border-red-800 p-6">
                    <h2 class="text-lg font-semibold mb-4 text-red-600 dark:text-red-400">Danger Zone</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Once you delete a post, there is no going back. Please be certain.
                    </p>
                    <button type="button" onclick="confirmDelete()"
                        class="w-full py-2 rounded-lg border border-red-500 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        Delete Post
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Form (separate from update form) -->
    <form id="deleteForm" action="{{ route('admin.blog.destroy', $blog->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Image preview
                const imageInput = document.getElementById('featured_image');
                const imagePreview = document.getElementById('imagePreview');

                imageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.querySelector('img').src = e.target.result;
                            imagePreview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // Initialize Jodit Editor
                const editor = window.initJoditEditor('editor', {
                    syncTo: 'content',
                    autosave: false, // Don't auto-save on edit page
                    placeholder: 'Write your blog post content here...'
                });

                // Sync content before form submit
                document.getElementById('blogForm').addEventListener('submit', function(e) {
                    const contentInput = document.getElementById('content');
                    if (editor) {
                        contentInput.value = editor.value;
                    }
                });
            });

            function confirmDelete() {
                if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                    document.getElementById('deleteForm').submit();
                }
            }
        </script>
    @endpush
@endsection
