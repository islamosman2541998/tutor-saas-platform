<?php

namespace App\Livewire\AcademicStructure;

use App\Actions\AcademicStructure\DeleteLocationAction;
use App\Exceptions\CannotDeleteException;
use App\Livewire\Concerns\Notifies;
use App\Models\Location;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LocationsIndex extends Component
{
    use Notifies;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'center';

    public string $governorate = '';

    public string $city = '';

    public string $area = '';

    public string $address = '';

    public string $google_maps_url = '';

    public string $phone = '';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('settings.manage');
    }

    public function create(): void
    {
        $this->authorize('settings.manage');

        $this->reset([
            'editingId', 'name', 'governorate', 'city', 'area', 'address', 'google_maps_url', 'phone',
        ]);
        $this->type = 'center';
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $locationId): void
    {
        $this->authorize('settings.manage');

        $location = Location::query()->findOrFail($locationId);
        $this->editingId = $location->id;
        $this->name = $location->name;
        $this->type = $location->type;
        $this->governorate = (string) $location->governorate;
        $this->city = (string) $location->city;
        $this->area = (string) $location->area;
        $this->address = (string) $location->address;
        $this->google_maps_url = (string) $location->google_maps_url;
        $this->phone = (string) $location->phone;
        $this->is_active = $location->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('settings.manage');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:center,home,online,other'],
            'governorate' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [], [
            'name' => 'اسم المكان',
            'type' => 'نوع المكان',
            'google_maps_url' => 'رابط الخريطة',
        ]);

        $data['is_active'] = $this->is_active;

        if ($this->editingId) {
            Location::query()->findOrFail($this->editingId)->update($data);
            $this->toast('تم تحديث المكان.');
        } else {
            Location::query()->create($data);
            $this->toast('تم إنشاء المكان.');
        }

        $this->showForm = false;
    }

    public function askDelete(int $locationId): void
    {
        $location = Location::query()->findOrFail($locationId);

        $this->confirmThen(
            confirmEvent: 'location-delete-confirmed',
            title: 'حذف المكان؟',
            text: "سيتم حذف \"{$location->name}\" نهائيًا.",
            params: ['locationId' => $locationId],
            confirmButtonText: 'حذف',
        );
    }

    #[On('location-delete-confirmed')]
    public function delete(int $locationId, DeleteLocationAction $action): void
    {
        $this->authorize('settings.manage');

        try {
            $action->execute(Location::query()->findOrFail($locationId));
            $this->toast('تم حذف المكان.');
        } catch (CannotDeleteException $e) {
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.academic-structure.locations-index', [
            'locations' => Location::query()->orderByDesc('id')->get(),
        ]);
    }
}
