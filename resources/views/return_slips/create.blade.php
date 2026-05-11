@extends('layouts.master')

@section('body')
<div class="container-fluid return-slip-page">
    <div class="return-slip-hero">
        <div>
            <span class="return-slip-kicker">New Request</span>
            <h1>Create Return Slip</h1>
            <p>Select one or more properties from your campus and submit them for transfer or unserviceable confirmation.</p>
        </div>
        <a href="{{ route('returnSlips.index') }}" class="btn btn-light return-slip-action">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <form action="{{ route('returnSlips.store') }}" method="POST" id="returnSlipForm">
        @csrf
        <div id="selectedPropertyInputs"></div>
        <div class="row">
            <div class="col-lg-4">
                <div class="card return-slip-card">
                    <div class="card-header">
                        <h3 class="card-title">Request Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Request Type</label>
                            <select name="request_type" id="request_type" class="form-control" required>
                                <option value="">Select Request Type</option>
                                <option value="Unserviceable" {{ old('request_type') === 'Unserviceable' ? 'selected' : '' }}>Mark as Unserviceable</option>
                                <option value="Transfer" {{ old('request_type') === 'Transfer' ? 'selected' : '' }}>Transfer to Campus / Office</option>
                            </select>
                        </div>

                        <div class="transfer-fields">
                            <div class="form-group">
                                <label>Target Office/Campus</label>
                                <select name="target_office_id" id="target_office_id" class="form-control select2bs4" style="width: 100%;">
                                    <option value="">Select Office/Campus</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ old('target_office_id') == $office->id ? 'selected' : '' }}>
                                            {{ $office->office_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Reason / Notes</label>
                            <textarea name="reason" class="form-control" rows="5" placeholder="Enter reason for return, transfer, or unserviceable request">{{ old('reason') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-paper-plane"></i> Submit Return Slip
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card return-slip-card">
                    <div class="card-header">
                        <h3 class="card-title">Select Properties</h3>
                    </div>
                    <div class="card-body">
                        <div class="return-slip-table-toolbar d-flex justify-content-between align-items-center mb-3">
                            <span><strong id="selectedPropertyCount">0</strong> selected</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelectedProperties">
                                Clear selected
                            </button>
                        </div>
                        <div class="table-responsive">
                        <table class="table table-hover mb-0" id="returnSlipPropertyTable">
                            <thead>
                                <tr>
                                    <th style="width: 44px;"></th>
                                    <th>Property No.</th>
                                    <th>Item</th>
                                    <th>Serial No.</th>
                                    <th>Office</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($properties as $property)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="return-slip-property-check" value="{{ $property->pid }}" {{ in_array($property->pid, old('property_ids', [])) ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $property->property_no_generated }}</td>
                                        <td>{{ $property->item_name }}</td>
                                        <td>{{ $property->serial_number }}</td>
                                        <td>{{ $property->office_name }}</td>
                                        <td>{{ $property->remarks ?: 'No Status' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No available properties found for your campus.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        function toggleTransferFields() {
            var isTransfer = $('#request_type').val() === 'Transfer';
            $('.transfer-fields').toggle(isTransfer);
            $('#target_office_id').prop('required', isTransfer);
        }

        $('#request_type').on('change', toggleTransferFields);
        toggleTransferFields();

        var selectedProperties = {};

        function syncSelectedProperties() {
            $('#selectedPropertyInputs').empty();

            Object.keys(selectedProperties).forEach(function (propertyId) {
                $('#selectedPropertyInputs').append(
                    $('<input>', {
                        type: 'hidden',
                        name: 'property_ids[]',
                        value: propertyId
                    })
                );
            });

            $('#selectedPropertyCount').text(Object.keys(selectedProperties).length);
        }

        $('.return-slip-property-check:checked').each(function () {
            selectedProperties[$(this).val()] = true;
        });

        var propertyTable = $('#returnSlipPropertyTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 10,
            order: [[2, 'asc']],
            columnDefs: [
                { orderable: false, searchable: false, targets: 0 }
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ properties',
                emptyTable: 'No available properties found for your campus.',
                info: 'Showing _START_ to _END_ of _TOTAL_ properties',
                infoEmpty: 'Showing 0 to 0 of 0 properties',
                infoFiltered: '(filtered from _MAX_ total properties)'
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"l<"dataTables_filter_wrapper"f>>rtip',
            initComplete: function() {
                // Ensure search input is styled properly
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_filter label').addClass('mb-0');
            }
        });

        $('#returnSlipPropertyTable').on('change', '.return-slip-property-check', function () {
            if (this.checked) {
                selectedProperties[this.value] = true;
            } else {
                delete selectedProperties[this.value];
            }

            syncSelectedProperties();
        });

        propertyTable.on('draw', function () {
            $('.return-slip-property-check').each(function () {
                this.checked = !!selectedProperties[this.value];
            });
        });

        $('#clearSelectedProperties').on('click', function () {
            selectedProperties = {};
            $('.return-slip-property-check').prop('checked', false);
            syncSelectedProperties();
        });

        syncSelectedProperties();
    });
</script>
@endsection