@if ($crud->hasAccess('upload'))
    <a href="{{ url($crud->route . '/upload') }}" class="btn btn-sm btn-link"><i class="fa fa-list"></i> Upload</a>
@endif
