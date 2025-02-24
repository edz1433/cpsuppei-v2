<script>
    $(function () {
        $('#addAccnt').validate({
            rules: {
                person_accnt: {
                    required: true
                },
                off_id: {
                    required: true
                },
            },
            messages: {
                person_accnt: {
                    required: "Please Enter Person Accountable"
                },
                off_id: {
                    required: "Select Campus or Office"
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


@if(in_array($cur_viewSidebar_route, ['accountableRead', 'accountableEdit']))

<script>
    $(document).on('click', '.accnt-delete', function(e){
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
                    url: "{{ route('accountableDelete', ":id") }}".replace(':id', id),
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