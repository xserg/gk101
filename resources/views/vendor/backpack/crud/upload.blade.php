@extends(backpack_view('blank'))

@php
/*
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    'Upload' => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
*/
@endphp

@section('header')
  <section class="container-fluid">
    <h2>
        <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
        <small>{!! $crud->getSubheading() ?? 'Upload '.$crud->entity_name !!}.</small>

        @if ($crud->hasAccess('list'))
          <small><a href="{{ url($crud->route) }}" class="hidden-print font-sm"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif
    </h2>
  </section>
@endsection

@section('content')
@if (Alert::any())
    <div class="alert alert-danger">
        <ul>
            @foreach (Alert::all() as $alert)
                <li>{{ $alert }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row">
    <div class="col-md-8 col-md-offset-2">
          <div class="card">
            <div class="card-header">
                <h3 class="card-title">Загрузка</h3>
            </div>
            <div class="card-body row">
              <form method="post" name="upload" action="/admin/upload" enctype="multipart/form-data">
              {!! csrf_field() !!}

              {{-- load the view from the application if it exists, otherwise load the one in the package --}}
              @if(view()->exists('vendor.backpack.crud.form_content'))
                @include('vendor.backpack.crud.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
              @else
                @include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
                  @endif
                  {{-- This makes sure that all field assets are loaded. --}}
                <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
                @include('crud::inc.form_save_buttons')

                <button type="submit" class="btn btn-success text-white">
                    <span class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                    <span data-value="">Загрузить</span>
                </button>
          </form>


            </div><!-- /.card-body -->

            <div class="card-footer">

            </div><!-- /.card-footer-->
          </div><!-- /.card -->
          </form>
    </div>
</div>
@endsection
