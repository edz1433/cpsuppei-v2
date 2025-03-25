@extends('layouts.master')

@section('body')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        <div class="col-lg-3">
            <div class="card" >
                <div class="card-body">
                    <form action="{{ isset($issues) ? route('issueUpdate', ['id' => $issues->id]) : route('issueInsertion') }} " method="post">
                        @csrf
                        <div class="col-12">
                            <label for="exampleInput" class="form-label"><span class="badge badge-secondary">Issue</span></label>
                            <input type="text" value="{{ (isset($issues)) ? $issues->issue : '' }}" name="issue" class="form-control" id="exampleInput" placeholder="">
                        </div>
                        <div class="col-12">
                            <label for="exampleInput" class="form-label"><span class="badge badge-secondary">Description</span></label>
                            <input type="text" value="{{ (isset($issues)) ? $issues->remarks : '' }}"  name="remarks" class="form-control" id="exampleInput" placeholder="">
                        </div>
                        <div class="col-12">
                            <label for="exampleInput" class="form-label"><span class="badge badge-secondary">Status</span></label>
                            <input type="text" value="{{ (isset($issues)) ? $issues->status : '' }}"  name="status" class="form-control" id="exampleInput" placeholder="">
                        </div>
                        <div class="col-12">
                            <label for="exampleInput" class="form-label"><span class="badge badge-secondary">Urgency</span></label>
                            <input type="text" value="{{ (isset($issues)) ? $issues->urgency : '' }}"  name="urgency" class="form-control" id="exampleInput" placeholder="">
                        </div>
                        <div class="col-12 mt-4">
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i>{{ (isset($issues)) ? 'Update' : 'Save'}}
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-hover ">
                            <thead>
                            <tr>
                                <th>Property number</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Urgency</th>
                                <th>Action</th>
                            </tr>
                                @foreach($repairs as $repair)
                            <tr>
                                <td>{{$repair->property_no_generated}}</td>
                                <td>{{$repair->item_descrip}}</td>
                                <td>
                                    @if($repair->status == 1)
                                        <span class="badge badge-warning">Ongoing Repair</span>
                                    @elseif($repair->status == 2)
                                        <span class="badge badge-success">Good</span>
                                    @else
                                        <span class="status-label">Status Unknown</span>
                                    @endif
                                </td>
                                <td>{{$repair->urgency}}</td>
                                <td class="d-flex" >
                                <a style="margin:5px" class="btn btn-primary btn-sm" href="{{ route('editEssue.edit', ['id'=>$repair->repair_id] )}}">
                                    <i class="fas fa-edit"></i>
                                 </a> 
                                <button style="margin:5px" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </tr>
                            @endforeach   
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
           </div>
       </div> 
   </div> 
</div>
@endsection
          