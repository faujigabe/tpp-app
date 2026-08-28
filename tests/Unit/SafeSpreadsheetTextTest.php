<?php

namespace Tests\Unit;

use App\Rules\SafeSpreadsheetText;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SafeSpreadsheetTextTest extends TestCase
{
    /**
     * @dataProvider formulaValues
     */
    public function test_formula_spreadsheet_ditolak(string $value): void
    {
        $validator = Validator::make(['nilai' => $value], [
            'nilai' => [new SafeSpreadsheetText()],
        ]);

        $this->assertTrue($validator->fails());
    }

    public static function formulaValues(): array
    {
        return [
            ['=1+1'],
            ['+SUM(1,1)'],
            ['-1+1'],
            ['@SUM(1,1)'],
            ["\t=1+1"],
        ];
    }

    public function test_teks_biasa_diterima(): void
    {
        $validator = Validator::make(['nilai' => 'Analis Sistem Informasi'], [
            'nilai' => [new SafeSpreadsheetText()],
        ]);

        $this->assertFalse($validator->fails());
    }
}
