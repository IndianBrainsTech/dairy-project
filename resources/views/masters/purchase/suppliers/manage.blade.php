@extends('app-layouts.admin-master')

@section('title', $page_title)

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
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
                    @slot('item3') Suppliers @endslot
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
                                    <div class="card">
                                        <div class="card-body">

                                            <!-- Nav tabs --> 
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="tab-profile" data-toggle="tab" href="#tpn-profile" role="tab" aria-controls="tpn-profile" aria-selected="true" tabindex="0">Profile</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab-finance" data-toggle="tab" href="#tpn-finance" role="tab" aria-controls="tpn-finance" aria-selected="false" tabindex="0">Finance</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab-banking" data-toggle="tab" href="#tpn-banking" role="tab" aria-controls="tpn-banking" aria-selected="false" tabindex="0">Banking</a>
                                                </li>
                                            </ul>

                                            <!-- Tab panes -->
                                            <div class="tab-content">
                                                <div class="tab-pane active p-3" id="tpn-profile" role="tabpanel" aria-labelledby="tab-profile" tabindex="0">

                                                    {{-- Supplier Name & Code --}}
                                                    <div class="row mt-1">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-name">
                                                                    Supplier Name <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <input type="text" 
                                                                    name="name" 
                                                                    id="txt-name"
                                                                    value="{{ old('name', $master->name ?? '') }}" 
                                                                    class="form-control @error('name') is-invalid @enderror" 
                                                                    tabindex="1" 
                                                                    maxlength="100">
                                                                @error('name')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-code">
                                                                    Supplier Code <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <input type="text" 
                                                                    name="code" 
                                                                    id="txt-code"
                                                                    value="{{ old('code', $master->code ?? '') }}" 
                                                                    class="form-control @error('code') is-invalid @enderror" 
                                                                    tabindex="2" 
                                                                    maxlength="20"
                                                                    oninput="this.value = this.value.toUpperCase()">                                                                    
                                                                @error('code')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Address, City & State --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-address">
                                                                    Address <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <textarea 
                                                                    name="address" 
                                                                    rows="4" 
                                                                    placeholder="Address Lines.." 
                                                                    class="form-control @error('address') is-invalid @enderror" 
                                                                    tabindex="3" 
                                                                    style="min-height:125px">{{ old('address', $master->address ?? '') }}</textarea>
                                                                @error('address')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-city">
                                                                    City <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <input type="text" 
                                                                    name="city" 
                                                                    id="txt-city"
                                                                    value="{{ old('city', $master->city ?? '') }}" 
                                                                    class="form-control @error('city') is-invalid @enderror" 
                                                                    tabindex="4" 
                                                                    maxlength="50">
                                                                @error('city')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="ddl-state">
                                                                    State <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <select name="state_id" id="ddl-state" class="form-control @error('state_id') is-invalid @enderror" tabindex="5">
                                                                    <option value="">Select</option>
                                                                    @foreach($states as $state)
                                                                        <option value="{{ $state->id }}"
                                                                            @selected(old('state_id', $master->state_id ?? '') == $state->id)>
                                                                            {{ $state->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('state_id')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Landmark & Pin Code --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-city">
                                                                    Landmark
                                                                </label>
                                                                <input type="text" 
                                                                    name="landmark" 
                                                                    id="txt-landmark"
                                                                    value="{{ old('landmark', $master->landmark ?? '') }}" 
                                                                    class="form-control @error('landmark') is-invalid @enderror" 
                                                                    tabindex="6" 
                                                                    maxlength="255">
                                                                @error('landmark')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-pin-code">
                                                                    PIN Code <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <input type="text" 
                                                                    name="pin_code" 
                                                                    id="txt-pin-code"
                                                                    value="{{ old('pin_code', $master->pin_code ?? '') }}" 
                                                                    class="form-control @error('pin_code') is-invalid @enderror" 
                                                                    tabindex="7" 
                                                                    maxlength="6">
                                                                @error('pin_code')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Contact Number & Email Address --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-contact-number">
                                                                    Contact Number
                                                                </label>
                                                                <input type="text" 
                                                                    name="contact_number" 
                                                                    id="txt-contact-number" 
                                                                    value="{{ old('contact_number', $master->contact_number ?? '') }}" 
                                                                    class="form-control @error('contact_number') is-invalid @enderror" 
                                                                    tabindex="8" 
                                                                    maxlength="15">
                                                                @error('contact_number')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-email">
                                                                    Email Address
                                                                </label>
                                                                <input type="text" 
                                                                    name="email" 
                                                                    id="txt-email"
                                                                    value="{{ old('email', $master->email ?? '') }}" 
                                                                    class="form-control @error('email') is-invalid @enderror" 
                                                                    tabindex="9" 
                                                                    maxlength="100">
                                                                @error('email')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr/>
                                                    
                                                    {{-- Next Button --}}
                                                    <button type="button" class="btn btn-info btn-sm px-3 float-right" tabindex="10" 
                                                        onclick="document.getElementById('tab-finance').click()">
                                                        Next >
                                                    </button>
                                                </div>

                                                <div class="tab-pane p-3" id="tpn-finance" role="tabpanel" aria-labelledby="tab-finance" tabindex="0">
                                                    {{-- Credit Limit  & Credit Days --}}
                                                    <div class="row mt-1">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-credit-limit">
                                                                    Credit Limit
                                                                </label>
                                                                <input type="text" 
                                                                    name="credit_limit" 
                                                                    id="txt-credit-limit"
                                                                    value="{{ old('credit_limit', $master->credit_limit ?? '') }}" 
                                                                    class="form-control @error('credit_limit') is-invalid @enderror" 
                                                                    tabindex="11" 
                                                                    maxlength="18">
                                                                @error('credit_limit')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-credit-days">
                                                                    Credit Days
                                                                </label>
                                                                <input type="text" 
                                                                    name="credit_days" 
                                                                    id="txt-credit-days"
                                                                    value="{{ old('credit_days', $master->credit_days ?? '') }}" 
                                                                    class="form-control @error('credit_days') is-invalid @enderror" 
                                                                    tabindex="12" 
                                                                    maxlength="4">                                                                    
                                                                @error('credit_days')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- GST Type  & GSTIN --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="ddl-gst-type">
                                                                    GST Type <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <select name="gst_type" id="ddl-gst-type" class="form-control @error('gst_type') is-invalid @enderror" tabindex="13">
                                                                    <option value="">Select</option>
                                                                    @foreach(\App\Enums\GstType::cases() as $option)
                                                                        <option value="{{ $option->value }}" 
                                                                            @selected(old('gst_type', $master->gst_type ?? '') == $option->value)>
                                                                            {{ $option->label() }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('gst_type')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-gstin">
                                                                    GSTIN
                                                                </label>
                                                                <input type="text" 
                                                                    name="gstin" 
                                                                    id="txt-gstin"
                                                                    value="{{ old('gstin', $master->gstin ?? '') }}" 
                                                                    class="form-control @error('gstin') is-invalid @enderror" 
                                                                    tabindex="14" 
                                                                    maxlength="15"
                                                                    oninput="this.value = this.value.toUpperCase()">                                                                    
                                                                @error('gstin')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- PAN & Payment Terms --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-pan">
                                                                    PAN
                                                                </label>
                                                                <input type="text" 
                                                                    name="pan" 
                                                                    id="txt-pan"
                                                                    value="{{ old('pan', $master->pan ?? '') }}" 
                                                                    class="form-control @error('pan') is-invalid @enderror" 
                                                                    tabindex="15"                                                                     
                                                                    maxlength="10"
                                                                    oninput="this.value = this.value.toUpperCase()">
                                                                @error('pan')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-payment-terms">
                                                                    Payment Terms
                                                                </label>
                                                                <input type="text" 
                                                                    name="payment_terms" 
                                                                    id="txt-payment-terms"
                                                                    value="{{ old('payment_terms', $master->payment_terms ?? '') }}" 
                                                                    class="form-control @error('payment_terms') is-invalid @enderror" 
                                                                    tabindex="16" 
                                                                    maxlength="255">
                                                                @error('payment_terms')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- TCS Status  & TDS Status --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="ddl-tcs-status">
                                                                    TCS Status <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <select name="tcs_status" id="ddl-tcs-status" class="form-control @error('tcs_status') is-invalid @enderror" tabindex="17">
                                                                    <option value="">Select</option>
                                                                    @foreach(\App\Enums\TcsStatus::cases() as $option)
                                                                        <option value="{{ $option->value }}" 
                                                                            @selected(old('tcs_status', $master->tcs_status ?? '') == $option->value)>
                                                                            {{ $option->label() }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('tcs_status')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="ddl-tds-status">
                                                                    TDS Status <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <select name="tds_status" id="ddl-tds-status" class="form-control @error('tds_status') is-invalid @enderror" tabindex="18">
                                                                    <option value="">Select</option>
                                                                    @foreach(\App\Enums\TdsStatus::cases() as $option)
                                                                        <option value="{{ $option->value }}" 
                                                                            @selected(old('tds_status', $master->tds_status ?? '') == $option->value)>
                                                                            {{ $option->label() }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('tds_status')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr/>

                                                    {{-- Previous & Next Buttons --}}
                                                    <button type="button" class="btn btn-info btn-sm px-2 float-left" tabindex="19" 
                                                        onclick="document.getElementById('tab-profile').click()">
                                                        < Previous
                                                    </button>

                                                    <button type="button" class="btn btn-info btn-sm px-3 float-right" tabindex="20" 
                                                        onclick="document.getElementById('tab-banking').click()">
                                                        Next >
                                                    </button>
                                                </div>

                                                <div class="tab-pane p-3" id="tpn-banking" role="tabpanel" aria-labelledby="tab-banking" tabindex="0">
                                                    {{-- Bank & Branch --}}
                                                    <div class="row mt-1">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="ddl-bank">
                                                                    Bank <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <span id="spn-bank" class="btn btn-info px-1 py-0 ml-1 rounded cursor-pointer">
                                                                    <i class="mdi mdi-refresh"></i>
                                                                </span>
                                                                <select name="bank_id" id="ddl-bank" class="form-control @error('bank_id') is-invalid @enderror" tabindex="21">
                                                                    <option value="">Select</option>
                                                                    @foreach($banks as $bank)
                                                                        <option value="{{ $bank->id }}"
                                                                            @selected(old('bank_id', $master->bank_id ?? '') == $bank->id)>
                                                                            {{ $bank->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('bank_id')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="ddl-branch">
                                                                    Branch <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <span id="spn-branch" class="btn btn-info px-1 py-0 ml-1 rounded cursor-pointer">
                                                                    <i class="mdi mdi-refresh"></i>
                                                                </span>
                                                                <select name="branch_id" id="ddl-branch" class="form-control @error('branch_id') is-invalid @enderror" tabindex="22">
                                                                    <option value="">Select</option>
                                                                </select>
                                                                @error('branch_id')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- IFSC & Account Holder Name --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-ifsc">
                                                                    IFSC
                                                                </label>
                                                                <input type="text" 
                                                                    name="ifsc" 
                                                                    id="txt-ifsc"
                                                                    value="{{ old('ifsc', $master->branch->ifsc ?? '') }}" 
                                                                    class="form-control @error('ifsc') is-invalid @enderror" 
                                                                    tabindex="-1" 
                                                                    maxlength="11" 
                                                                    readonly>
                                                                @error('ifsc')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-account-holder">
                                                                    Account Holder <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <input type="text" 
                                                                    name="account_holder" 
                                                                    id="txt-account-holder"
                                                                    value="{{ old('account_holder', $bunk->account_holder ?? '') }}" 
                                                                    class="form-control @error('account_holder') is-invalid @enderror" 
                                                                    tabindex="23" 
                                                                    maxlength="100">
                                                                @error('account_holder')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Account Number --}}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-account-number">
                                                                    Account Number <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <input type="text" 
                                                                    name="account_number" 
                                                                    id="txt-account-number"
                                                                    value="{{ old('account_number', $bunk->account_number ?? '') }}" 
                                                                    class="form-control @error('account_number') is-invalid @enderror"
                                                                    tabindex="24" 
                                                                    maxlength="30"
                                                                    oninput="this.value = this.value.toUpperCase()">
                                                                @error('account_number')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>                                                    
                                                    <hr/>

                                                    {{-- Previous, Reset & Submit Buttons --}}
                                                    <button type="button" class="btn btn-info btn-sm px-2 float-left" tabindex="25" 
                                                        onclick="document.getElementById('tab-finance').click()">
                                                        < Previous
                                                    </button>
                                                    <button type="submit" class="btn btn-primary btn-sm px-3 float-right" tabindex="27">
                                                        Submit
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary btn-sm px-3 mr-3 float-right" tabindex="26">
                                                        {{ isset($master) ? 'Reset' : 'Clear' }}
                                                    </button>
                                                </div>
                                            </div>
                                            
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
                    .then(() => window.location.replace("{{ route('suppliers.index') }}"));
            @endif

            const $ddlBank   = $('#ddl-bank');
            const $ddlBranch = $('#ddl-branch');
            const $txtIfsc   = $('#txt-ifsc');
            doInit();

            function doInit() {
                setMenuItemActive('Masters','ul-purchase','li-suppliers');
                restrictToNumbers('#txt-pin-code');
                restrictToNumbers('#txt-credit-days');
                restrictToNumbers('#txt-credit-limit');
                restrictToNumbersAndHyphen('#txt-contact-number');
                restrictToNumbersAndAlphabets('#txt-gstin');
                restrictToNumbersAndAlphabets('#txt-pan');

                $('#txt-name').on('focusout', function() {
                    $('#txt-account-holder').val($('#txt-name').val())
                });

                $('#spn-bank').on('click', loadBanks);
                $('#spn-branch').on('click', loadBranches);

                $ddlBank.on('change', function() {
                    loadBranches(false); // don't preselect on manual change
                });

                // Update IFSC when branch changes
                $('#ddl-branch').on('change', function() {
                    const ifsc = $(this).find(':selected').data('ifsc') || '';
                    $('#txt-ifsc').val(ifsc);
                });

                // On page load: handle edit form or validation error
                const oldBankId = '{{ old('bank_id', $bunk->bank_id ?? '') }}';
                if (oldBankId) {
                    $ddlBank.val(oldBankId);
                    loadBranches(true); // preselect branch
                } else {
                    $ddlBranch.prop('disabled', true); // default disabled
                }

                $('form').on('reset', resetForm);
            }

            function loadBanks() {                
                $.ajax({
                    url: "{{ route('banks.fetch') }}",
                    method: "GET",
                    dataType: "json"
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    let banks = response.data;
                    let $ddlBank = $('#ddl-bank');
                    $ddlBank.find('option:not(:first)').remove();
                    banks.forEach(bank => {
                        $ddlBank.append($('<option>', { value: bank.id, text: bank.name }));
                    });
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function loadBranches(preselect = false) {
                const bankId = $ddlBank.val();

                // Clear branch dropdown and IFSC
                $ddlBranch.find('option:not(:first)').remove();
                $txtIfsc.val('');

                if (!bankId) {
                    $ddlBranch.prop('disabled', true);
                    return;
                }

                // Show loading option
                $ddlBranch.append($('<option>', { value: '', text: 'Loading branches...' }));
                $ddlBranch.prop('disabled', true);

                $.ajax({
                    url: "{{ route('banks.branches.fetch', ['bank' => '__ID__']) }}".replace('__ID__', bankId),
                    method: "GET",
                    dataType: "json"
                })
                .done(response => {
                    console.log("AJAX Success:", response);                    
                    const branches = response.data || [];

                    // Clear loading option
                    $ddlBranch.find('option:not(:first)').remove();

                    // Populate branches
                    branches.forEach(branch => {
                        $ddlBranch.append(
                            $('<option>', {
                                value: branch.id,
                                text: branch.name,
                                'data-ifsc': branch.ifsc  // store IFSC
                            })
                        );
                    });

                    // Enable dropdown
                    $ddlBranch.prop('disabled', false);

                    // Preselect branch for edit / validation error
                    if (preselect) {
                        const oldBranchId = '{{ old('branch_id', $bunk->branch_id ?? '') }}';
                        if (oldBranchId) {
                            $ddlBranch.val(oldBranchId);
                            // Set IFSC for preselected branch
                            const selectedIfsc = $ddlBranch.find(':selected').data('ifsc');
                            $txtIfsc.val(selectedIfsc || '');
                        }
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function resetForm() {
                // Wait a tiny bit for fields to reset
                setTimeout(function() {
                    const bankId = $('#ddl-bank').val();
                    if (bankId) {
                        // $('#ddl-bank').trigger('change'); // reload branches
                        loadBranches(true);
                    } else {
                        $('#ddl-branch').prop('disabled', true).find('option:not(:first)').remove();
                        $('#txt-ifsc').val('');
                    }
                }, 10);
            }
        });
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>    
@stop