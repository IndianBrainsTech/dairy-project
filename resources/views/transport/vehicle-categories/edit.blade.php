@extends('app-layouts.admin-master')

@section('title', 'Edit Vehicle Category')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @component('app-components.breadcrumb-4')
                @slot('title') Edit Vehicle Category @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Transport @endslot
                @slot('item3') Vehicle Categories @endslot
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 mx-auto">
            <div class="card">
                @if($errors->any())
                    <div class="alert alert-danger mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card-body">
                    <form method="POST" action="{{ route('transport.vehicle-categories.update', $vehicleCategory) }}">
                        @csrf @method('PUT')

                        <div class="form-group">
                            <label>Category Name <small class="text-danger">*</small></label>
                            <input type="text" name="name"
                                   value="{{ old('name', $vehicleCategory->name) }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   maxlength="100" autofocus>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description"
                                   value="{{ old('description', $vehicleCategory->description) }}"
                                   class="form-control @error('description') is-invalid @enderror"
                                   maxlength="255">
                            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-group">
                            <label>Status <small class="text-danger">*</small></label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="active"   @selected(old('status', $vehicleCategory->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $vehicleCategory->status) === 'inactive')>Inactive</option>
                            </select>
                            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('transport.vehicle-categories.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
