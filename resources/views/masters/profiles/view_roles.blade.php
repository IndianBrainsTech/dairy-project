@extends('app-layouts.admin-master')

@section('title', 'Roles')

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Roles @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Profiles @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-11">
                <div class="card">
                    <div class="card-body">  
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Role</th>
                                    <th>Short Name</th>
                                    <th>Role Nature</th>
                                    <th>Department</th>
                                    <th>Reporting Roles</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $role->role_name }}</td>
                                            <td>{{ $role->short_name }}</td>
                                            <td>{{ $role->role_nature }}</td>
                                            <td>{{ $role->department }}</td>
                                            <td>{{ $role->reporting_roles }}</td>
                                        </tr>
                                    @endforeach                                         
                                </tbody>
                            </table>                    
                        </div>                                      
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->                               
        </div><!--end row--> 
    </div><!-- container -->
@stop
