@extends('app-layouts.admin-master')

@section('title', 'Under Progress')

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Progress @endslot
                    @slot('item1') Maintenance @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">                                                    
                    <div class="px-3">
                        <img src="{{ URL::asset('assets/images/progress.jpg')}}" alt="" class="d-block mx-auto mt-4" height="250">
                        <div class="text-center auth-logo-text mb-4">
                            <h4 class="mt-0 mb-3 mt-5">This page is under process...Will update soon...</h4>                            
                        </div> <!--end auth-logo-text-->                                                                     
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->                               
        </div><!--end row--> 
    </div><!-- container -->   
@stop