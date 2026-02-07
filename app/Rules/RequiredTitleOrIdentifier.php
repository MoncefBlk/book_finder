<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiredTitleOrIdentifier implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            return;
        }

        $title = $value['title'] ?? null;
        $identifiers = $value['industryIdentifiers'] ?? null;

        $hasTitle = !empty($title);
        $hasIdentifier = false;

        if (!empty($identifiers) && is_array($identifiers)) {
            foreach ($identifiers as $id) {
                if (!empty($id['identifier'])) {
                    $hasIdentifier = true;
                    break;
                }
            }
        }

        if (!$hasTitle && !$hasIdentifier) {
            $fail('The book must have at least a title or an identifier.');
        }
    }
}
