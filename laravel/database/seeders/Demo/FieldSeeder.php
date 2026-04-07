<?php

namespace Database\Seeders\Demo;

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
        $createdFields = [];

        // 4 Ecken mit 1x1 Feldern
        $corners = [
            ['row' => 1, 'col' => 1, 'name' => 'Ecke Oben Links'],
            ['row' => 1, 'col' => $board->{Board::columns}, 'name' => 'Ecke Oben Rechts'],
            ['row' => $board->{Board::rows}, 'col' => 1, 'name' => 'Ecke Unten Links'],
            ['row' => $board->{Board::rows}, 'col' => $board->{Board::columns}, 'name' => 'Ecke Unten Rechts'],
        ];

        foreach ($corners as $corner) {
            $cornerField = Field::create([
                Field::board_id => $board->id,
                Field::name => $corner['name'],
                Field::row => $corner['row'],
                Field::column => $corner['col'],
                Field::width => 1,
                Field::height => 1,
                Field::price_per_month => 5.00,
                Field::status => Field::STATUS_AVAILABLE,
                Field::description => 'Eckfeld - sichtbar bei Eckbällen und Auswechslungen.',
            ]);

            $createdFields[] = [
                'row' => $corner['row'],
                'column' => $corner['col'],
                'width' => $cornerField->width,
                'height' => $cornerField->height,
            ];
        }

        // 2 Tore mit 1x5 Feldern (vertikal)
        // Tor Links: startend bei (row=7, col=1)
        $goalLeft = Field::create([
            Field::board_id => $board->id,
            Field::name => 'Tor Links',
            Field::row => 7,
            Field::column => 1,
            Field::width => 1,
            Field::height => 5,
            Field::price_per_month => 50.00,
            Field::status => Field::STATUS_AVAILABLE,
            Field::description => 'Premium-Position am linken Tor mit höchster Aufmerksamkeit. Ideal für Hauptsponsoren.',
        ]);

        $createdFields[] = [
            'row' => $goalLeft->row,
            'column' => $goalLeft->column,
            'width' => $goalLeft->width,
            'height' => $goalLeft->height,
        ];

        // Tor Rechts: startend bei (row=7, col=16)
        $goalRight = Field::create([
            Field::board_id => $board->id,
            Field::name => 'Tor Rechts',
            Field::row => 7,
            Field::column => 16,
            Field::width => 1,
            Field::height => 5,
            Field::price_per_month => 50.00,
            Field::status => Field::STATUS_AVAILABLE,
            Field::description => 'Premium-Position am rechten Tor mit höchster Aufmerksamkeit. Ideal für Hauptsponsoren.',
        ]);

        $createdFields[] = [
            'row' => $goalRight->row,
            'column' => $goalRight->column,
            'width' => $goalRight->width,
            'height' => $goalRight->height,
        ];

        // Mittelkreis mit 4x5 Feldern (horizontal): startend bei (row=7, col=7)
        $middleCircle = Field::create([
            Field::board_id => $board->id,
            Field::name => 'Mittelkreis',
            Field::row => 7,
            Field::column => 7,
            Field::width => 4,
            Field::height => 5,
            Field::price_per_month => 100.00,
            Field::status => Field::STATUS_AVAILABLE,
            Field::description => 'Herz des Spielfelds - wird zu Spielbeginn und bei jedem Anstoß gesehen. Premium-Position mit maximaler Sichtbarkeit.',
        ]);

        $createdFields[] = [
            'row' => $middleCircle->row,
            'column' => $middleCircle->column,
            'width' => $middleCircle->width,
            'height' => $middleCircle->height,
        ];

        // Fülle restliche Positionen mit Standard-Feldern
        $standardPrice = 10.00;
        for ($r = 1; $r <= $board->{Board::rows}; $r++) {
            for ($c = 1; $c <= $board->{Board::columns}; $c++) {
                // Prüfe ob Position schon belegt ist
                if ($this->hasOverlap($r, $c, 1, 1, $createdFields)) {
                    continue;
                }

                // Generiere Feldnamen im Format A1, B2, etc.
                $fieldName = chr(64 + $r) . $c;

                Field::create([
                    Field::board_id => $board->id,
                    Field::name => 'Feld ' . $fieldName,
                    Field::row => $r,
                    Field::column => $c,
                    Field::width => 1,
                    Field::height => 1,
                    Field::price_per_month => $standardPrice,
                    Field::status => Field::STATUS_AVAILABLE,
                    Field::description => 'Standardfeld mit guter Sichtbarkeit auf dem Spielfeld.',
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
}
