@extends('layouts.app')

@section('title', 'Desarrollador')

@section('submenu')

    <x-submenus.developer-menu/>

@endsection

@section('content')

    @if(Session::has('token'))

        <div class="row">

            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="alert alert-info" role="alert">
                    Tu API token generado es
                    <br>
                    <b>{{ Session::get('token') }}</b>
                    <br><br>
                    Aviso: tu API token solo se mostrará esta vez por seguridad. Guárdalo en lugar seguro. Recuerda que puedes revocar
                    el acceso en cualquier momento.
                </div>
            </div>

        </div>

    @endif

    <!-- Card -->
    <div class="card" data-list='{"valueNames": ["item-name", "item-title", "item-email", "item-phone", "item-score", "item-company"], "page": 10, "pagination": {"paginationClass": "list-pagination"}}' id="contactsList">

        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">

                    <!-- Form -->
                    <form>
                        <div class="input-group input-group-flush input-group-merge input-group-reverse">
                            <input class="form-control list-search" type="search" placeholder="Search">
                            <span class="input-group-text">
                              <i class="fe fe-search"></i>
                            </span>
                        </div>
                    </form>

                </div>
                <div class="col-auto me-n3">

                    <!-- Select -->
                    <form>
                        <select class="form-select form-select-sm form-control-flush" data-choices='{"searchEnabled": false}'>
                            <option>5 per page</option>
                            <option selected>10 per page</option>
                            <option>All</option>
                        </select>
                    </form>

                </div>
                <div class="col-auto">

                    <!-- Dropdown -->
                    <div class="dropdown">

                        <!-- Toggle -->
                        <button class="btn btn-sm btn-white" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                            <i class="fe fe-sliders me-1"></i> Filter <span class="badge bg-primary ms-1 d-none">0</span>
                        </button>

                        <!-- Menu -->
                        <form class="dropdown-menu dropdown-menu-end dropdown-menu-card">
                            <div class="card-header">

                                <!-- Title -->
                                <h4 class="card-header-title">
                                    Filters
                                </h4>

                                <!-- Link -->
                                <button class="btn btn-sm btn-link text-reset d-none" type="reset">
                                    <small>Clear filters</small>
                                </button>

                            </div>
                            <div class="card-body">

                                <!-- List group -->
                                <div class="list-group list-group-flush mt-n4 mb-4">
                                    <div class="list-group-item">
                                        <div class="row">
                                            <div class="col">

                                                <!-- Text -->
                                                <small>Title</small>

                                            </div>
                                            <div class="col-auto">

                                                <!-- Select -->
                                                <select class="form-select form-select-sm" name="item-title" data-choices='{"searchEnabled": false}'>
                                                    <option value="*" selected>Any</option>
                                                    <option value="Designer">Designer</option>
                                                    <option value="Developer">Developer</option>
                                                    <option value="Owner">Owner</option>
                                                    <option value="Founder">Founder</option>
                                                </select>

                                            </div>
                                        </div> <!-- / .row -->
                                    </div>
                                    <div class="list-group-item">
                                        <div class="row">
                                            <div class="col">

                                                <!-- Text -->
                                                <small>Lead scrore</small>

                                            </div>
                                            <div class="col-auto">

                                                <!-- Select -->
                                                <select class="form-select form-select-sm" name="item-score" data-choices='{"searchEnabled": false}'>
                                                    <option value="*" selected>Any</option>
                                                    <option value="1/10">1+</option>
                                                    <option value="2/10">2+</option>
                                                    <option value="3/10">3+</option>
                                                    <option value="4/10">4+</option>
                                                    <option value="5/10">5+</option>
                                                    <option value="6/10">6+</option>
                                                    <option value="7/10">7+</option>
                                                    <option value="8/10">8+</option>
                                                    <option value="9/10">9+</option>
                                                    <option value="10/10">10</option>
                                                </select>

                                            </div>
                                        </div> <!-- / .row -->
                                    </div>
                                </div>

                                <!-- Button -->
                                <button class="btn w-100 btn-primary" type="submit">
                                    Apply filter
                                </button>

                            </div>
                        </form>

                    </div>

                </div>
            </div> <!-- / .row -->
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-nowrap card-table">
                <thead>
                <tr>
                    <th>

                        <!-- Checkbox -->
                        <div class="form-check mb-n2">
                            <input class="form-check-input list-checkbox-all" id="listCheckboxAll" type="checkbox">
                            <label class="form-check-label" for="listCheckboxAll"></label>
                        </div>

                    </th>
                    <th>
                        <a class="list-sort text-muted" data-sort="item-name" href="#">Name</a>
                    </th>
                    <th>
                        <a class="list-sort text-muted" data-sort="item-title" href="#">Job title</a>
                    </th>
                    <th>
                        <a class="list-sort text-muted" data-sort="item-email" href="#">Email</a>
                    </th>
                    <th>
                        <a class="list-sort text-muted" data-sort="item-phone" href="#">Phone</a>
                    </th>
                    <th>
                        <a class="list-sort text-muted" data-sort="item-score" href="#">Lead score</a>
                    </th>
                    <th colspan="2">
                        <a class="list-sort text-muted" data-sort="item-company" href="#">Company</a>
                    </th>
                </tr>
                </thead>
                <tbody class="list fs-base">
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxOne" type="checkbox">
                            <label class="form-check-label" for="listCheckboxOne"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Dianna Smiley</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">diana.smiley@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-4890">(988) 568-3568</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-danger-soft">1/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Twitter</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxTwo" type="checkbox">
                            <label class="form-check-label" for="listCheckboxTwo"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-2.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Ab Hadley</a>

                    </td>
                    <td class="">

                        <!-- Text -->
                        <span class="item-title">Developer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">ab.hadley@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(650) 430-9876</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">8/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Google</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxThree" type="checkbox">
                            <label class="form-check-label" for="listCheckboxThree"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-3.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Adolfo Hess</a>

                    </td>
                    <td class="">

                        <!-- Text -->
                        <span class="item-title">Owner</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">adolfo.hess@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(968) 682-1364</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">7/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Google</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxFour" type="checkbox">
                            <label class="form-check-label" for="listCheckboxFour"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-4.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Daniela Dewitt</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">daniela.dewitt@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-489">(650) 430-9876</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">4/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Twitch</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxFive" type="checkbox">
                            <label class="form-check-label" for="listCheckboxFive"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-5.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Miyah Myles</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Founder</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">miyah.myles@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(935) 165-8435</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-danger-soft">3/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Facebook</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxSix" type="checkbox">
                            <label class="form-check-label" for="listCheckboxSix"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-6.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Ryu Duke</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">ryu.duke@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(937) 596-0152</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">6/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Netflix</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxSeven" type="checkbox">
                            <label class="form-check-label" for="listCheckboxSeven"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-7.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Glen Rouse</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">glen.rouse@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(689) 798-4635</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">9/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Netflix</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxEight" type="checkbox">
                            <label class="form-check-label" for="listCheckboxEight"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Daniela Dewitt</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Developer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">daniela.dewitt@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(937) 568-8946</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">7/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Uber</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxNine" type="checkbox">
                            <label class="form-check-label" for="listCheckboxNine"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-2.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Adolfo Hess</a>

                    </td>
                    <td class="">

                        <!-- Text -->
                        <span class="item-title">Founder</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">adolfo.hess@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(568) 498-0365</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">10/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Amazon</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxTen" type="checkbox">
                            <label class="form-check-label" for="listCheckboxTen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-3.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Glen Rouse</a>

                    </td>
                    <td class="">

                        <!-- Text -->
                        <span class="item-title">Owner</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">glen.rouse@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-67890">(968) 135-6458</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">6/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Twitch</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxEleven" type="checkbox">
                            <label class="form-check-label" for="listCheckboxEleven"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-4.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Miyah Myles</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">miyah.myles@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(650) 430-9876</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">8/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Twitter</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxTwelve" type="checkbox">
                            <label class="form-check-label" for="listCheckboxTwelve"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-5.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Dianna Smiley</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Developer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">dianna.smiley@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(968) 165-8790</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">5/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Google</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxThirteen" type="checkbox">
                            <label class="form-check-label" for="listCheckboxThirteen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-6.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Glen Rouse</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Owner</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">glen.rouse@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(937) 596-0152</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-danger-soft">2/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Uber</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxFourteen" type="checkbox">
                            <label class="form-check-label" for="listCheckboxFourteen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-7.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Adolfo Hess</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">adolfo.hess@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(689) 798-4635</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">4/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Netflix</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="Fifteen" type="checkbox">
                            <label class="form-check-label" for="Fifteen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-8.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Daniela Dewitt</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Owner</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">daniela.dewitt@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(365) 489-1365</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">9/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Uber</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" id="listCheckboxSixteen" type="checkbox">
                            <label class="form-check-label" for="listCheckboxSixteen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Miyah Myles</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Owner</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">miyah.myles@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-4890">(968) 165-8920</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">5/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Twitch</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxSeventeen">
                            <label class="form-check-label" for="listCheckboxSeventeen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-2.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Glen Rouse</a>

                    </td>
                    <td class="">

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">glen.rouse@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(689) 263-4856</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-danger-soft">3/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Facebook</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxEighteen">
                            <label class="form-check-label" for="listCheckboxEighteen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-3.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Ab Hadley</a>

                    </td>
                    <td class="">

                        <!-- Text -->
                        <span class="item-title">Founder</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">ab.hadley@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(346) 795-1685</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">9/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Lyft</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxNineteen">
                            <label class="form-check-label" for="listCheckboxNineteen"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-4.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Daniela Dewitt</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Developer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">daniela.dewitt@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-489">(892) 678-0028</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-success-soft">10/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Microsoft</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxTwenty">
                            <label class="form-check-label" for="listCheckboxTwenty"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-5.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Daniela Dewitt</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Developer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">daniela.dewitt@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(036) 000-8935</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-danger-soft">1/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Lyft</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxTwentyOne">
                            <label class="form-check-label" for="listCheckboxTwentyOne"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-6.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Adolfo Hess</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Founder</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">adolfo.hess@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(968) 264-8964</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-danger-soft">2/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Google</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxTwentyTwo">
                            <label class="form-check-label" for="listCheckboxTwentyTwo"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-7.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Adolfo Hess</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Owner</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">adolfo.hess@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(158) 167-0680</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">5/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Uber</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxTwentyThree">
                            <label class="form-check-label" for="listCheckboxTwentyThree"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Miyah Myles</a>

                    </td>
                    <td>

                        <!-- Text -->
                        <span class="item-title">Owner</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">miyah.myles@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(038) 876-3917</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-warning-soft">6/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Twitter</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" type="checkbox" id="listCheckboxTwentyFour">
                            <label class="form-check-label" for="listCheckboxTwentyFour"></label>
                        </div>

                    </td>
                    <td>

                        <!-- Avatar -->
                        <div class="avatar avatar-xs align-middle me-2">
                            <img class="avatar-img rounded-circle" src="assets/img/avatars/profiles/avatar-2.jpg" alt="...">
                        </div> <a class="item-name text-reset" href="profile-posts.html">Ryu.Duke</a>

                    </td>
                    <td class="">

                        <!-- Text -->
                        <span class="item-title">Designer</span>

                    </td>
                    <td>

                        <!-- Email -->
                        <a class="item-email text-reset" href="mailto:john.doe@company.com">ryu.duke@company.com</a>

                    </td>
                    <td>

                        <!-- Phone -->
                        <a class="item-phone text-reset" href="tel:1-123-456-7890">(862) 0057-9806</a>

                    </td>
                    <td>

                        <!-- Badge -->
                        <span class="item-score badge bg-danger-soft">1/10</span>

                    </td>
                    <td>

                        <!-- Link -->
                        <a class="item-company text-reset" href="team-overview.html">Amazon</a>

                    </td>
                    <td class="text-end">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between">

            <!-- Pagination (prev) -->
            <ul class="list-pagination-prev pagination pagination-tabs card-pagination">
                <li class="page-item">
                    <a class="page-link ps-0 pe-4 border-end" href="#">
                        <i class="fe fe-arrow-left me-1"></i> Prev
                    </a>
                </li>
            </ul>

            <!-- Pagination -->
            <ul class="list-pagination pagination pagination-tabs card-pagination"></ul>

            <!-- Pagination (next) -->
            <ul class="list-pagination-next pagination pagination-tabs card-pagination">
                <li class="page-item">
                    <a class="page-link ps-4 pe-0 border-start" href="#">
                        Next <i class="fe fe-arrow-right ms-1"></i>
                    </a>
                </li>
            </ul>

            <!-- Alert -->
            <div class="list-alert alert alert-dark alert-dismissible border fade" role="alert">

                <!-- Content -->
                <div class="row align-items-center">
                    <div class="col">

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input" id="listAlertCheckbox" type="checkbox" checked disabled>
                            <label class="form-check-label text-white" for="listAlertCheckbox">
                                <span class="list-alert-count">0</span> deal(s)
                            </label>
                        </div>

                    </div>
                    <div class="col-auto me-n3">

                        <!-- Button -->
                        <button class="btn btn-sm btn-white-20">
                            Edit
                        </button>

                        <!-- Button -->
                        <button class="btn btn-sm btn-white-20">
                            Delete
                        </button>

                    </div>
                </div> <!-- / .row -->

                <!-- Close -->
                <button type="button" class="list-alert-close btn-close" aria-label="Close"></button>

            </div>

        </div>
    </div>





@endsection
