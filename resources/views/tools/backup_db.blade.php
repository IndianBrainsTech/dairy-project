@extends('app-layouts.admin-master')

@section('title', 'Db Backup')

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Database @endslot
                    @slot('item1') Tools @endslot                    
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body"> 
                        <form action="{{ route('backup_db') }}" method="get">
                            <button style="submit" class="btn btn-primary">Download</button>
                        </form>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row--> 

    </div><!-- container -->
@stop    