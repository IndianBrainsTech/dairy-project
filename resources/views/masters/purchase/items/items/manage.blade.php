@extends('app-layouts.admin-master')

@section('title', $page_title)

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .rdo-container {
            padding: 2px 10px;
            border: 1px solid #e8ebf3;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') {{ $page_title }} @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Purchase @endslot
                    @slot('item3') Items @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12 col-lg-8 mx-auto">
                <div class="card">

                    @if ($errors->any())
                        <div class="alert alert-danger mb-0">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card-body">
                        <form method="post" action="{{ $form_action }}">
                            @csrf
                            @if ($form_mode === \App\Enums\FormMode::EDIT)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-0">
                                        <div class="card-body">

                                            <h4 class="header-title">Item Data</h4> <hr/>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="txt-code">
                                                            Item Code <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <input type="text" 
                                                            name="code" 
                                                            id="txt-code"
                                                            value="{{ old('code', $master->code ?? '') }}" 
                                                            class="form-control @error('code') is-invalid @enderror" 
                                                            tabindex="1" 
                                                            maxlength="15"
                                                            readonly>
                                                        @error('code')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="txt-name">
                                                            Item Name <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <input type="text" 
                                                            name="name" 
                                                            id="txt-name"
                                                            value="{{ old('name', $master->name ?? '') }}" 
                                                            class="form-control @error('name') is-invalid @enderror" 
                                                            tabindex="2" 
                                                            maxlength="100">
                                                        @error('name')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="ddl-group">
                                                            Item Group <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <select name="group_id" id="ddl-group" 
                                                            class="form-control @error('group_id') is-invalid @enderror" tabindex="3">
                                                            <option value="">Select</option>
                                                            @foreach ($groups as $group)
                                                                <option value="{{ $group->id }}"
                                                                    @selected(old('group_id', $master->group_id ?? '') == $group->id)>
                                                                    {{ $group->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('group_id')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 class="header-title mt-4">Tax Data</h4><hr/>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="act-hsn-code">
                                                            HSN Code <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <div class="input-group w-100 h-100">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="btn btn-info btn-match" aria-label="Search HSN Code" tabindex="-1">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
                                                            <input type="text" 
                                                                name="hsn_code"
                                                                id="act-hsn-code" 
                                                                value="{{ old('hsn_code', isset($master) ? $master->hsn_code ?? '' : '') }}"
                                                                class="form-control @error('hsn_code') is-invalid @enderror" 
                                                                placeholder="HSN Code" 
                                                                tabindex="4"
                                                                maxlength="10">
                                                            <input type="hidden" id="hdn-hsn-id" name="hsn_id"
                                                                value="{{ old('hsn_id', '') }}">
                                                        </div>
                                                        @error('hsn_code')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="tax_type">
                                                            Tax Type <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <div class="rdo-container">
                                                            <div class="form-check-inline my-1 pr-2">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" 
                                                                        name="tax_type" 
                                                                        id="rdo-taxable" 
                                                                        value="TAXABLE" 
                                                                        class="custom-control-input" 
                                                                        tabindex="5"
                                                                        @checked(old('tax_type', $master->tax_type ?? '') === 'TAXABLE')>
                                                                    <label class="custom-control-label" for="rdo-taxable">
                                                                        Taxable
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="form-check-inline my-1 pr-2">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" 
                                                                        name="tax_type" 
                                                                        id="rdo-exempted" 
                                                                        value="EXEMPTED" 
                                                                        class="custom-control-input" 
                                                                        tabindex="5"
                                                                        @checked(old('tax_type', $master->tax_type ?? '') === 'EXEMPTED')>
                                                                    <label class="custom-control-label" for="rdo-exempted">
                                                                        Exempted
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @error('tax_type')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row" id="div-gst">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="txt-gst">
                                                            GST <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="text" 
                                                                name="gst" 
                                                                id="txt-gst"
                                                                value="{{ old('gst', $master->gst ?? '') }}" 
                                                                class="form-control gst-field @error('gst') is-invalid @enderror" 
                                                                tabindex="6" 
                                                                maxlength="5">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">
                                                                    <i class="mdi mdi-percent"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @error('gst')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="txt-gst">
                                                            SGST <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="text" 
                                                                name="sgst" 
                                                                id="txt-sgst"
                                                                value="{{ old('sgst', $master->sgst ?? '') }}" 
                                                                class="form-control gst-field @error('sgst') is-invalid @enderror" 
                                                                tabindex="7" 
                                                                maxlength="5">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">
                                                                    <i class="mdi mdi-percent"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @error('sgst')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="txt-gst">
                                                            CGST <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="text" 
                                                                name="cgst" 
                                                                id="txt-cgst"
                                                                value="{{ old('cgst', $master->cgst ?? '') }}" 
                                                                class="form-control gst-field @error('cgst') is-invalid @enderror" 
                                                                tabindex="8" 
                                                                maxlength="5">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">
                                                                    <i class="mdi mdi-percent"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @error('cgst')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="txt-gst">
                                                            IGST <small class="text-danger font-13">*</small>
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="text" 
                                                                name="igst" 
                                                                id="txt-igst"
                                                                value="{{ old('igst', $master->igst ?? '') }}" 
                                                                class="form-control gst-field @error('igst') is-invalid @enderror" 
                                                                tabindex="9" 
                                                                maxlength="5"
                                                                readonly>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">
                                                                    <i class="mdi mdi-percent"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @error('igst')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <hr/>

                                            <button type="submit" class="btn btn-primary btn-sm px-3 float-right" tabindex="10">
                                                Submit
                                            </button>
                                            <button type="reset" class="btn btn-secondary btn-sm px-3 mr-3 float-right" tabindex="11">
                                                {{ isset($master) ? 'Reset' : 'Clear' }}
                                            </button>

                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div><!--end col-->
                            </div><!--end row-->
                        </form><!--end form-->
                    </div>
                </div><!--end card-->

            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper-v1.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            @if(session('success'))
                Swal.fire('Success!', "{{ session('success') }}", 'success')
                    .then(() => window.location.replace("{{ route('purchase.items.items.index') }}"));
            @endif

            const $divGst      = $('#div-gst');
            const $rdoTaxable  = $('#rdo-taxable');
            const $rdoExempted = $('#rdo-exempted');
            const $hdnHsnId    = $('#hdn-hsn-id');
            const $actHsnCode  = $('#act-hsn-code');
            const $txtGst      = $('#txt-gst');
            const $txtSgst     = $('#txt-sgst');
            const $txtCgst     = $('#txt-cgst');
            const $txtIgst     = $('#txt-igst');

            // Create a map for quick lookup by ID
            const hsnCodes = @json($hsn_codes);
            const hsnMap = new Map(hsnCodes.map(code => [code.id, code]));

            doInit();

            function doInit() {
                setMenuItemActive('Masters','ul-purchase','ul-purchase-items','li-purchase-items');
                restrictToNumbers('#act-hsn-code');
                restrictToFloatNumbers('.gst-field');
                $rdoTaxable.click(handleTaxTypeSelection);
                $rdoExempted.click(handleTaxTypeSelection);
                $txtGst.change(handleGstChange);                
                handleTaxTypeSelection();
            }

            function autocompleteSource(map) {
                return function (request, response) {
                    const term = request.term.toLowerCase();

                    const results = Array.from(map.values())
                        .filter(item => item.hsn_code.toLowerCase().includes(term))
                        .map(item => ({
                            label: item.hsn_code, // display in dropdown
                            value: item.hsn_code, // fill input
                            id: item.id           // keep ID
                        }));

                    response(results);
                };
            }

            $actHsnCode.autocomplete({
                source: autocompleteSource(hsnMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const record = hsnMap.get(id);
                    console.log('Selected Record:', record);

                    $hdnHsnId.val(id);

                    if (record.tax_type == 'TAXABLE') {
                        $txtGst.val(parseFloat(record.gst));
                        $txtSgst.val(parseFloat(record.sgst));
                        $txtCgst.val(parseFloat(record.cgst));
                        $txtIgst.val(parseFloat(record.igst));
                        $rdoTaxable.click();
                    }
                    else {
                        $rdoExempted.click();
                    }
                }
            });

            function handleTaxTypeSelection() {
                const taxType = $('input[name="tax_type"]:checked').val();
                if(taxType === "TAXABLE") {
                    $rdoTaxable.prop('checked', true);
                    $divGst.show();
                }
                else {
                    $rdoExempted.prop('checked', true);
                    $divGst.hide();
                    $txtGst.val('');
                    $txtSgst.val('');
                    $txtCgst.val('');
                    $txtIgst.val('');
                }
            }

            function handleGstChange() {
                const gst = parseFloat($txtGst.val());
                if(gst > 0 && gst < 100) {
                    $txtSgst.val(gst/2);
                    $txtCgst.val(gst/2);
                    $txtIgst.val(gst);
                }
                else {
                    $txtGst.val('');
                    $txtSgst.val('');
                    $txtCgst.val('');
                    $txtIgst.val('');
                }
            }

        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>    
@stop