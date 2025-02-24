
@if(in_array($cur_viewSidebar_route, ['lvRead', 'lvEdit']))

<script>
function addCommas(text) {
  // Replace double spaces with commas
  text = text.replace(/(\s{2})/g, ', ');
  
    return text;
  }
</script>

<script>
    $(document).on('click', '.property-delete', function(e){
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
                url: "{{ route('lvDelete', ":id") }}".replace(':id', id),
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
  $(function () {
    $.validator.addMethod('customAccountNumberFormat', function(value, element) {
        return this.optional(element) || /^\d-\d{2}-\d{2}-\d{3}$/.test(value);
    }, 'Please enter a valid format: 0-00-00-000');

    $('#propertyForm').validate({
        rules: {
            category_id: {
                required: true,
            },
            account_number: {
                required: true,
                customAccountNumberFormat: true,
            },
            account_title: {
                required: true,
            },
            description: {
                required: true,
            },
        },
        messages: {
            category_id: {
                required: "Please select a category.",
            },
            account_number: {
                required: "Please enter an account number.",
            },
            account_title: {
                required: "Please enter an account title.",
            },
            description: {
                required: "Please enter a description.",
            },
        },

        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            // Handle the form submission here
            // For example:
            form.submit(); // This will submit the form normally
        }
    });
});

</script>
<script>
  function concatenateValue(selectField) {
    var selectedValue = selectField.value;
    var inputField = selectField.closest('.form-group').nextElementSibling.querySelector('input[name="account_number"]');
    inputField.value = "{{ $property->default_code }}-{{ $property->property_code }}-" + selectedValue + "-";
  }
  
  function updateInputValue(inputField, defaultValue) {
    var selectField = document.getElementById("category");
    var selectedValue = selectField.value + "-";
    if (!inputField.value.startsWith(defaultValue)) {
      inputField.value = defaultValue + selectedValue;
    }
  }

  function extractLastThree() {
		var inputString = document.getElementById("account_number").value;
		var lastThreeDigits = inputString.split("-").pop();
		document.getElementById("code").value = lastThreeDigits;
	}

  function extractLastThree1() {
		var inputString = document.getElementById("account_number_id").value;
		var lastThreeDigits = inputString.split("-").pop();
		document.getElementById("code_edit").value = lastThreeDigits;
	}
</script>
@endif