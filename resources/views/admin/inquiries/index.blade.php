@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Contact & Business Leads</h1>
        <p class="text-xs text-slate-500">Messages and corporate account applications submitted from the frontend.</p>
    </div>

    <div class="space-y-4">
        @forelse ($inquiries as $inquiry)
            <article class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-extrabold px-2 py-1 rounded-full {{ $inquiry->inquiry_type === 'business_account' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ str_replace('_', ' ', $inquiry->inquiry_type) }}
                            </span>
                            <span class="text-[10px] uppercase font-bold text-slate-500">{{ $inquiry->status }}</span>
                        </div>
                        <h2 class="text-lg font-extrabold text-slate-900 mt-2">{{ $inquiry->name }}</h2>
                        <p class="text-xs text-slate-600">{{ $inquiry->email }} · {{ $inquiry->phone }}</p>
                    </div>
                    <span class="text-xs text-slate-400">{{ $inquiry->created_at->format('d M Y, H:i') }}</span>
                </div>

                @if ($inquiry->company_name)
                    <div class="grid sm:grid-cols-3 gap-3 text-xs bg-slate-50 rounded-xl p-4">
                        <div><strong>Company:</strong> {{ $inquiry->company_name }}</div>
                        <div><strong>Registration:</strong> {{ $inquiry->company_registration }}</div>
                        <div><strong>Monthly deliveries:</strong> {{ $inquiry->monthly_deliveries ?: 'Not specified' }}</div>
                    </div>
                @endif

                @if ($inquiry->subject || $inquiry->message)
                    <div class="text-xs text-slate-700">
                        @if ($inquiry->subject)<p class="font-bold">{{ $inquiry->subject }}</p>@endif
                        @if ($inquiry->message)<p class="mt-1 whitespace-pre-line">{{ $inquiry->message }}</p>@endif
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.inquiries.status', $inquiry) }}" class="flex items-center gap-3">
                    @csrf
                    <select name="status" class="text-xs border border-slate-300 rounded-lg px-3 py-2">
                        @foreach (['new', 'in_progress', 'closed'] as $status)
                            <option value="{{ $status }}" @selected($inquiry->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <button class="text-xs font-bold bg-[#0a192f] text-white rounded-lg px-4 py-2">Update status</button>
                </form>
            </article>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-sm text-slate-500">No inquiries received yet.</div>
        @endforelse
    </div>

    {{ $inquiries->links() }}
</div>
@endsection
