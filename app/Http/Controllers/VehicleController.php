<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class VehicleController extends CrudController
{
    protected function model(): string { return Vehicle::class; }
    protected function title(): string { return 'Vehicles'; }
    protected function route(): string { return 'vehicles'; }
    protected function search(): array { return ['plate_no', 'model', 'type']; }

    protected function columns(): array
    {
        return [
            'Plate no.'    => 'plate_no',
            'Model'        => 'model',
            'Type'         => 'type',
            'Transmission' => 'transmission',
            'Status'       => fn ($r) => $this->badge($r->status),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'plate_no', 'label' => 'Plate no.', 'rules' => 'required|string|max:20', 'unique' => true],
            ['name' => 'model', 'label' => 'Make / model', 'rules' => 'required|string|max:100'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'rules' => 'required', 'options' => Options::make(['Car', 'Motorcycle', 'Van', 'Truck'])],
            ['name' => 'transmission', 'label' => 'Transmission', 'type' => 'select', 'rules' => 'required', 'options' => Options::make(['Manual', 'Automatic', 'N/A'])],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required', 'options' => Options::make(['available', 'maintenance', 'unavailable'])],
        ];
    }
}
