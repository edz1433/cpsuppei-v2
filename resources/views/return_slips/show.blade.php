@extends('layouts.master')

@section('body')
<div class="container-fluid return-slip-page">
    <div class="return-slip-hero">
        <div>
            <span class="return-slip-kicker">Slip Details</span>
            <h1>RS-{{ str_pad($slip->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p>{{ $slip->request_type }} request submitted by {{ $slip->requester_name ?: $slip->requested_by }}.</p>
        </div>
        <a href="{{ route('returnSlips.index') }}" class="btn btn-light return-slip-action">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card return-slip-card">
                <div class="card-header">
                    <h3 class="card-title">Request Summary</h3>
                </div>
                <div class="card-body">
                    <dl class="return-slip-summary">
                        <dt>Status</dt>
                        <dd><span class="return-slip-badge status-{{ str_replace(' ', '-', strtolower($slip->status)) }}">{{ $slip->status }}</span></dd>
                        <dt>Request Type</dt>
                        <dd>{{ $slip->request_type }}</dd>
                        <dt>Source Campus</dt>
                        <dd>{{ $slip->source_campus_name ?? 'Main Campus / Office' }}</dd>
                        <dt>Target Office/Campus</dt>
                        <dd>{{ $slip->request_type === 'Transfer' ? ($slip->target_office_name ?? 'Not specified') : 'Not applicable' }}</dd>
                        <dt>Reason</dt>
                        <dd>{{ $slip->reason ?: 'No reason provided' }}</dd>
                        @if($slip->remarks)
                            <dt>Confirmation Notes</dt>
                            <dd>{{ $slip->remarks }}</dd>
                        @endif
                    </dl>
                    <div class="alert alert-light border mb-0">
                        Confirm or cancel each property item individually from the table. Request owners may delete pending items before Supply/Admin action.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card return-slip-card">
                <div class="card-header">
                    <h3 class="card-title">Included Properties</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Property No.</th>
                                <th>Item</th>
                                <th>Serial No.</th>
                                <th>Current Office</th>
                                <th>Item Status</th>
                                <th style="min-width: 260px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php
                                    $itemStatus = $item->status ?: 'Pending';
                                @endphp
                                <tr>
                                    <td>{{ $item->property_no_generated }}</td>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->serial_number }}</td>
                                    <td>{{ $item->current_office_name }}</td>
                                    <td>
                                        <span class="return-slip-badge status-{{ str_replace(' ', '-', strtolower($itemStatus)) }}">
                                            {{ $itemStatus }}
                                        </span>
                                        <div class="text-muted small mt-1">{{ $item->current_status ?: 'No Status' }}</div>
                                        @if($item->action_remarks)
                                            <div class="text-muted small mt-1">{{ $item->action_remarks }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if(in_array(auth()->user()->role, ['Administrator', 'Supply Officer']) && $itemStatus === 'Pending')
                                            <form action="{{ route('returnSlips.items.confirm', $item->id) }}" method="POST" class="return-slip-row-action mb-2">
                                                @csrf
                                                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Optional note">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                            <form action="{{ route('returnSlips.items.cancel', $item->id) }}" method="POST" class="return-slip-row-action">
                                                @csrf
                                                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Cancel note">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        @elseif($itemStatus === 'Pending' && $slip->user_id === auth()->id())
                                            <form action="{{ route('returnSlips.items.delete', $item->id) }}" method="POST" class="return-slip-row-action">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">No action available</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
