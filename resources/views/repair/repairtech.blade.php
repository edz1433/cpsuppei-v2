@extends('layouts.master')

@section('body')
@php
$cr = request()->route()->getName();
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                    </h3>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Property number</th>
                                <th>Findings</th>
                                <th>Urgency</th>
                                <th>Date Received</th>
                                <th>Date Diagnosed</th>
                                <th>Date Released</th>
                                <th>Total Days</th>
                                <th>Diagnosis</th>
                                <th>Repair Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($repairs as $repair)
                            <tr id="tr-{{ $repair->rpid }}">
                                <td>{{ $repair->property_no_generated }}</td>
                                <td>{{ $repair->findings }}</td>

                                <!-- Urgency Badge -->
                                <td>
                                    @switch($repair->urgency)
                                        @case('Low')
                                            <span class="badge bg-success">Low Priority</span>
                                            @break
                                        @case('Medium')
                                            <span class="badge bg-info text-dark">Medium Priority</span>
                                            @break
                                        @case('High')
                                            <span class="badge bg-warning text-dark">High Priority</span>
                                            @break
                                        @case('Urgent')
                                            <span class="badge bg-danger">Urgent / Critical</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $repair->urgency }}</span>
                                    @endswitch
                                </td>

                                <td>
                                    @php 
                                        $formattedDate = strtoupper($repair->created_at->format('M')) . '. ' . $repair->created_at->format('d, Y g:i A');
                                    @endphp
                                    {{ $formattedDate }}<br>
                                    <b>by:</b>
                                </td>
                                <td>
                                    @if($repair->date_diagnose)
                                        @php 
                                            $formattedDiagnoseDate = strtoupper($repair->date_diagnose->format('M')) . '. ' . $repair->date_diagnose->format('d, Y g:i A');
                                        @endphp
                                        {{ $formattedDiagnoseDate }}
                                    @else
                                        N/A
                                    @endif <br>
                                    <b>by:</b>
                                </td>
                                <td>
                                    @if($repair->release_date)
                                        @php 
                                            $formattedReleaseDate = strtoupper($repair->release_date->format('M')) . '. ' . $repair->release_date->format('d, Y g:i A');
                                        @endphp
                                        {{ $formattedReleaseDate }}
                                    @else
                                        N/A
                                    @endif<br>
                                    <b>by:</b>
                                </td>
                                <td>
                                    @if($repair->date_diagnose)
                                        @php $totalDays = $repair->created_at->diffInDays($repair->date_diagnose); @endphp
                                        {{ $totalDays }} {{ $totalDays == 1 ? 'day' : 'days' }}
                                    @else
                                        @php $totalDays = $repair->created_at->diffInDays(now()); @endphp
                                        {{ $totalDays }} {{ $totalDays == 1 ? 'day' : 'days' }}
                                    @endif
                                </td>

                                <td>{{ $repair->diagnosis }}</td>

                                <!-- Repair Status Badge -->
                                <td>
                                    @if($repair->repair_status == 1)
                                        <span class="badge bg-warning">Pending</span>

                                    @elseif($repair->repair_status == 2)
                                        <span class="badge bg-success">Completed</span>

                                    @elseif($repair->repair_status == 3)
                                        <span class="badge bg-info text-dark">Referred to Supply Office</span>

                                    @elseif($repair->repair_status == 4)
                                        <span class="badge bg-primary">Referred for Outside Repair</span>

                                    @elseif($repair->repair_status == 5)
                                        <span class="badge bg-danger">Unserviceable</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('repairPDF', ['id' => $repair->rpid]) }}" target="_blank" class="btn btn-danger btn-xs" data-id="{{ $repair->rpid }}">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
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