<header id="topnav">
    <div class="topbar-main">
        <div class="container-fluid">
            <!-- Logo-->
            <div>
                <a href="{{url('/')}}" class="logo">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="" height="26">
                </a>
            </div><!-- End Logo-->
            <div class="menu-extras topbar-custom navbar p-0">
                <div class="search-wrap open" id="search-wrap">
                    <div class="search-bar">
                        {!! Form::open(['route' => 'members.dashboardSearch', 'method' => 'GET']) !!}
                        <input autofocus class="search-input" name="search" placeholder="Search" id="search">
                        {!! Form::close() !!}
                        <a href="#" class="close-search toggle-search" data-target="#search-wrap"><i
                                class="mdi mdi-close-circle"></i>
                        </a>
                    </div>
                </div>
                <ul class="list-inline ml-auto mb-0">
                    <!-- notification-->
                    <li class="list-inline-item dropdown notification-list"><a
                            class="nav-link waves-effect toggle-search" href="#" data-target="#search-wrap"><i
                                class="mdi mdi-magnify noti-icon"></i></a></li>
                    <li class="list-inline-item dropdown notification-list nav-user">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="d-none d-md-inline-block ml-1">
                                @auth
                                {{auth()->user()->name}}
                                @endauth
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated profile-dropdown">
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Logout</a>
                        </div>
                    </li>
                    <li class="menu-item list-inline-item">
                        <a class="navbar-toggle nav-link">
                            <div class="lines">
                            <span></span> <span></span> <span></span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div><!-- end menu-extras -->
            <div class="clearfix"></div>
        </div><!-- end container -->
    </div><!-- end topbar-main -->
    <!-- MENU Start -->
    <div class="navbar-custom">
        <div class="container-fluid">
            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu">
                    @php
                    $roles = auth()->user()->getRoleNames();
                    @endphp
                    @if(!($roles->count() === 1 && $roles[0] == 'member'))

                    <li class="has-submenu">
                        <a href="{{ route('dashboard') }}"><i class="dripicons-home"></i> Dashboard</a>
                    </li>

                    <li class="has-submenu"><a href="#"><i class="dripicons-duplicate"></i> Tasks <i class="mdi mdi-chevron-down mdi-drop"></i></a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    @if(auth()->user()->can('generate IPPIS deduction file') && auth()->user()->can('import and reconcile IPPIS deduction file'))
                                    <li class="has-submenu">
                                        <a href="{{ route('exportToIppis') }}"> Export Deductions </a>
                                    </li>
                                    <li class="has-submenu">
                                        <a href="{{ route('importFromIppis') }}"> Import Deductions </a>
                                    </li>
                                    @endif
                                    <li class="has-submenu">
                                        <a href="{{ route('pendingTransactions') }}"> Pending Transactions </a>
                                    </li>
                                    <li class="has-submenu">
                                        <a href="{{ route('sharesBought') }}"> Shares Bought </a>
                                    </li>
                                    <!-- <li class="has-submenu">
                                        <a href="{{ route('sharesLiquidated') }}"> Liquidate Shares </a>
                                    </li> -->
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li class="has-submenu">
                        <a href="{{ route('members.index') }}"><i class="dripicons-suitcase"></i> Members </a>
                    </li>
                    
                    @can('generate reports')
                    <li class="has-submenu"><a href="#"><i class="dripicons-duplicate"></i> Reports <i class="mdi mdi-chevron-down mdi-drop"></i></a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    <!-- <li><a href="{{ route('reports') }}">General Reports</a></li> -->
                                    <li><a href="{{ route('reports.monthlyDefaults') }}">Monthly Defaults</a></li>
                                    <!-- <li><a href="{{ route('reports.loanDefaults') }}">Loan Defaults</a></li> -->
                                    <!-- <li class="has-submenu">
                                        <a href="{{ route('reports.accounts') }}"> Accounts </a>
                                    </li> -->
                                    <li><a href="{{ route('ledgerSnapShot') }}">Ledger Snapshots</a></li>
                                    <li><a href="{{ route('members.register') }}">Members Register</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('generate reports')
                    <li class="has-submenu"><a href="#"><i class="dripicons-duplicate"></i> Accounting <i class="mdi mdi-chevron-down mdi-drop"></i></a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    <li><a href="{{ route('accountingBalanceSheet') }}"> Balance Sheet</a></li>
                                    <li><a href="{{ route('accountingProfitAndLoss') }}"> Profit & Loss</a></li>
                                    <li><a href="{{ route('accountingJournal') }}"> Journal Entries</a></li>
                                    <li><a href="{{ route('accountingCOA') }}"> Chart of Accounts</a></li>
                                    <li><a href="{{ route('trialBalance') }}"> Trial Balance</a></li>
                                    <li><a href="{{ route('accountingLinkAccounts') }}"> Link Accounts</a></li>
                                    <li><a href="{{ route('accountingQuickBalance') }}"> Quick Balance</a></li>
                                    <li><a href="{{ route('makeLedgerEntry') }}"> Make Ledger Entry</a></li>
                                    <li><a href="{{ route('expensesIndex') }}"> Expenses</a></li>
                                    <li class="has-submenu">
                                        <a href="{{ route('ippis.trxns') }}"> IPPIS Account </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @hasanyrole('president|secretary')
                    <li class="has-submenu"><a href="#"><i class="dripicons-duplicate"></i> Settings <i class="mdi mdi-chevron-down mdi-drop"></i></a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    <li>
                                        <a href="{{ route('members.updateMemberInformation') }}">Update Members Information</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('getImportInitialLedgerSummary') }}">Import Ledger Summary</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('getImportInitialLedger') }}">Import Ledger</a>
                                    </li>
                                    <li><a href="{{ route('centers.index') }}">Centers</a></li>
                                    <li><a href="{{ route('users.index') }}">Users</a></li>
                                    <li><a href="{{ route('sharesSettings') }}">Shares</a></li>
                                    <li><a href="{{ route('loanSettings') }}">Loans Settings</a></li>
                                    <li><a href="{{ route('settings.charges') }}">Bank Settings & Charges</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @hasanyrole('president|secretary')
                    <li class="has-submenu"><a href="#"><i class="dripicons-duplicate"></i> Inventory Mgt <i class="mdi mdi-chevron-down mdi-drop"></i></a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    <li>
                                        <a href="{{ route('inventory.index') }}">Items List</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @endhasanyrole

                    @hasanyrole('president|secretary')
                    <li class="has-submenu">
                        <a href="{{ route('showActivityLog') }}"><i class="dripicons-suitcase"></i> Activity Log</a>
                    </li>
                    @endhasanyrole


                    <li class="has-submenu"><a href="#"><i class="dripicons-duplicate"></i> My Account <i class="mdi mdi-chevron-down mdi-drop"></i></a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    <li>
                                        <a href="{{ route('members.ledger', auth()->user()->ippis) }}">My Ledger</a>
                                    </li>
                                    <li class="has-submenu">
                                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;"> {{ csrf_field() }}</form>
                                    </li>
                                    <li><a href="{{ route('users.changePassword') }}">Change Passowrd</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                </ul><!-- End navigation menu -->
            </div><!-- end #navigation -->
        </div><!-- end container -->
    </div><!-- end navbar-custom -->
</header>
