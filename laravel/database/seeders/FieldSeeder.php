<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Field;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boards = Board::all();

        if ($boards->isEmpty()) {
            $this->command->warn('No boards found. Please run BoardSeeder first.');
            return;
        }

        foreach ($boards as $board) {
            $this->createFieldsForBoard($board);
        }
    }

    /**
     * Create fields for a specific board.
     */
    private function createFieldsForBoard(Board $board): void
    {
        $fieldTypes = [
            // Premium Felder (teuer)
            [
                'names' => ['Tor Links', 'Tor Rechts'],
                'price' => 500.00,
                'width' => 2,
                'height' => 2,
            ],
            [
                'names' => ['Elfmeterpunkt', 'Mittelkreis'],
                'price' => 400.00,
                'width' => 2,
                'height' => 2,
            ],
            [
                'names' => ['Strafraum Links', 'Strafraum Rechts'],
                'price' => 350.00,
                'width' => 3,
                'height' => 2,
            ],
            // Mittelfeld Felder (mittel)
            [
                'names' => ['Mittellinie Mitte', 'Mittelfeld Links', 'Mittelfeld Rechts'],
                'price' => 200.00,
                'width' => 2,
                'height' => 1,
            ],
            // Standard Felder (günstig)
            [
                'names' => ['Außenlinie Links', 'Außenlinie Rechts', 'Eckfahne'],
                'price' => 100.00,
                'width' => 1,
                'height' => 1,
            ],
        ];

        $row = 0;
        $column = 0;
        $fieldCount = 0;
        $maxFields = min($board->rows * $board->columns, 30); // Max 30 Felder pro Board

        // Erstelle verschiedene Feldtypen
        foreach ($fieldTypes as $typeIndex => $fieldType) {
            foreach ($fieldType['names'] as $nameIndex => $name) {
                if ($fieldCount >= $maxFields) {
                    break 2;
                }

                // Berechne Position
                $column = $fieldCount % $board->columns;
                $row = (int)($fieldCount / $board->columns);

                // Prüfe ob Feld ins Board passt
                if ($row + $fieldType['height'] > $board->rows ||
                    $column + $fieldType['width'] > $board->columns) {
                    $fieldCount++;
                    continue;
                }

                // Wähle Status basierend auf Feldtyp
                $status = match ($typeIndex) {
                    0, 1 => Field::STATUS_RENTED, // Premium Felder meist vermietet
                    2 => rand(0, 1) ? Field::STATUS_RENTED : Field::STATUS_AVAILABLE,
                    3 => rand(0, 2) ? Field::STATUS_AVAILABLE : Field::STATUS_RENTED,
                    default => Field::STATUS_AVAILABLE,
                };

                Field::create([
                    Field::board_id => $board->id,
                    Field::name => $name . ' (' . $board->name . ')',
                    Field::row => $row,
                    Field::column => $column,
                    Field::width => $fieldType['width'],
                    Field::height => $fieldType['height'],
                    Field::price_per_month => $fieldType['price'],
                    Field::status => $status,
                    Field::description => $this->getFieldDescription($name, $fieldType['price']),
                ]);

                $fieldCount += $fieldType['width'] * $fieldType['height'];
            }
        }

        // Fülle restliche Positionen mit Standard-Feldern
        $standardPrice = 120.00;
        for ($r = 0; $r < $board->rows; $r++) {
            for ($c = 0; $c < $board->columns; $c++) {
                // Prüfe ob Position schon belegt ist
                $existingField = Field::where(Field::board_id, $board->id)
                    ->where(function ($query) use ($r, $c) {
                        $query->where(function ($q) use ($r, $c) {
                            $q->where(Field::row, '<=', $r)
                              ->whereRaw('`row` + `height` > ?', [$r])
                              ->where(Field::column, '<=', $c)
                              ->whereRaw('`column` + `width` > ?', [$c]);
                        });
                    })
                    ->first();

                if (!$existingField) {
                    Field::create([
                        Field::board_id => $board->id,
                        Field::name => 'Feld ' . chr(65 + $r) . ($c + 1) . ' (' . $board->name . ')',
                        Field::row => $r,
                        Field::column => $c,
                        Field::width => 1,
                        Field::height => 1,
                        Field::price_per_month => $standardPrice,
                        Field::status => rand(0, 3) > 0 ? Field::STATUS_AVAILABLE : Field::STATUS_RENTED,
                        Field::description => 'Standardfeld mit guter Sichtbarkeit.',
                    ]);
                }
            }
        }
    }

    /**
     * Get description for field based on name and price.
     */
    private function getFieldDescription(string $name, float $price): string
    {
        return match (true) {
            str_contains($name, 'Tor') => 'Premium-Position mit höchster Aufmerksamkeit. Ideal für Hauptsponsoren.',
            str_contains($name, 'Elfmeterpunkt') => 'Zentrale Position mit maximaler Sichtbarkeit während spannender Spielmomente.',
            str_contains($name, 'Mittelkreis') => 'Herz des Spielfelds - wird zu Spielbeginn und bei jedem Anstoß gesehen.',
            str_contains($name, 'Strafraum') => 'Hochfrequentierte Zone mit vielen Action-Momenten.',
            str_contains($name, 'Mittellinie') => 'Gute Sichtbarkeit über die gesamte Spielzeit.',
            str_contains($name, 'Mittelfeld') => 'Solide Position im Zentrum des Geschehens.',
            str_contains($name, 'Außenlinie') => 'Kostengünstige Option an der Seitenlinie.',
            str_contains($name, 'Eckfahne') => 'Sichtbar bei Eckbällen und Auswechslungen.',
            default => 'Attraktive Werbefläche auf dem Spielfeld.',
        };
    }
}
