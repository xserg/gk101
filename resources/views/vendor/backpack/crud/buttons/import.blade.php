@if ($crud->hasAccess('create'))
    <a href="javascript:void(0)" onclick="importTransaction(this)" data-route="{{ url($crud->route.'/import') }}" class="btn btn-sm btn-link" data-button-type="import">
<span class="ladda-label"><i class="la la-plus"></i> Import {{ $crud->entity_name }}</span>
</a>
@endif

@push('after_scripts')
<script>
    if (typeof importTransaction != 'function') {
      $("[data-button-type=import]").unbind('click');

      function importTransaction(button) {
          // ask for confirmation before deleting an item
          // e.preventDefault();
          var button = $(button);
          var route = button.attr('data-route');

          $.ajax({
              url: route,
              type: 'POST',
              success: function(result) {
                  // Show an alert with the result
                  console.log(result,route);
                  new Noty({
                      text: "Some Tx had been imported",
                      type: "success"
                  }).show();

                  // Hide the modal, if any
                  $('.modal').modal('hide');

                  crud.table.ajax.reload();
              },
              error: function(result) {
                  // Show an alert with the result
                  new Noty({
                      text: "The new entry could not be created. Please try again.",
                      type: "warning"
                  }).show();
              }
          });
      }
    }
</script>
@endpush
