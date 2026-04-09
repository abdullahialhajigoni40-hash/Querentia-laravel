@extends('layouts.app')

@section('title', 'Comment Reports')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Comment Reports</h1>
        </div>

        @if (session('status') === 'report-submitted')
            <div class="mt-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3">
                Report submitted.
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reports as $report)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $report->status === 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $report->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $report->reason }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="font-medium text-gray-900">{{ $report->comment?->user?->full_name }}</div>
                                <div class="text-gray-600">{{ \Illuminate\Support\Str::limit($report->comment?->content ?? '', 120) }}</div>
                                @if($report->details)
                                    <div class="text-gray-500 mt-1">Details: {{ \Illuminate\Support\Str::limit($report->details, 120) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $report->reporter?->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->created_at?->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <form method="POST" action="{{ route('admin.comment-reports.resolve', $report) }}" class="inline-flex items-center space-x-2">
                                    @csrf
                                    <select name="status" class="border-gray-300 rounded-lg text-sm">
                                        <option value="open" {{ $report->status === 'open' ? 'selected' : '' }}>open</option>
                                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>resolved</option>
                                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>dismissed</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $reports->links() }}
        </div>
    </div>
</div>
@endsection
