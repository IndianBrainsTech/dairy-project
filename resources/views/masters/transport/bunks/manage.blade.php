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
                    @slot('item2') Transport @endslot
                    @slot('item3') Petrol Bunks @endslot
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

                                            <!-- Nav tabs --> 
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="tab-profile" data-toggle="tab" href="#tpn-profile" role="tab">Profile</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab-banking" data-toggle="tab" href="#tpn-banking" role="tab">Banking</a>
                                                </li>
                                            </ul>

                                            <div class="tab-content">
                                                <div class="tab-pane active p-3" id="tpn-profile" role="tabpanel">
                                                    <div class="row mt-2">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-name">
                                                                    Petrol Bunk Name <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <input type="text" 
                                                                    name="name" 
                                                                    id="txt-name"
                                                                    value="{{ old('name', $bunk->name ?? '') }}" 
                                                                    class="form-control @error('name') is-invalid @enderror" 
                                                                    tabindex="1" 
                                                                    maxlength="100">
                                                                @error('name')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="txt-contact-number">
                                                                    Contact Number
                                                                </label>
                                                                <input type="text" 
                                                                    name="contact_number" 
                                                                    id="txt-contact-number" 
                                                                    value="{{ old('contact_number', $bunk->contact_number ?? '') }}" 
                                                                    class="form-control @error('contact_number') is-invalid @enderror" 
                                                                    tabindex="4" 
                                                                    maxlength="15">
                                                                @error('contact_number')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
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
                                                                    tabindex="2" 
                                                                    style="min-height:125px">{{ old('address', $bunk->address ?? '') }}</textarea>
                                                                @error('address')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-email">
                                                                    Email Address
                                                                </label>
                                                                <input type="text" 
                                                                    name="email" 
                                                                    id="txt-email"
                                                                    value="{{ old('email', $bunk->email ?? '') }}" 
                                                                    class="form-control @error('email') is-invalid @enderror" 
                                                                    tabindex="5" 
                                                                    maxlength="100">

                                                                @error('email')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-pin-code">
                                                                    PIN Code
                                                                </label>
                                                                <input type="text" 
                                                                    name="pin_code" 
                                                                    id="txt-pin-code"
                                                                    value="{{ old('pin_code', $bunk->pin_code ?? '') }}" 
                                                                    class="form-control @error('pin_code') is-invalid @enderror" 
                                                                    tabindex="3" 
                                                                    maxlength="6">
                                                                @error('pin_code')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-pan">
                                                                    PAN
                                                                </label>
                                                                <input type="text" 
                                                                    name="pan" 
                                                                    id="txt-pan"
                                                                    value="{{ old('pan', $bunk->pan ?? '') }}" 
                                                                    class="form-control @error('pan') is-invalid @enderror"
                                                                    maxlength="10" 
                                                                    tabindex="6" 
                                                                    oninput="this.value = this.value.toUpperCase()">
                                                                @error('pan')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>                                                            
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-gst-number">
                                                                    GST Number
                                                                </label>
                                                                <input type="text" 
                                                                    name="gst_number" 
                                                                    id="txt-gst-number"
                                                                    value="{{ old('gst_number', $bunk->gst_number ?? '') }}" 
                                                                    class="form-control @error('gst_number') is-invalid @enderror"
                                                                    maxlength="15" 
                                                                    tabindex="7" 
                                                                    oninput="this.value = this.value.toUpperCase()">
                                                                @error('gst_number')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>                                                            
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-gst-number">
                                                                    TDS Status <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <select name="tds_status" id="ddl-tds-status" class="form-control @error('tds_status') is-invalid @enderror" tabindex="8">
                                                                    <option value="">Select</option>
                                                                    <option value="NOT_APPLICABLE" @selected(old('tds_status', $bunk->tds_status?->value ?? '') == 'NOT_APPLICABLE')>TDS Not Applicable</option>
                                                                    <option value="APPLICABLE" @selected(old('tds_status', $bunk->tds_status?->value ?? '') == 'APPLICABLE')>TDS Applicable</option>
                                                                    <option value="APPLIED" @selected(old('tds_status', $bunk->tds_status?->value ?? '') == 'APPLIED')>Already in TDS</option>
                                                                </select>
                                                                @error('tds_status')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr/>
                                                    <button type="button" class="btn btn-info btn-sm float-right px-3" tabindex="9" onclick="document.getElementById('tab-banking').click()">Next ></button>
                                                </div>

                                                <div class="tab-pane p-2" id="tpn-banking" role="tabpanel">
                                                    <div class="row mt-3">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="ddl-bank">
                                                                    Bank <small class="text-danger font-13">*</small>
                                                                </label>
                                                                <span id="spn-bank" class="btn btn-info px-1 py-0 ml-1 rounded cursor-pointer">
                                                                    <i class="mdi mdi-refresh"></i>
                                                                </span>
                                                                <select name="bank_id" id="ddl-bank" class="form-control @error('bank_id') is-invalid @enderror" tabindex="10">
                                                                    <option value="">Select</option>
                                                                    @foreach($banks as $bank)
                                                                        <option value="{{ $bank->id }}"
                                                                            @selected(old('bank_id', $bunk->bank_id ?? '') == $bank->id)>
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
                                                                <select name="branch_id" id="ddl-branch" class="form-control @error('branch_id') is-invalid @enderror" tabindex="11">
                                                                    <option value="">Select</option>
                                                                </select>
                                                                @error('branch_id')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="txt-ifsc">
                                                                    IFSC
                                                                </label>
                                                                <input type="text"                                                                     
                                                                    id="txt-ifsc"
                                                                    value="{{ old('ifsc', $bunk->branch->ifsc ?? '') }}" 
                                                                    class="form-control @error('ifsc') is-invalid @enderror" 
                                                                    tabindex="-1" 
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
                                                                    tabindex="12" 
                                                                    maxlength="100">
                                                                @error('account_holder')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

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
                                                                    maxlength="20" 
                                                                    tabindex="13" 
                                                                    maxlength="30"
                                                                    oninput="this.value = this.value.toUpperCase()">
                                                                @error('account_number')
                                                                    <small class="text-danger">{{ $message }}</small>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>                                                    
                                                    <hr/>

                                                    <button type="button" class="btn btn-info btn-sm px-2 float-left" tabindex="14" onclick="document.getElementById('tab-profile').click()">< Previous</button>
                                                    <button type="submit" class="btn btn-primary btn-sm px-3 float-right" tabindex="16">Submit</button>
                                                    <button type="reset" class="btn btn-secondary btn-sm px-3 mr-3 float-right" tabindex="15">{{ isset($bunk) ? 'Reset' : 'Clear' }}</button>
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

            const $ddlBank   = $('#ddl-bank');
            const $ddlBranch = $('#ddl-branch');
            const $txtIfsc   = $('#txt-ifsc');
            doInit();

            function doInit() {
                $('a[href="#MenuMasters"]').click();

                @if(session('success'))
                    Swal.fire('Success!', "{{ session('success') }}", 'success')
                        .then(() => window.location.replace("{{ route('bunks.index') }}"));
                @endif

                restrictToNumbersAndHyphen('#txt-contact-number');
                restrictToNumbersAndAlphabets('#txt-pan');
                restrictToNumbersAndAlphabets('#txt-gst-number');
                restrictToNumbers('#txt-pin-code');
                restrictToNumbersAlphabetsHyphenSpace('#txt-account-number');                

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