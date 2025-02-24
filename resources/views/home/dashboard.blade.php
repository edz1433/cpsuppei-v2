@extends('layouts.master')

@section('body')
<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        @if(auth()->user()->role != 'Campus Admin')
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $userCount }}</h3>
                                <p>Users</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>{{ $offCount }}</h3>
                                <p>Offices</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-building"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $campusCount }}</h3>
                                <p>Campuses</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-map-marker"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $propertyCount }}</h3>
                                <p>Property Type</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-gear"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header text-dark" style="background-color: #c5cdd4;">
                    <h3 class="card-title">MAIN CAMPUS (PROPERTY TYPE)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <div class="position-relative mb-4">
                            <canvas id="sales-chartMain"
                                    data-main="{!! $MainPpeCount !!}"
                                    data-main-high="{!! $MainHighCount !!}" 
                                    data-main-low="{!! $MainLowCount !!}"
                                    height="200">
                            </canvas>
                        </div>
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square" style="color: #ffc107"></i> PPE
                            </span>

                            <span class="mr-2">
                                <i class="fas fa-square" style="color: #00a65a"></i> High Value
                            </span>

                            <span>
                                <i class="fas fa-square" style="color: #90ee90"></i> Low Value
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card">
                <div class="card-header text-dark" style="background-color: #c5cdd4;">
                    <h3 class="card-title">EXTENSION CAMPUSES (PROPERTY TYPE)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <div class="position-relative mb-4">
                            <canvas id="sales-chart"
                                    data-ilog="{!! $IlogPpeCount !!}"
                                    data-ilog-high="{!! $IlogHighCount !!}" 
                                    data-ilog-low="{!! $IlogLowCount !!}"
                                    data-cauayan="{!! $CauayanPpeCount !!}"
                                    data-cauayan-high="{!! $CauayanHighCount !!}" 
                                    data-cauayan-low="{!! $CauayanLowCount !!}"
                                    data-siplay="{!! $SipalayPpeCount !!}"
                                    data-siplay-high="{!! $SipalayHighCount !!}" 
                                    data-siplay-low="{!! $SipalayLowCount !!}" 
                                    data-hinobaan="{!! $HinobaanPpeCount !!}"
                                    data-hinobaan-high="{!! $HinobaanHighCount !!}" 
                                    data-hinobaan-low="{!! $HinobaanLowCount !!}" 
                                    data-hinigaran="{!! $HinigaranPpeCount !!}"
                                    data-hinigaran-high="{!! $HinigaranHighCount !!}" 
                                    data-hinigaran-low="{!! $HinigaranLowCount !!}"
                                    data-moises="{!! $MoisesPpeCount !!}"
                                    data-moises-high="{!! $MoisesHighCount !!}" 
                                    data-moises-low="{!! $MoisesLowCount !!}" 
                                    data-sancarlos="{!! $SancarlosPpeCount !!}"
                                    data-sancarlos-high="{!! $SancarlosHighCount !!}" 
                                    data-sancarlos-low="{!! $SancarlosLowCount !!}" 
                                    data-victorias="{!! $VictoriasPpeCount !!}"
                                    data-victorias-high="{!! $VictoriasHighCount !!}" 
                                    data-victorias-low="{!! $VictoriasLowCount !!}"  
                                    height="200">
                            </canvas>
                        </div>
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2">
                                <i class="fas fa-square" style="color: #ffc107"></i> PPE
                            </span>

                            <span class="mr-2">
                                <i class="fas fa-square" style="color: #00a65a"></i> High Value
                            </span>

                            <span>
                                <i class="fas fa-square" style="color: #90ee90"></i> Low Value
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <ul class="chart-legend clearfix">
                                <li><i class="fas fa-square" style="color: #ffc107"></i> PPE</li>
                                <li><i class="fas fa-square" style="color: #00a65a"></i> High Value</li>
                                <li><i class="fas fa-square" style="color: #90ee90"></i> Low Value</li>
                            </ul>
                        </div>
                        <div class="col-md-8">
                            <div class="chart-responsive pt-1">
                                <canvas id="pieChart"
                                        data-ppe="{{ $inventoryPPECount }}"
                                        data-high="{{ $inventoryHighCount }}"
                                        data-low="{{ $inventoryLowCount }}"
                                        style="min-height: 500;">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection