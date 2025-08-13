@push('scripts')
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDoHvP1E73opwpU6NSQ3Qy4wjq2wTdGbvg&libraries=places&callback=initMap">
    </script>


    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            let dTable = $('#datatable').DataTable({
                order: [],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                responsive: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                    </div>`
                },
                ajax: {
                    url: "{{ route('admin.venue.index') }}",
                    type: "GET",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'capacity'
                    },
                    {
                        data: 'location'
                    },
                    {
                        data: 'service_start_time'
                    },
                    {
                        data: 'service_end_time'
                    },
                    {
                        data: 'ticket_price'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Add Venue button
            $('#addVenueBtn').click(function() {
                $('#venueModalLabel').text('Create Venue');
                $('#venueForm')[0].reset();
                $('#venueID').val('');
                $('.error-text').text('');
                $('#venue_images').dropify().clearElement();
                $('#venue_videos').dropify().clearElement();
                $('#venueModal').modal('show');
            });


            // Submit form
            $('#venueForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                var id = $('#venueID').val();
                var url = id ?
                    "{{ route('admin.venue.update', ':id') }}".replace(':id', id) :
                    "{{ route('admin.venue.store') }}";

                if (id) {
                    formData.append('_method', 'POST'); // this might actually need to be PUT/PATCH
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('span.error-text').text('');
                        $('#venueSubmitBtn').prop('disabled', true).html('Processing...');
                    },
                    success: function(response) {
                        if (response.status === 0) {
                            $.each(response.error, function(prefix, val) {
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            $('#venueModal').modal('hide');
                            $('#venueForm')[0].reset();
                            $('#venue_images').dropify().clearElement();
                            $('#venue_videos').dropify().clearElement();
                            dTable.ajax.reload(null,
                                false); // reload without resetting pagination
                            toastr.success(response.message);
                        }
                        $('#venueSubmitBtn').prop('disabled', false).html('Save changes');
                    },
                    error: function(xhr) {
                        $('#venueSubmitBtn').prop('disabled', false).html('Save changes');
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(prefix, val) {
                                prefix = prefix.replace(/\./g, '_');
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            toastr.error('Something went wrong. Please try again.');
                        }
                    }
                });
            });
    </script>
@endpush
