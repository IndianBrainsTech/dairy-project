<h4 class="header-title mt-0 mb-3">Document Header</h4>
<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="document_number">Document</label>
                    <input type="text" class="form-control" name="document_number" value="{{ $documentNumber }}" disabled>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="document_date">Document Date</label>
                    <input type="date" class="form-control" name="document_date" value="{{ $documentDate }}" disabled>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="dt-effect">Effect Date <small class="text-danger font-13">*</small></label>
                    <input type="date" class="form-control" name="effect_date" value="{{ $effectDate }}" 
                        id="dt-effect" min="{{ $documentDate }}" tabindex="1">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group row">
                    <label for="txt-narration" class="col-lg-3 col-form-label text-right">Narration <small class="text-danger font-13">*</small></label>
                    <div class="col-9">
                        <input type="text" class="form-control" name="narration" value="{{ $narration }}" id="txt-narration" tabindex="2">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br/>

<div class="row">
    <div class="col-12">
        <h4 class="header-title mt-0">
            Applicable Customers
            <button type="button" id="btn-choose-customers" class="btn btn-primary ml-3 pr-3" 
                data-toggle="modal" data-animation="bounce" data-target="#mdl-customers" 
                tabindex="3" style="height:32px; padding-top:1px; padding-bottom:1px">
                    <i class="mdi mdi-plus-circle-outline mr-2"></i>Add
            </button>
        </h4>
        <div class="table-responsive table-container mt-3" style="max-height:250px">
            <table id="tbl-customers" class="table table-bordered table-sm">
                <thead class="thead-light" style="height:32px">
                    <tr>
                        <th class="text-center">S.No</th>
                        <th class="d-none">ID</th>
                        <th>Customer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>