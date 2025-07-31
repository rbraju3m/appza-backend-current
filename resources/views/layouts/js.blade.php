
<script src="{{ asset('assets/backend/js/jquery-3.6.0.min.js') }}"></script>

<script src="{{ asset('assets/backend/js/bootstrap.bundle.min.js') }}"></script>
{{--<script src="{{ asset('assets/backend/js/perfect-scrollbar.min.js') }}"></script>--}}
{{--<script src="{{ asset('assets/backend/js/next-sidebar.js') }}"></script>--}}

<!-- for datatable js -->
<script src="{{ asset('assets/backend/js/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/backend/js/bootstrap-editable.js') }}"></script>
<!-- for select2 & multiple select -->
<script src="{{ asset('assets/backend/js/select2.min.js') }}"></script>

<script src="{{ asset('assets/backend/js/jquery-ui.js') }}"></script>


<!-- for material component js -->
<script src="{{ asset('assets/backend/js/material-components-web.min.js') }}"></script>
<!-- for form validation -->

<script src="{{ asset('assets/backend/dashnav/lib/feathericons/feather.min.js') }}"></script>
<script src="{{ asset('assets/backend/dashnav/lib/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/backend/dashnav/assets/js/script.js') }}"></script>

<script src="{{ asset('assets/backend/js/sweetalert.js') }}"></script>

<!-- for plugin js default js  -->

<script type="text/javascript">
    // for select2
    $(document).ready(function() {
        $('.js-example-basic-single').select2();
    });

    // for select2 multiple
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });

    $(document).ready(function(){
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    $(function () {
        // All status active / inactive
        $(document).on("change", ".isChecked", function (e) {
            e.preventDefault();
            var table = $(this).attr('dbTable');
            var id = $(this).attr('data-id');
            var val = $(this).val();
            var url = $('.isChecked').attr('data-href');
            $.ajax({
                url: url,
                method: "GET",
                dataType: "json",
                data: {table: table,id:id,value:val},
                beforeSend: function( xhr ) {

                }
            }).done(function( response ) {
                $('.setvalue'+response.id).val(response.value);
                if (response.value == 0){
                    $('.allbutton'+response.id).show();
                }else{
                    $('.allbutton'+response.id).hide();
                }
            }).fail(function( jqXHR, textStatus ) {

            });
            return false;
        });

        // Language change
        $(document).on("change", "#languageChange", function (e) {
            e.preventDefault();
            var languageValue = $(this).val();
            var url = $('#languageRoute').attr('data-href');

            $.ajax({
                url: url,
                method: "GET",
                dataType: "json",
                data: {languageValue: languageValue},
                beforeSend: function( xhr ) {

                }
            }).done(function( response ) {
                if (response.message == 'Update'){
                    // alert('ok');
                    location.reload();
                }else{
                    alert(response.message);
                }
            }).fail(function( jqXHR, textStatus ) {

            });
            return false;
        });
    });






</script>




