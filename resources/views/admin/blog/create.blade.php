@extends('admin.layouts.app')

@section('title', 'Create Blog Post')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.blog.index') }}"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Blog
        </a>
        <h1 class="text-2xl font-bold mt-2">Create New Post</h1>
    </div>

    <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" id="blogForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                                placeholder="Post Title">
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Slug <span class="text-red-500">*</span></label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                                placeholder="post-slug">
                            <p class="text-xs text-gray-500 mt-1">Auto-generated from title. You can edit it manually.</p>
                            @error('slug')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Excerpt</label>
                            <textarea name="excerpt" rows="3"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                                placeholder="Short summary of the post...">{{ old('excerpt') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Content Editor -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Post Content</h2>
                    <input type="hidden" name="content" id="content" value="{{ old('content') }}">
                    <textarea id="editor" class="hidden">{{ old('content') }}</textarea>
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
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 dark:peer-focus:ring-violet-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600">
                            </div>
                            <span class="ml-2 text-sm">Published</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full instagram-gradient text-white font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                        Create Post
                    </button>
                </div>

                <!-- Featured Image -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Featured Image</h2>
                    <div id="imagePreview" class="mb-4 hidden">
                        <img src="" alt="Preview" class="w-full h-auto rounded-lg">
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
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none text-sm"
                                placeholder="SEO Title">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Meta Description</label>
                            <textarea name="meta_description" rows="3"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none text-sm resize-none"
                                placeholder="SEO Description">{{ old('meta_description') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Keywords</label>
                            <textarea name="meta_keywords" rows="2"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none text-sm resize-none"
                                placeholder="keyword1, keyword2">{{ old('meta_keywords') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-generate slug from title
                const titleInput = document.getElementById('title');
                const slugInput = document.getElementById('slug');
                let slugManuallyEdited = false;

                slugInput.addEventListener('input', function() {
                    slugManuallyEdited = true;
                });

                titleInput.addEventListener('input', function() {
                    if (!slugManuallyEdited || slugInput.value === '') {
                        const slug = this.value
                            .toLowerCase()
                            .trim()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                        slugInput.value = slug;
                    }
                });

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
                    } else {
                        imagePreview.classList.add('hidden');
                    }
                });

                // Initialize Jodit Editor
                const editor = window.initJoditEditor('editor', {
                    syncTo: 'content',
                    autosave: true,
                    placeholder: 'Write your blog post content here...'
                });

                // Sync content before form submit
                document.getElementById('blogForm').addEventListener('submit', function(e) {
                    const contentInput = document.getElementById('content');
                    if (editor) {
                        contentInput.value = editor.value;
                    }

                    // Clear draft on successful submit
                    window.clearJoditDraft && window.clearJoditDraft('editor');
                });
            });
        </script>
    @endpush
@endsection