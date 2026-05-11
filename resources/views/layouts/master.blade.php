<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CPSU || PPEI {{isset($title)?'| '.$title:''}}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('template/plugins/fontawesome-free-v6/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('template/dist/css/adminlte.css') }}">
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('template/plugins/toastr/toastr.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('template/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('template/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- Bootstrap4 Duallistbox -->
    <link rel="stylesheet" href="{{ asset('template/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('template/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Logo  -->
    <link rel="shortcut icon" type="" href="{{ asset('template/img/CPSU_L.png') }}">

    <style type="text/css">
        .navbar-light .navbar-nav .nav-item.active .nav-link.active{
            background-color: #f1c40f !important ;
            color: #000 !important;
            border-radius: 3px;
        }
        .bg-greenn{
            background-color: #1f5036;
            color: #000 !important;
        }
        .bg-selectEdit{
            background-color: #dcfdeb !important ;
            color: #000 !important;
        }
        .nav-link.active{
            color: #000 !important;
        }
        
        .content-header {
            position: fixed !important;
            width: 100% !important;
            z-index: 999 !important;
        }

        .container-fluid {
            padding-right: 2 !important;
            padding-left: 2 !important;
            margin-right: auto !important;
            margin-left: auto !important;
        }
        .btn-app{
            color: #1f5036;
            box-shadow: 5px 8px 10px rgba(0, 0, 0, 0.2) !important;
        }
        .btn-app:hover{
            background-color: #187744;
            color: #fff;
            border: #ffc107;
        }
        .btn-app.active{
            background-color: #187744;
            color: #fff;
            border: 1px blur #ffc107;
        }
        .nav-link2{
            font-size: 12pt;
            color: #000 !important;
            border-radius: 3px;
            display: block;
            padding: 0.5rem 1rem;
        }
        .nav-link2:hover{
            background-color: #187744 !important ;
            color: #fff !important;
            border-radius: 3px;
        }

        .nav-link2.active{
            background-color: #187744 !important ;
            color: #fff !important;
            border-radius: 1px blur #ffc107;
        }
        .select2-selection,
        .form-control1 {
            height: 38px !important;
        }
        @keyframes blink {
            0% { color: red; }
            5% { color: red; }
            10% { color: red; }
            20% { color: green; }
            50% { color: green; }
            80% { color: green; }
            100% { color: green; }
        }

        .blinking-text {
            animation: blink 2s infinite;
        }
        
    </style>
    <!-- Custom AdminLTE overrides -->
    <link rel="stylesheet" href="{{ asset('template/css/custom-style.css') }}">
</head>

<body class="hold-transition layout-top-nav layout-navbar-fixed text-sm">
    <div class="wrapper">
        @if(!request()->is('technician/form*') && !request()->is('technician/qr-scan*'))
        @php
            $currentUser = auth()->user();
            $systemName = $setting->system_name ?: 'CPSU PPEI';
            $systemLogo = $setting->photo_filename ? asset('uploads/'. $setting->photo_filename) : asset('template/img/default-logo.png');
            $userInitial = strtoupper(substr($currentUser->fname ?? $currentUser->username ?? 'U', 0, 1));
        @endphp
        <nav class="main-header navbar navbar-expand-md navbar-light bg-greenn app-topbar" aria-label="Application header">
            <div class="container-fluid">
                <a href="{{ route('dashboard') }}" class="navbar-brand app-brand" aria-label="Go to dashboard">
                    <span class="app-brand-logo-wrap">
                        <img src="{{ $systemLogo }}" class="brand-image img-circle app-brand-image" alt="System Photo">
                    </span>
                    <span class="app-brand-copy">
                        <span class="brand-text text-light text-bold app-brand-text">{{ $systemName }}</span>
                        <span class="app-brand-subtitle">Property, plant, equipment, and inventory management workspace.</span>
                    </span>
                </a>


                <ul class="navbar-nav navbar-no-expand ml-auto app-topbar-actions">
                    <li class="nav-item app-session-state">
                        <span class="app-session-dot"></span>
                        <span>Secure Session</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link app-user-link" data-widget="control-sidebar" data-slide="true" href="#" role="button" title="Signed in user">
                            <span class="app-user-avatar">{{ $userInitial }}</span>
                            <span class="app-user-meta">
                                <span class="signed-in-name">{{ $currentUser->fname }}</span>
                                <span class="signed-in-label">{{ $currentUser->role }}</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        @endif

        <div class="content-wrapper">
            @if(!request()->is('technician/form*') && !request()->is('technician/qr-scan*'))
            <div class="content-header app-control-header">
                <div class="container-fluid">
                    @include('partials.control')
                </div> 
            </div>
            @endif
            <!-- Main content -->
            <div class="content pt-3">
                @yield('body')
            </div>
            <!-- /.content -->
        </div>
        
        <aside class="control-sidebar control-sidebar-dark">
            
        </aside>
        
    @if(!request()->is('technician/form*') && !request()->is('technician/qr-scan*'))
    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline ">
            PPEI Management System
        </div>
        Developed and Maintain by <i>Management Information System Office</i>.
    </footer>
    @endif
</div> 
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="{{ asset('template/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('template/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('template/dist/js/adminlte.min.js') }}"></script>

<!-- Toastr -->
<script src="{{ asset('template/plugins/toastr/toastr.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('template/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@if(!request()->is('technician/form/*') && !request()->is('technician/qr-scan*'))
<!-- Select2 -->
<script src="{{ asset('template/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="{{ asset('template/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js') }}"></script>

<!-- DataTables  & Plugins -->
<script src="{{ asset('template/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script> 
<script src="{{ asset('template/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('template/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('template/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('template/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

<!-- ChartJS -->
<script src="{{ asset('template/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('js/chartjs/dashboardChart.js') }}"></script>
<script src="{{ asset('js/chartjs/Bar.js') }}"></script>
<script src="{{ asset('js/chartjs/MainBar.js') }}"></script>

<!-- jquery-validation -->
<script src="{{ asset('template/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('template/plugins/jquery-validation/additional-methods.min.js') }}"></script>

<script src="{{ asset('js/validation/purchaseValidation.js') }}"></script>
<script src="{{ asset('js/validation/inventoryValidation.js') }}"></script>
<script src="{{ asset('js/validation/passValidation.js') }}"></script>
<script src="{{ asset('js/validation/rpcppeValidation.js') }}"></script>
<script src="{{ asset('js/validation/rpcsepValidation.js') }}"></script>
<script src="{{ asset('js/validation/icsValidation.js') }}"></script>
<script src="{{ asset('js/validation/parValidation.js') }}"></script>

<!-- Moment -->
<script src="{{ asset('template/plugins/moment/moment.min.js') }}"></script>

<!-- Date Range Picker -->
<script src="{{ asset('template/plugins/daterangepicker/daterangepicker.js') }}"></script>
<link rel="stylesheet" href="{{ asset('template/plugins/daterangepicker/daterangepicker.css') }}">

@yield('scripts')

@endif
<script>
    @if(Session::has('error'))
        toastr.options = {
            "closeButton":true,
            "progressBar":true,
            'positionClass': 'toast-bottom-right'
        }
        toastr.error("{{ session('error') }}")
    @endif
    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                'positionClass': 'toast-bottom-center'
            }
            toastr.error("{{ $error }}");
        @endforeach
    @endif
</script>

<script>
    @if(Session::has('success'))
        toastr.options = {
            "closeButton":true,
            "progressBar":true,
            'positionClass': 'toast-bottom-right'
        }
        toastr.success("{{ session('success') }}")
    @endif
    @if(Session::has('success1'))
        toastr.options = {
            "closeButton":false,
            "progressBar":false,
            'positionClass': 'toast-bottom-center'
        }
        toastr.success("{{ session('success1') }}")
    @endif
</script>

@if(!request()->is('technician/form/*') && !request()->is('technician/qr-scan*'))
@if(Session::has('successcopy'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var downloadLink = document.createElement('a');
            downloadLink.href = '{{ asset("Downloaded Form/" . session('download')) }}';
            downloadLink.download = '{{ session('download') }}';
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
    </script>
@endif

<script>
    $(function () {
        if ($.fn.dataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                language: {
                    search: '',
                    searchPlaceholder: 'Search records...',
                    lengthMenu: 'Show _MENU_ rows',
                    info: 'Showing _START_ to _END_ of _TOTAL_ records',
                    infoEmpty: 'No records available',
                    zeroRecords: 'No matching records found',
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                },
                dom: "<'datatable-toolbar'<'datatable-length'l><'datatable-filter'f>>" +
                     "<'datatable-scroll'tr>" +
                     "<'datatable-footer'<'datatable-info'i><'datatable-pagination'p>>"
            });
        }

        $("#example1").DataTable({
            "responsive": false,
            "lengthChange": true, 
            "autoWidth": true,
            //"buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]

        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


        $("#example3").DataTable({
            "responsive": true,
            "lengthChange": true, 
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]

        }).buttons().container().appendTo('#example3_wrapper .col-md-6:eq(0)');
        @if(!request()->is('report'))
            $('.select2').select2()

            //Initialize Select2 Elements
            $('.select2bs4').select2({
            theme: 'bootstrap4'
            
            })
        @endif
        //Bootstrap Duallistbox
        $('.duallistbox').bootstrapDualListbox()

        $('#date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            }
        });

        // Apply event
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(
                picker.startDate.format('YYYY-MM-DD') + ' - ' +
                picker.endDate.format('YYYY-MM-DD')
            );

            console.log("Start:", picker.startDate.format('YYYY-MM-DD'));
            console.log("End:", picker.endDate.format('YYYY-MM-DD'));
        });

        // Clear event
        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#file_type').on('change', function() {
            var fileType = $(this).val();
            var form = $('#rpcppeReport');

            if (fileType === 'EXCEL') {
                form.attr('target', '');
            } else {
                form.attr('target', '_blank');
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#file_type').on('change', function() {
            var fileType = $(this).val();
            var form = $('#rpcsepReport');

            if (fileType === 'EXCEL') {
                form.attr('target', '');
            } else {
                form.attr('target', '_blank');
            }
        });
    });
</script>

<script>
    function toggleSubmenu(link) {
        var angleIcon = link.querySelector(".fas.fa-angle-down");
        angleIcon.classList.toggle("fa-angle-up"); // Change angle icon
        var submenu = link.nextElementSibling;
        if (submenu.style.display === "none" || submenu.style.display === "") {
            submenu.style.display = "block"; // Show the submenu
        } else {
            submenu.style.display = "none"; // Hide the submenu
        }
    }
    
    // Add an initialization step to open the submenu by default
    var initialSubmenu = document.querySelector("#propertySubmenu");
    if (initialSubmenu) {
        initialSubmenu.style.display = "block";
        var initialAngleIcon = initialSubmenu.previousElementSibling.querySelector(".fas.fa-angle-down");
        if (initialAngleIcon) {
            initialAngleIcon.classList.add("fa-angle-up");
        }
    }
</script>

@php
    $cur_viewSidebar_route=request()->route()->getName();
@endphp

<!-- users script -->
@include('../script.admin_user_script')
<!-- end of users script -->

<!-- manage/view script -->
@include('../script.property_script')
@include('../script.propertylv_script')
@include('../script.propertyhv_script')
@include('../script.propertyint_script')
@include('../script.unit_script')
@include('../script.item_script')
@include('../script.office_script')
@include('../script.accnt_script')
<!-- end of manage/view script -->

@include('../script.inventoryAll_script')
@include('../script.purchaseAll_script')

@endif
</body>
</html>
