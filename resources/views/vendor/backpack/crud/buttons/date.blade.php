@if ($crud->hasAccess('date'))

  <div class="card col-sm-3">

              <form action="{{ url($crud->route)  }}">
              <input type=hidden name="institution_id" value="{{ app('request')->input('institution_id') }}" >
              <input type=hidden name="division_id" value="{{ app('request')->input('division_id') }}" >
              <input
              type="month"
              name="month"
              value="{{ $button->meta['month'] }}"
              class="form-control"
              onchange="this.form.submit();"
              >
            </form>
    </div>
@endif
