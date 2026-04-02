<!-- Start of Modal Cancel -->
<div class="modal fade" id="mdl-cancel" tabindex="-1" role="dialog" aria-labelledby="modal{{ $modalFor }}CancelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content" style="min-width:400px">
            <div class="modal-header">
                <h5 class="modal-title mt-0">{{ $modalFor }} Cancellation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row mb-0">
                                <textarea id="remarks" rows="3" class="form-control mx-2" placeholder="Reason / Remarks for Cancellation"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-secondary mr-2" data-dismiss="modal" value="Close" />
                    <input type="button" class="btn btn-primary ml-3" id="btn-cancel" value="Submit"/>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of Modal Cancel -->