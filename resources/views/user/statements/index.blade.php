@extends('layouts.app')
@section('heading', 'Statements')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Period</th>
                <th class="text-end">Opening</th>
                <th class="text-end">Credits</th>
                <th class="text-end">Debits</th>
                <th class="text-end">Closing</th>
                <th>Status</th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse ($statements as $s)
                <tr>
                    <td>{{ $s->period_start->format('Y-m-d') }} → {{ $s->period_end->format('Y-m-d') }}</td>
                    <td class="text-end">{{ number_format($s->opening_balance) }}</td>
                    <td class="text-end text-success">{{ number_format($s->total_credits) }}</td>
                    <td class="text-end text-danger">{{ number_format($s->total_debits) }}</td>
                    <td class="text-end">{{ number_format($s->closing_balance) }}</td>
                    <td class="text-capitalize">{{ $s->status }}</td>
                    <td>
                        @if ($s->pdf_path)
                            <a href="{{ route('user.statements.download', $s) }}" class="btn btn-sm btn-outline-primary">PDF</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No statements yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.pagination', ['paginator' => $statements])
@endsection