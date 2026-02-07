<?php

namespace App\Rules;

use App\Models\Book;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueBookIdentifier implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value) || !is_array($value)) {
            return;
        }

        $identifiersToCheck = [];
        foreach ($value as $item) {
            if (!empty($item['identifier'])) {
                $identifiersToCheck[] = $item['identifier'];
            }
        }

        if (empty($identifiersToCheck)) {
            return;
        }
        
        $exists = Book::where(function ($query) use ($identifiersToCheck) {
            foreach ($identifiersToCheck as $id) {
                $query->orWhere(function ($subQ) use ($id) {
                    $subQ->whereJsonContains('isbn', [['identifier' => $id]]);
                });
            }
        })->exists();

        if ($exists) {
            $fail('This book has identifiers already registered in your library.');
        }
    }
}
