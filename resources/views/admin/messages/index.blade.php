@extends('admin.layouts.app')

@section('title', 'Messages')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Contact Messages</h1>
        <p class="text-gray-500 dark:text-gray-400">Manage messages from the contact form</p>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.messages.index') }}"
            class="px-4 py-2 rounded-lg {{ !request('status') ? 'instagram-gradient text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors">
            All ({{ $statusCounts['all'] }})
        </a>
        <a href="{{ route('admin.messages.index', ['status' => 'new']) }}"
            class="px-4 py-2 rounded-lg {{ request('status') === 'new' ? 'instagram-gradient text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors">
            New ({{ $statusCounts['new'] }})
        </a>
        <a href="{{ route('admin.messages.index', ['status' => 'read']) }}"
            class="px-4 py-2 rounded-lg {{ request('status') === 'read' ? 'instagram-gradient text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors">
            Read ({{ $statusCounts['read'] }})
        </a>
        <a href="{{ route('admin.messages.index', ['status' => 'replied']) }}"
            class="px-4 py-2 rounded-lg {{ request('status') === 'replied' ? 'instagram-gradient text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors">
            Replied ({{ $statusCounts['replied'] }})
        </a>
        <a href="{{ route('admin.messages.index', ['status' => 'archived']) }}"
            class="px-4 py-2 rounded-lg {{ request('status') === 'archived' ? 'instagram-gradient text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors">
            Archived ({{ $statusCounts['archived'] }})
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            From</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Subject</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($messages as $message)
                        <tr
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $message->status === 'new' ? 'bg-violet-50 dark:bg-violet-900/10' : '' }}">
                            <td class="px-6 py-4">
                                <div
                                    class="font-medium {{ $message->status === 'new' ? 'text-violet-600 dark:text-violet-400' : '' }}">
                                    {{ $message->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $message->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ $message->subject_label }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                    {{ Str::limit($message->message, 50) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($message->status === 'new')
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">New</span>
                                @elseif($message->status === 'read')
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">Read</span>
                                @elseif($message->status === 'replied')
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">Replied</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Archived</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $message->created_at->format('M d, Y') }}
                                <div class="text-xs">{{ $message->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.messages.show', $message) }}"
                                    class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400" title="View">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST"
                                    class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-500 hover:text-red-600 dark:hover:text-red-400"
                                        title="Delete">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
                                No messages found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $messages->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
