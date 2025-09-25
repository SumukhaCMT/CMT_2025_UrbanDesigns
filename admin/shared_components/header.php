<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="#" class="logo logo-dark">

                    <span class="logo-lg">
                        <img src="assets/images/favicons/logo-1.webp" alt="" height="52">
                    </span>
                </a>
            </div>
            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>
        </div>
        <div class="d-flex">
            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                    <i class="bx bx-fullscreen"></i>
                </button>
            </div>

            <div class=" d-inline-block">
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                        <img class="rounded-circle header-profile-user" src="assets/images/users/user.webp" alt="admin">
                        <span class="d-none d-xl-inline-block ms-1"
                            key="admin"><?php echo htmlspecialchars($_SESSION['admin_name']) ?></span>
                        <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <a class="dropdown-item" href="change-name.php"><i
                                class="bx bx-user font-size-16 align-middle me-1"></i> <span key="t-profile">Change
                                Name</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="change-password.php"><i
                                class="bx bx-lock-open font-size-16 align-middle me-1"></i> <span
                                key="t-lock-screen">Change Password</span></a>


                    </div>
                </div>

                <button type="button" class="btn header-item waves-effect">
                    <a class="dropdown-item text-danger" href="./shared_components/logout.php"><i
                            class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span
                            key="t-logout">Logout</span></a>
                </button>
            </div>
        </div>
    </div>
</header>