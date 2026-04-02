<!-- Top Bar Start -->
<div class="topbar">           
    <!-- Navbar -->
    <nav class="navbar-custom">            
        <ul class="list-unstyled topbar-nav float-right mb-0">            
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false" style="padding-right:6px">
                    <button type="button" onclick="window.history.back()" class="btn btn-outline-info waves-effect waves-light" style="padding-top:0px;padding-bottom:0px"><i class="fas fa-angle-left"></i></button>
                </a>
            </li>
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false" style="padding-left:6px">
                    <button type="button" onclick="window.history.forward()" class="btn btn-outline-info waves-effect waves-light" style="padding-top:0px;padding-bottom:0px"><i class="fas fa-angle-right"></i></button>
                </a>
            </li>
            
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i class="ti-bell noti-icon"></i>
                    <span class="badge badge-danger badge-pill noti-icon-badge">1</span>
                </a>
            </li>

            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <img src="{{ asset('assets/images/users/user-avatar.jpg') }}" alt="profile-user" class="rounded-circle" /> 
                    <span class="ml-1 nav-user-name hidden-sm">{{ auth()->user()->name }}<i class="mdi mdi-chevron-down"></i> </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#"><i class="dripicons-user text-muted mr-2"></i> Profile</a>                            
                    <a class="dropdown-item" href="/auth/lock-screen"><i class="dripicons-lock text-muted mr-2"></i> Lock screen</a>                            
                    <a class="dropdown-item" href="/logout"><i class="dripicons-exit text-muted mr-2"></i> Logout</a>
                </div>
            </li>
        </ul>        

        <ul class="list-unstyled topbar-nav mb-0">  
            <li>
                <a href="#">
                    <span class="responsive-logo">
                        <img src="{{ asset('assets/images/logo-sm.png') }}" alt="logo-small" class="logo-sm align-self-center" height="34">
                    </span>
                </a>                        
            </li>                      
            <li>
                <button class="button-menu-mobile nav-link">
                    <i data-feather="menu" class="align-self-center"></i>
                </button>
            </li>
        </ul>
    </nav>
    <!-- end navbar-->
</div>
<!-- Top Bar End -->
