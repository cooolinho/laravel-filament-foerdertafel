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
                'price' => 10,
                'width' => 2,
                'height' => 2,
            ],
            [
                'names' => ['Elfmeterpunkt', 'Mittelkreis'],
                'price' => 10,
                'width' => 2,
                'height' => 2,
            ],
            [
                'names' => ['Strafraum Links', 'Strafraum Rechts'],
                'price' => 10,
                'width' => 3,
                'height' => 2,
            ],
            // Mittelfeld Felder (mittel)
            [
                'names' => ['Mittellinie Mitte', 'Mittelfeld Links', 'Mittelfeld Rechts'],
                'price' => 10,
                'width' => 2,
                'height' => 1,
            ],
            // Standard Felder (günstig)
            [
                'names' => ['Außenlinie Links', 'Außenlinie Rechts', 'Eckfahne'],
                'price' => 10,
                'width' => 1,
                'height' => 1,
            ],
        ];

        $createdFields = [];
        $maxAttempts = 100;
        $attempt = 0;

        // Erstelle verschiedene Feldtypen
        foreach ($fieldTypes as $typeIndex => $fieldType) {
            foreach ($fieldType['names'] as $nameIndex => $name) {
                if ($attempt >= $maxAttempts) {
                    break 2;
                }

                // Finde eine freie Position für dieses Feld
                $placed = false;
                for ($tryRow = 1; $tryRow <= $board->{Board::rows} && !$placed; $tryRow++) {
                    for ($tryCol = 1; $tryCol <= $board->{Board::columns} && !$placed; $tryCol++) {
                        $attempt++;

                        // Prüfe ob Feld ins Board passt
                        if ($tryRow + $fieldType['height'] - 1 > $board->{Board::rows} ||
                            $tryCol + $fieldType['width'] - 1 > $board->{Board::columns}) {
                            continue;
                        }

                        // Prüfe auf Überlappung mit bereits erstellten Feldern
                        if ($this->hasOverlap($tryRow, $tryCol, $fieldType['width'], $fieldType['height'], $createdFields)) {
                            continue;
                        }

                        // Wähle Status basierend auf Feldtyp
                        $status = match ($typeIndex) {
                            0, 1 => Field::STATUS_RENTED, // Premium Felder meist vermietet
                            2 => rand(0, 1) ? Field::STATUS_RENTED : Field::STATUS_AVAILABLE,
                            3 => rand(0, 2) ? Field::STATUS_AVAILABLE : Field::STATUS_RENTED,
                            default => Field::STATUS_AVAILABLE,
                        };

                        $field = Field::create([
                            Field::board_id => $board->id,
                            Field::name => $name . ' (' . $board->{Board::name} . ')',
                            Field::row => $tryRow,
                            Field::column => $tryCol,
                            Field::width => $fieldType['width'],
                            Field::height => $fieldType['height'],
                            Field::price_per_month => $fieldType['price'],
                            Field::status => $status,
                            Field::description => $this->getFieldDescription($name, $fieldType['price']),
                        ]);

                        $createdFields[] = [
                            'row' => $tryRow,
                            'column' => $tryCol,
                            'width' => $fieldType['width'],
                            'height' => $fieldType['height'],
                        ];

                        $placed = true;
                    }
                }
            }
        }

        // Fülle restliche Positionen mit Standard-Feldern
        $standardPrice = 2;
        for ($r = 1; $r <= $board->{Board::rows}; $r++) {
            for ($c = 1; $c <= $board->{Board::columns}; $c++) {
                // Prüfe ob Position schon belegt ist
                if ($this->hasOverlap($r, $c, 1, 1, $createdFields)) {
                    continue;
                }

                $field = Field::create([
                    Field::board_id => $board->id,
                    Field::name => 'Feld ' . chr(64 + $r) . $c . ' (' . $board->{Board::name} . ')',
                    Field::row => $r,
                    Field::column => $c,
                    Field::width => 1,
                    Field::height => 1,
                    Field::price_per_month => $standardPrice,
                    Field::status => rand(0, 3) > 0 ? Field::STATUS_AVAILABLE : Field::STATUS_RENTED,
                    Field::description => 'Standardfeld mit guter Sichtbarkeit.',
                ]);

                $createdFields[] = [
                    'row' => $r,
                    'column' => $c,
                    'width' => 1,
                    'height' => 1,
                ];
            }
        }
    }

    /**
     * Check if a field would overlap with existing fields
     */
    private function hasOverlap(int $newRow, int $newCol, int $newWidth, int $newHeight, array $existingFields): bool
    {
        foreach ($existingFields as $existing) {
            $existingRow = $existing['row'];
            $existingCol = $existing['column'];
            $existingWidth = $existing['width'];
            $existingHeight = $existing['height'];

            // Prüfe auf Überlappung in beide Richtungen
            $rowOverlap = $newRow < $existingRow + $existingHeight &&
                         $newRow + $newHeight > $existingRow;
            $colOverlap = $newCol < $existingCol + $existingWidth &&
                         $newCol + $newWidth > $existingCol;

            if ($rowOverlap && $colOverlap) {
                return true;
            }
        }

        return false;
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
