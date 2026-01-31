@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Users
        </a>
        <h1 class="text-2xl font-bold mt-2">Edit User: {{ $user->name }}</h1>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="max-w-2xl">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition-colors"
                        placeholder="Full Name">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition-colors"
                        placeholder="user@example.com">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">New Password</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition-colors"
                            placeholder="Leave blank to keep current">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition-colors"
                            placeholder="••••••••">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Role <span class="text-red-500">*</span></label>
                    <select name="role" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition-colors">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        @if (auth()->user()->role === 'super_admin')
                            <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>
                                Super Admin</option>
                        @endif
                    </select>
                    @error('role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-800">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                            {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 dark:peer-focus:ring-violet-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600 {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}">
                        </div>
                        <span class="ml-3 text-sm font-medium">Active</span>
                        @if ($user->id === auth()->id())
                            <span class="ml-2 text-xs text-gray-500">(Cannot change your own status)</span>
                            <input type="hidden" name="is_active" value="1">
                        @endif
                    </label>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.users.index') }}"
                            class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="instagram-gradient text-white font-medium px-6 py-2 rounded-lg hover:opacity-90 transition-opacity">
                            Update User
                        </button>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-sm text-gray-500 dark:text-gray-400">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><strong class="text-gray-700 dark:text-gray-300">Created:</strong>
                            {{ $user->created_at->format('M d, Y H:i') }}</p>
                        <p class="mt-1"><strong class="text-gray-700 dark:text-gray-300">Updated:</strong>
                            {{ $user->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p><strong class="text-gray-700 dark:text-gray-300">Last Login:</strong>
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</p>
                        @if ($user->last_login_ip)
                            <p class="mt-1"><strong class="text-gray-700 dark:text-gray-300">Last IP:</strong>
                                {{ $user->last_login_ip }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            @if ($user->id !== auth()->id())
                <div class="mt-6 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-4">
                    <h3 class="text-red-600 dark:text-red-400 font-semibold mb-2">Danger Zone</h3>
                    <p class="text-sm text-red-600 dark:text-red-400 mb-3">Once you delete a user, there is no going back.
                        Please be certain.</p>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                            Delete User
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </form>
@endsection