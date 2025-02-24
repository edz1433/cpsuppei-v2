<script>
    $(function () {
        $('#addoffice').validate({
            rules: {
                office_code: {
                    required: true
                },
                office_name: {
                    required: true
                },
                office_abbr: {
                    required: true
                }, 
                office_officer: {
                    required: true
                },
            },
            messages: {
                office_code: {
                    required: "Please Enter Office Code"
                },
                office_name: {
                    required: "Please Enter Office Name"
                },
                office_abbr: {
                    required: "Please Enter Office Abbreviation"
                },
                office_officer: {
                    required: "Please Enter Office Officer"
                },

            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.col-md-12').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
        });
    });
</script>


@if(in_array($cur_viewSidebar_route, ['officeRead', 'officeEdit']))

<script>
    $(document).on('click', '.office-delete', function(e){
        var id = $(this).val();
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        });
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('officeDelete', ":id") }}".replace(':id', id),
                        success: function (response) {  
                        $("#tr-"+id).delay(1000).fadeOut();
                        Swal.fire({
                            title:'Deleted!',
                            text:'Successfully Deleted!',
                            type:'success',
                            icon: 'warning',
                            showConfirmButton: false,
                            timer: 1000
                        })
                    }
                });
            }
        })
    });
</script>

@endif