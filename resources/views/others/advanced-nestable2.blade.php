@extends('layouts.master')

@section('title', 'Metrica - Admin & Dashboard Template')

@section('headerStyle')
<!-- Nestable css -->
<link href="{{ URL::asset('plugins/nestable/jquery.nestable.min.css')}}" rel="stylesheet" />
@stop

@section('content')
 <div class="container-fluid">
                    <!-- Page-Title -->
                    <div class="row">
                        <div class="col-sm-12">
                              @component('common-components.breadcrumb')
                                 @slot('title') Nestable @endslot
                                 @slot('item1') Metrica @endslot
                                 @slot('item2') UI Kit @endslot
                                 @endcomponent

                        </div><!--end col-->
                    </div>
                    <!-- end page title end breadcrumb -->
 
                     
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="custom-dd dd" id="nestable_list_1">
                                        <ol class="dd-list">
                                            <li class="dd-item dd3-item" data-id="37">
                                                <div class="dd-handle dd3-handle"></div>
                                                <div class="dd3-content dd3-content-p">Item 1</div>
                                            </li>
                                            <li class="dd-item dd3-item" data-id="38">
                                                <div class="dd-handle dd3-handle"></div>
                                                <div class="dd3-content dd3-content-p">Item 2</div>
                                            </li>
                                            <li class="dd-item dd3-item" data-id="39">
                                                <div class="dd-handle dd3-handle"></div>
                                                <div class="dd3-content dd3-content-p">Item 3</div>
                                                <ol class="dd-list">
                                                    <li class="dd-item dd3-item" data-id="40">
                                                      <div class="dd-handle dd3-handle"></div>
                                                      <div class="dd3-content dd3-content-p">Item 4</div>
                                                    </li>
                                                    <li class="dd-item dd3-item" data-id="41">
                                                        <div class="dd-handle dd3-handle"></div>
                                                        <div class="dd3-content dd3-content-p">Item 5</div>
                                                    </li>
                                                    <li class="dd-item dd3-item" data-id="42">
                                                        <div class="dd-handle dd3-handle"></div>
                                                        <div class="dd3-content dd3-content-p">Item 6</div>
                                                    </li>
                                                </ol>
                                            </li>
                                        </ol>
                                    </div><!--nastable-list-3-->    
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div> <!-- end col -->
                    </div> <!-- end row -->

                </div><!-- container -->
@stop

@section('footerScript')
<!--Nestable-->
        <script src="{{ URL::asset('plugins/nestable/jquery.nestable.min.js')}}"></script>
        <script src="{{ URL::asset('assets/pages/jquery.nastable.init.js')}}"></script>
        
@stop