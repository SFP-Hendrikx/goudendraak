<?php

namespace App\Http\Requests\Kassa;

use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) session('kassa_is_admin');
    }

    protected function prepareForValidation(): void
    {
        $menuItems = $this->input('menu_items', []);

        foreach ($menuItems as $menuItemId => $menuItemData) {
            if (isset($menuItemData['actieprijs']) && is_string($menuItemData['actieprijs'])) {
                $menuItems[$menuItemId]['actieprijs'] = str_replace(',', '.', $menuItemData['actieprijs']);
            }
        }

        $this->merge([
            'titel' => trim((string) $this->input('titel')),
            'beschrijving' => $this->emptyToNull($this->input('beschrijving')),
            'is_actief' => $this->boolean('is_actief'),
            'menu_items' => $menuItems,
        ]);
    }

    public function rules(): array
    {
        return [
            'titel' => ['required', 'string', 'max:150'],
            'beschrijving' => ['nullable', 'string', 'max:10000'],
            'startdatum' => ['required', 'date'],
            'einddatum' => ['required', 'date', 'after_or_equal:startdatum'],
            'is_actief' => ['boolean'],

            'menu_items' => ['required', 'array'],
            'menu_items.*.selected' => ['nullable', 'boolean'],
            'menu_items.*.actieprijs' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'titel.required' => 'Titel is verplicht.',
            'titel.max' => 'Titel mag maximaal 150 tekens zijn.',
            'startdatum.required' => 'Startdatum is verplicht.',
            'startdatum.date' => 'Startdatum is geen geldige datum.',
            'einddatum.required' => 'Einddatum is verplicht.',
            'einddatum.date' => 'Einddatum is geen geldige datum.',
            'einddatum.after_or_equal' => 'Einddatum moet op of na de startdatum liggen.',
            'menu_items.required' => 'Kies minimaal één gerecht.',
            'menu_items.*.actieprijs.numeric' => 'Actieprijs moet een geldig bedrag zijn.',
            'menu_items.*.actieprijs.min' => 'Actieprijs mag niet negatief zijn.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $selectedItems = $this->selectedMenuItems();

            if (count($selectedItems) === 0) {
                $validator->errors()->add('menu_items', 'Kies minimaal één gerecht voor de aanbieding.');
                return;
            }

            $menuItemIds = array_keys($selectedItems);

            $existingMenuItemCount = MenuItem::query()
                ->whereIn('id', $menuItemIds)
                ->count();

            if ($existingMenuItemCount !== count($menuItemIds)) {
                $validator->errors()->add('menu_items', 'Eén van de gekozen gerechten bestaat niet.');
            }

            foreach ($selectedItems as $menuItemId => $menuItemData) {
                if (! isset($menuItemData['actieprijs']) || $menuItemData['actieprijs'] === '') {
                    $validator->errors()->add(
                        "menu_items.{$menuItemId}.actieprijs",
                        'Vul voor elk gekozen gerecht een actieprijs in.'
                    );
                }
            }
        });
    }

    public function offerData(): array
    {
        return [
            'titel' => $this->validated('titel'),
            'beschrijving' => $this->validated('beschrijving'),
            'startdatum' => $this->validated('startdatum'),
            'einddatum' => $this->validated('einddatum'),
            'is_actief' => $this->boolean('is_actief'),
        ];
    }

    public function offerMenuItems(): array
    {
        $syncData = [];

        foreach ($this->selectedMenuItems() as $menuItemId => $menuItemData) {
            $syncData[$menuItemId] = [
                'actieprijs' => $menuItemData['actieprijs'],
            ];
        }

        return $syncData;
    }

    private function selectedMenuItems(): array
    {
        $selectedItems = [];

        foreach ($this->input('menu_items', []) as $menuItemId => $menuItemData) {
            if (! empty($menuItemData['selected'])) {
                $selectedItems[(int) $menuItemId] = $menuItemData;
            }
        }

        return $selectedItems;
    }

    private function emptyToNull(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }
}
