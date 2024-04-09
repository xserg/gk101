{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>


<x-backpack::menu-dropdown title="Организации" icon="la la-industry">
  <x-backpack::menu-dropdown-header title="Организации" />
  @if(backpack_user()->can('menu.department') || backpack_user()->hasRole('admin'))
  <x-backpack::menu-dropdown-item title="Департаменты" icon="la la-industry" :link="backpack_url('department')" />
  @endif
  @if(backpack_user()->can('menu.institution') || backpack_user()->hasRole('admin'))
  <x-backpack::menu-dropdown-item title="Учереждения" icon="la la-clinic-medical" :link="backpack_url('institution')" />
  @endif
  @if(backpack_user()->can('menu.division') || backpack_user()->hasRole('admin'))
  <x-backpack::menu-dropdown-item title="Подразделения" icon="la la-file-medical" :link="backpack_url('division')" />
  @endif
</x-backpack::menu-dropdown>

@if(backpack_user()->can('menu.user') || backpack_user()->hasRole('admin'))
<x-backpack::menu-dropdown title="Пользователи" icon="la la-users">
    <x-backpack::menu-dropdown-header title="Authentication" />
    <x-backpack::menu-dropdown-item title="Пользователи" icon="la la-user" :link="backpack_url('user')" />
    <x-backpack::menu-dropdown-item title="Роли" icon="la la-group" :link="backpack_url('role')" />
    <x-backpack::menu-dropdown-item title="Разрешения" icon="la la-key" :link="backpack_url('permission')" />
</x-backpack::menu-dropdown>
@endif
@if(backpack_user()->hasRole('admin'))
<x-backpack::menu-dropdown title="Загрузка" icon="la la-upload">
  <x-backpack::menu-dropdown-header title="Загрузка" />
  <x-backpack::menu-dropdown-item title="Загрузка" icon="la la-upload" :link="backpack_url('upload')" />
  <x-backpack::menu-dropdown-item title="Поля" icon="la la-question" :link="backpack_url('field')" />
  <x-backpack::menu-dropdown-item title="Файлы" icon="la la-question" :link="backpack_url('file')" />
  <x-backpack::menu-dropdown-item title="Категории ПУМП" icon="la la-question" :link="backpack_url('pump-categories')" />
  <x-backpack::menu-dropdown-item title="Подкатегории ПУМП" icon="la la-question" :link="backpack_url('pump-subcat')" />
</x-backpack::menu-dropdown>
@endif
@if(backpack_user()->can('menu.staff') || backpack_user()->hasRole('admin'))
<x-backpack::menu-item title="Работники" icon="la la-user-md" :link="backpack_url('staff')" />
@endif
@if(backpack_user()->can('menu.result') || backpack_user()->hasRole('admin'))
<x-backpack::menu-item title="Результаты" icon="la la-question" :link="backpack_url('division-result')" />
<x-backpack::menu-item title="ПУМП" icon="la la-question" :link="backpack_url('pump')" />
@endif
