<?php

namespace App\Support;

class SpreadsheetImportFailureFormatter
{
    public static function messages(iterable $failures, int $limit = 10): array
    {
        $messages = [];

        foreach ($failures as $failure) {
            foreach ($failure->errors() as $error) {
                $messages[] = 'Baris ' . $failure->row() . ': ' . $error;

                if (count($messages) >= $limit) {
                    return $messages;
                }
            }
        }

        return $messages;
    }
}
