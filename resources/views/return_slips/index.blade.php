@extends('layouts.master')

@section('body')
<div class="container-fluid return-slip-page">
    <div class="return-slip-hero">
        <div>
            <span class="return-slip-kicker">Property Accountability</span>
            <h1>Return Slip</h1>
            <p>
                Submit property items for transfer or unserviceable confirmation.
                Administrator and Supply Officer accounts can review and confirm pending slips.
            </p>
        </div>
        @if(!in_array(auth()->user()->role, ['Administrator', 'Supply Officer']))
            <a href="{{ route('returnSlips.create') }}" class="btn btn-light return-slip-action">
                <i class="fas fa-plus"></i> New Return Slip
            </a>
        @endif
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="return-slip-stat">
                <span>Pending</span>
                <strong>{{ number_format($pendingCount) }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="return-slip-stat">
                <span>Confirmed</span>
                <strong>{{ number_format($approvedCount) }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="return-slip-stat">
                <span>Total Slips</span>
                <strong>{{ number_format($slips->count()) }}</strong>
            </div>
        </div>
    </div>

    <div class="card return-slip-card">
        <div class="card-header">
            <h3 class="card-title">Return Slip Requests</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Slip No.</th>
                        <th>Requested By</th>
                        <th>Type</th>
                        <th>Items</th>
                        <th>Target Office/Campus</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slips as $slip)
                        <tr>
                            <td>RS-{{ str_pad($slip->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $slip->requester_name ?: $slip->requested_by }}</td>
                            <td>{{ $slip->request_type }}</td>
                            <td>{{ $slip->items_count }}</td>
                            <td>{{ $slip->request_type === 'Transfer' ? ($slip->target_office_name ?? 'Office pending') : 'Mark as Unserviceable' }}</td>
                            <td>
                                <span class="return-slip-badge status-{{ str_replace(' ', '-', strtolower($slip->status)) }}">
                                    {{ $slip->status }}
                                </span>
                            </td>
                            <td>{{ optional($slip->created_at)->format('M d, Y') }}</td>
                            <td class="text-right">
                                <a href="{{ route('returnSlips.show', $slip->id) }}" class="btn btn-sm btn-outline-success">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No return slips found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
