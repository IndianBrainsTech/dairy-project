<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <!-- Count button -->
    <button type="button" class="btn btn-pink btn-round font-weight-medium px-3 mb-2 mb-md-0">
        {{ $count }} {{ Str::plural($countLabel, $count) }}
    </button>

    <!-- Right side buttons -->
    <div class="d-flex flex-column flex-sm-row gap-2">
        
        <!-- Active dropdown -->
        <div class="btn-group dropright mr-3">
            <button id="btn-status" type="button" class="btn btn-outline-purple waves-effect waves-light" style="min-width:80px">
                {{ $status==='Active' ? 'Active' : 'All' }}
            </button>
            <button type="button" class="btn btn-info waves-effect waves-light dropdown-toggle-split dropdown-toggle" 
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropright</span>
                <i class="mdi mdi-chevron-right"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#">Active</a>
                <a class="dropdown-item" href="#">All</a>
            </div>
        </div>

        <!-- Add button -->
        <button type="button" id="btn-create" class="btn btn-primary px-3 mb-2 mb-sm-0">
            <i class="mdi mdi-plus-circle-outline mr-2"></i>Create
        </button>
    </div>
</div>