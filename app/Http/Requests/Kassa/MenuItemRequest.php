<?php

namespace App\Http\Requests\Kassa;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) session('kassa_is_admin');
    }

    protected function prepareForValidation(): void
    {
        $price = $this->input('price');

        if (is_string($price)) {
            $price = str_replace(',', '.', $price);
        }

        $selectedCategory = $this->emptyToNull($this->input('soortgerecht'));
        $newCategory = $this->emptyToNull($this->input('nieuwe_soortgerecht'));

        $category = $newCategory ?? $selectedCategory;

        if ($category === '__new__') {
            $category = null;
        }

        $this->merge([
            'menunummer' => $this->emptyToNull($this->input('menunummer')),
            'menu_toevoeging' => $this->emptyToNull($this->input('menu_toevoeging')),
            'naam' => trim((string) $this->input('naam')),
            'price' => $this->emptyToNull($price),
            'soortgerecht' => $category,
            'beschrijving' => $this->emptyToNull($this->input('beschrijving')),
            'nieuwe_soortgerecht' => $newCategory,
        ]);
    }

    public function rules(): array
    {
        return [
            'menunummer' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'menu_toevoeging' => ['nullable', 'string', 'max:20'],
            'naam' => ['required', 'string', 'max:5000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'soortgerecht' => ['nullable', 'string', 'max:45'],
            'nieuwe_soortgerecht' => ['nullable', 'string', 'max:45'],
            'beschrijving' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'naam.required' => 'Naam is verplicht.',
            'price.numeric' => 'Prijs moet een geldig getal zijn.',
            'price.min' => 'Prijs mag niet negatief zijn.',
            'menunummer.integer' => 'Menunummer moet een getal zijn.',
            'soortgerecht.max' => 'Categorie mag maximaal 45 tekens zijn.',
            'nieuwe_soortgerecht.max' => 'Nieuwe categorie mag maximaal 45 tekens zijn.',
        ];
    }

    private function emptyToNull(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }
}
