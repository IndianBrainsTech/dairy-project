@extends('app-layouts.admin-master')
@section('title', 'Create Payment Abstract')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Create Payment Abstract @endslot
            @slot('item1') Transport @endslot
            @slot('item2') Create Payment Abstract @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-8 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.secondary-payment-abstracts.store') }}">@csrf 
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Abstract Number <small class="text-danger">*</small></label>
                        <input type="text" name="abstract_number" value="{{ old('abstract_number', $abstractNumber) }}" class="form-control @error('abstract_number') is-invalid @enderror">
                        @error('abstract_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Abstract Date <small class="text-danger">*</small></label>
                        <input type="date" name="abstract_date" value="{{ old('abstract_date', now()->format('Y-m-d')) }}" class="form-control @error('abstract_date') is-invalid @enderror">
                        @error('abstract_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Supplier Transporter <small class="text-danger">*</small></label>
                        <select name="supplier_transporter_id" class="form-control @error('supplier_transporter_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($transporters as $id => $name)<option value="{{ $id }}" @selected(old('supplier_transporter_id')==$id)>{{ $name }}</option>@endforeach
                        </select>@error('supplier_transporter_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Period From <small class="text-danger">*</small></label>
                        <input type="date" name="period_from" value="{{ old('period_from') }}" class="form-control @error('period_from') is-invalid @enderror">
                        @error('period_from')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Period To <small class="text-danger">*</small></label>
                        <input type="date" name="period_to" value="{{ old('period_to') }}" class="form-control @error('period_to') is-invalid @enderror">
                        @error('period_to')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <div class="alert alert-info"><i class="mdi mdi-information-outline mr-1"></i>Totals will be auto-calculated from approved secondary transport bills for this transporter and period.</div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.secondary-payment-abstracts.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop