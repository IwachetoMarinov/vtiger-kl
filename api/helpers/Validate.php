<?php

namespace Api\Helper;

class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $rulesArr = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($rulesArr as $rule) {
                $rule = trim($rule);

                // Optional field not set → skip all rules for it
                if ($rule === 'optional' && !array_key_exists($field, $data)) {
                    continue 2;
                }

                switch (true) {
                    case $rule === 'required' && (empty($value) && $value !== '0'):
                        $errors[$field][] = 'Field is required';
                        break;

                    case $rule === 'string' && isset($value) && !is_string($value):
                        $errors[$field][] = 'Must be a string';
                        break;

                    case $rule === 'number' && isset($value) && !is_numeric($value):
                        $errors[$field][] = 'Must be a number';
                        break;

                    case $rule === 'date' && isset($value) && !self::isValidDate($value):
                        $errors[$field][] = 'Invalid date format (expected YYYY-MM-DD)';
                        break;

                    // Supports: option|[BTC,Ether,LTC] OR option:[BTC,Ether,LTC]
                    case str_starts_with($rule, 'option'):
                        if (preg_match('/\[(.*?)\]/', $rule, $matches)) {
                            $allowed = array_map('trim', explode(',', $matches[1]));
                            if (!in_array($value, $allowed, true)) {
                                $errors[$field][] = "Invalid option '$value' (allowed: " . implode(', ', $allowed) . ")";
                            }
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    private static function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
