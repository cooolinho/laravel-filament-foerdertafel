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

        // Fülle restliche Positionen mit Standard-Feldern
        $standardPrice = 2.5;
        for ($r = 1; $r <= $board->{Board::rows}; $r++) {
            for ($c = 1; $c <= $board->{Board::columns}; $c++) {
                // Prüfe ob Position schon belegt ist
                if ($this->hasOverlap($r, $c, 1, 1, $createdFields)) {
                    continue;
                }

                // Generiere Feldnamen im Format A1, B2, etc.
                $fieldName = chr(64 + $r) . $c;

                // Premium-Zonen prüfen
                $premium = $this->getPremiumData($r, $c);

                Field::create([
                    Field::board_id => $board->id,
                    Field::name => 'Feld ' . $fieldName,
                    Field::row => $r,
                    Field::column => $c,
                    Field::width => 1,
                    Field::height => 1,
                    Field::price_per_month => $premium ? $premium['price'] : $standardPrice,
                    Field::status => Field::STATUS_AVAILABLE,
                    Field::description => $premium ? $premium['description'] : 'Standardfeld mit guter Sichtbarkeit auf dem Spielfeld.',
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
     * Gibt Premium-Daten zurück, wenn die Position in einer Premium-Zone liegt.
     * Zonen basieren auf der Feldnamens-Konvention chr(64 + $row) . $col:
     *   - Torraum links:  D1–N3  (Zeilen 4–14, Spalten 1–3)
     *   - Torraum rechts: D16–N18 (Zeilen 4–14, Spalten 16–18)
     *   - Mittelkreis:    H9–J10  (Zeilen 8–10, Spalten 9–10)
     */
    private function getPremiumData(int $row, int $col): ?array
    {
        // Torraum links: D1 - N3
        if ($row >= 4 && $row <= 14 && $col >= 1 && $col <= 3) {
            return ['price' => 3.33, 'description' => 'Premium Feld Torraum'];
        }

        // Torraum rechts: D16 - N18
        if ($row >= 4 && $row <= 14 && $col >= 16 && $col <= 18) {
            return ['price' => 3.33, 'description' => 'Premium Feld Torraum'];
        }

        // Mittelkreis: H9 - J10
        if ($row >= 8 && $row <= 10 && $col >= 9 && $col <= 10) {
            return ['price' => 3.33, 'description' => 'Premium Feld Mittelkreis'];
        }

        return null;
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
