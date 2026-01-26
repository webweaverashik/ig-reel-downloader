@extends('admin.layouts.app')

@section('title', 'View Message')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.messages.index') }}"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Messages
        </a>
        <h1 class="text-2xl font-bold mt-2">Message Details</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Message Content -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold">{{ $message->subject_label }}</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">From: {{ $message->name }}
                                &lt;{{ $message->email }}&gt;</p>
                        </div>
                        <span
                            class="px-3 py-1 text-sm font-medium rounded-full 
                        {{ $message->status === 'new' ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : '' }}
                        {{ $message->status === 'read' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : '' }}
                        {{ $message->status === 'replied' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : '' }}
                        {{ $message->status === 'archived' ? 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' : '' }}">
                            {{ ucfirst($message->status) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    @if ($message->url)
                        <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <span class="text-sm text-gray-500">Related URL:</span>
                            <a href="{{ $message->url }}" target="_blank"
                                class="text-violet-600 dark:text-violet-400 hover:underline block truncate">{{ $message->url }}</a>
                        </div>
                    @endif

                    <div class="prose dark:prose-invert max-w-none">
                        <p class="whitespace-pre-wrap">{{ $message->message }}</p>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex flex-wrap gap-2">
                        <a href="mailto:{{ $message->email }}?subject=Re: {{ urlencode($message->subject_label) }}"
                            class="instagram-gradient text-white font-medium px-4 py-2 rounded-lg hover:opacity-90 transition-opacity inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Reply via Email
                        </a>

                        <form action="{{ route('admin.messages.status', $message) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="replied">
                            <button type="submit"
                                class="px-4 py-2 rounded-lg border border-green-500 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                Mark as Replied
                            </button>
                        </form>

                        <form action="{{ route('admin.messages.status', $message) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="archived">
                            <button type="submit"
                                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                Archive
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 mt-6 p-6">
                <h3 class="font-semibold mb-4">Admin Notes</h3>
                <form action="{{ route('admin.messages.notes', $message) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <textarea name="admin_notes" rows="3"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                        placeholder="Add internal notes about this message...">{{ $message->admin_notes }}</textarea>
                    <button type="submit"
                        class="mt-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        Save Notes
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="font-semibold mb-4">Message Info</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Received</dt>
                        <dd class="font-medium">{{ $message->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    @if ($message->read_at)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Read At</dt>
                            <dd class="font-medium">{{ $message->read_at->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($message->replied_at)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Replied At</dt>
                            <dd class="font-medium">{{ $message->replied_at->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">IP Address</dt>
                        <dd class="font-medium font-mono text-xs">{{ $message->ip_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">User Agent</dt>
                        <dd class="font-medium text-xs break-all">{{ Str::limit($message->user_agent, 100) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Delete -->
            <div class="mt-6">
                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this message?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full px-4 py-2 rounded-lg border border-red-300 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        Delete Message
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
