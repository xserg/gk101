<?php

namespace App\Exports;

use App\Models\Registry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;



class RegistryExport implements FromQuery, WithHeadings
{
    protected $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function query()
    {
        return $this->registry->select(
            [
                'divisions.name as division_name', 'lastname', 'registry.name', 'fathername', 
                'email', 'polis', 'birthdate', 'weeks', 'pregnancy_start', 'baby_born', 
                'roddom', 'pregnancy_num','born_num', 'date_off', 'phone', 'address', 
                'extra', 'check', 'expect_born', 'snils', 'created_at',
            ]
        )->join('divisions', 'division_id', '=', 'divisions.id');
    }

    public function headings(): array
    {
        return [
            'Подразделение', 'Фамилия', 'Имя', 'Отчество', 'Email', 'Полис', 
            'Дата рождения', 'Срок', 'Начало', 'Рождение', 'Роддом',  
            'Номер беременности', 'Детей', 'Дата снятия', 'Телефон', 'Адрес', 
            'Дополнительно', 'Проверка', 'Ожидаемая дата', 
            'СНИЛС', 'Дата добавления', 
        ];
    }

}
