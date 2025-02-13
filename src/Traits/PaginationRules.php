<?php

namespace LaravelPredatorApiUtils\Traits;

use Illuminate\Support\Facades\Validator;

trait PaginationRules
{
    /**
     * The `paginationRules` function in PHP defines validation rules for pagination parameters,
     * including sorting, page size, page number, and cursor for cursor pagination.
     * 
     * @param array baseRules The `baseRules` parameter in the `paginationRules` function is an array
     * that contains validation rules for additional parameters that may be specific to the resource
     * being paginated. These rules will be merged with the default pagination rules defined in the
     * function before being returned as an array.
     * 
     * @return array An array is being returned, which includes predefined pagination rules for
     * sorting, setting the number of items per page, specifying the page number, and using cursor
     * pagination. These predefined rules are merged with additional dynamic rules generated by the
     * `buildDynamicRules` method using the `array_merge` function.
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
     * The function `buildDynamicRules` dynamically determines whether to validate values as an array
     * or string based on the provided base rules in PHP.
     * 
     * @param array baseRules The `buildDynamicRules` function takes an array of base rules as input.
     * These base rules are key-value pairs where the key represents the field name and the value
     * represents the validation rule for that field.
     * 
     * @return array The `buildDynamicRules` function returns an array of dynamically created
     * validation rules based on the input array of base rules. The dynamic rules are determined based
     * on whether the value to be validated is an array or a single value (string, integer, etc.). The
     * function constructs and returns an array of validation rules that can be used in a Laravel
     * Validator instance.
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


    /**
     * The function `validatedFields` filters an array of validated data based on a set of allowed
     * parameters defined by rules.
     * 
     * @param array validatedData The `validatedData` parameter is an array containing the data that
     * needs to be validated. It typically consists of key-value pairs where the key represents the
     * field name and the value represents the data entered by the user or obtained from an external
     * source.
     * @param array rules The `rules` parameter in the `validatedFields` function is an array that
     * contains the allowed query parameters as keys. These keys are used to filter the `validatedData`
     * array to ensure that only the allowed query parameters are returned.
     * 
     * @return array The function `validatedFields` returns an array containing only the elements from
     * the `` array that have keys matching the keys in the `` array.
     */
    public function validatedFields(array $validatedData, array $rules): array
    {
        // Ensure only allowed query parameters are returned
        $allowedParams = array_keys($rules);
        return array_filter($validatedData, fn($key) => in_array($key, $allowedParams), ARRAY_FILTER_USE_KEY);
    }
}
