<?php

namespace LaravelPredatorApiUtils\Traits;

use Illuminate\Support\Facades\Validator;

trait PaginationRules
{
    /**
     * Get the validation rules for pagination.
     *
     * @return array
     */
    public function paginationRules(array $baseRules): array
    {
        return array_merge(
            [
                'sort' => 'nullable|string', // e.g., '-created_at' for descending
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'cursor' => 'nullable|string', // For cursor pagination
            ],
            $this->buildDynamicRules($baseRules)
        );
    }


    /**
     * The function `buildDynamicRules` generates dynamic rules for each field with different operators
     * based on the base rules provided.
     *
     * @param array baseRules The `buildDynamicRules` function takes an array of base rules as a
     * parameter. These base rules are key-value pairs where the key represents the field name and the
     * value represents the rule associated with that field.
     *
     * @return array The `buildDynamicRules` function returns an array of dynamic rules based on the
     * provided base rules. Each field in the base rules is used to create multiple dynamic rules with
     * different operators. The function returns an array of these dynamic rules.
     */
    protected function buildDynamicRules(array $baseRules): array
    {
        $dynamicRules = [];

        foreach ($baseRules as $field => $rule) {
            // Dynamically determine whether to validate as array or string
            $dynamicRules[$field] = function ($attribute, $value, $fail) use ($rule) {
                // Check if the value is an array
                if (is_array($value)) {
                    // Apply the rule to each item in the array
                    foreach ($value as $item) {
                        $validator = Validator::make([$attribute => $item], [$attribute => $rule]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                } else {
                    // Validate as a single value (string, integer, etc.)
                    $validator = Validator::make([$attribute => $value], [$attribute => $rule]);
                    if ($validator->fails()) {
                        $fail($validator->errors()->first($attribute));
                    }
                }
            };
        }

        return $dynamicRules;
    }


    public function validatedFields(array $validatedData, array $rules): array
    {
        // Ensure only allowed query parameters are returned
        $allowedParams = array_keys($rules);
        return array_filter($validatedData, fn($key) => in_array($key, $allowedParams), ARRAY_FILTER_USE_KEY);
    }
}
