@if(in_array($cur_viewSidebar_route, ['userRead', 'userEdit']))
<script>
    $(document).on('click', '.users-delete', function(e){
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
                    url: "{{ route('userDelete', ":id") }}".replace(':id', id),
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
<script>
    function previewImage(event) {
        var input = event.target;
        var preview = document.getElementById('image-preview');

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                preview.setAttribute('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(function () {
        $('#addUser').validate({
            rules: {
                lname: {
                    required: true,
                },
                fname: {
                    required: true,
                },
                mname: {
                    required: true,
                },
                username: {
                    required: true,
                    minlength: 5
                },
                password: {
                    required: true,
                    minlength: 8
                },
                campus_id: {
                    required: true,
                },
                role: {
                    required: true,
                },
            },
            messages: {
                lname: {
                    required: "Please enter a Last Name",
                },
                fname: {
                    required: "Please enter a First Name",
                },
                mname: {
                    required: "Please enter a Middle Name",
                },
                username: {
                    required: "Please provide a Username",
                    minlength: "Your username must be at least 5 characters long"
                },
                password: {
                    required: "Please provide a Password",
                    minlength: "Your password must be at least 8 characters long"
                },
                campus_id: {
                    required: "Please select a Campus",
                },
                role: {
                    required: "Please select a Role",
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
            }
        });
    });
</script>
<script>
     function previewImage(event) {
            var input = event.target;
            var preview = document.getElementById('image-preview');

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                preview.setAttribute('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endif