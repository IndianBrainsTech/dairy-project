<!-- Start of Customer Selection Modal -->
<div class="modal fade" id="mdl-customers" tabindex="-1" role="dialog" aria-labelledby="modalCustomerLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0" id="modal_title">Choose and Add Customers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_customers">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-sm table-bordered nowrap" style="overflow-y:auto; width:100%">
                                    <thead class="thead-light">
                                        <tr>
                                            <th></th>
                                            <th>Customer</th>
                                            <th>Group</th>
                                            <th>Route</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customers as $customer)
                                            <tr data-id="{{ $customer->id }}">
                                                <td class="text-right">
                                                    <div class="checkbox checkbox-primary checkbox-single">
                                                        <input type="checkbox"
                                                            class="chk-customer"
                                                            data-id="{{ $customer->id }}"
                                                            data-name="{{ $customer->customer_name }}"
                                                            aria-label="chk-{{ $customer->id }}">
                                                        <label class="mb-0"></label>
                                                    </div>
                                                </td>
                                                <td>{{ $customer->customer_name }}</td>
                                                <td>{{ $customer->group }}</td>
                                                <td>{{ $customer->route->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary mr-3" data-dismiss="modal">Close</button>
                    <button id="btn-add-customers" class="btn btn-primary px-4" data-dismiss="modal">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of Customer Selection Modal -->