<?php

namespace App\Http\Requests\Kassa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PlanningRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) session('kassa_is_admin');
    }

    protected function prepareForValidation(): void
    {
        $weekStart = $this->input('week_start');

        if ($weekStart) {
            $weekStart = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->toDateString();
        }

        $this->merge([
            'week_start' => $weekStart,
            'opmerking' => $this->emptyToNull($this->input('opmerking')),
        ]);
    }

    public function rules(): array
    {
        $weekStart = Carbon::parse($this->input('week_start'))->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        return [
            'week_start' => ['required', 'date'],
            'datum' => [
                'required',
                'date',
                'after_or_equal:' . $weekStart->toDateString(),
                'before_or_equal:' . $weekEnd->toDateString(),
            ],
            'dienst' => ['required', 'string', Rule::in(['middag', 'avond'])],
            'medewerker_id' => [
                'required',
                'integer',
                Rule::exists('medewerkers', 'id')->where('is_actief', true),
            ],
            'tafel_ids' => ['required', 'array', 'min:1'],
            'tafel_ids.*' => [
                'required',
                'integer',
                Rule::exists('restaurant_tafels', 'id')->where('is_actief', true),
            ],
            'opmerking' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'datum.required' => 'Kies een datum.',
            'datum.after_or_equal' => 'De datum moet binnen de gekozen week vallen.',
            'datum.before_or_equal' => 'De datum moet binnen de gekozen week vallen.',
            'dienst.required' => 'Kies een dienst.',
            'dienst.in' => 'Ongeldige dienst gekozen.',
            'medewerker_id.required' => 'Kies een medewerker.',
            'medewerker_id.exists' => 'Deze medewerker bestaat niet of is niet actief.',
            'tafel_ids.required' => 'Kies minimaal één tafel.',
            'tafel_ids.array' => 'Ongeldige tafelkeuze.',
            'tafel_ids.min' => 'Kies minimaal één tafel.',
            'tafel_ids.*.exists' => 'Eén van de gekozen tafels bestaat niet of is niet actief.',
            'opmerking.max' => 'Opmerking mag maximaal 255 tekens zijn.',
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
