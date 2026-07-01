<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OldGoudenDraakSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = database_path('seeders/data/gouden_draak_create_script.sql');

        if (! file_exists($sqlPath)) {
            throw new RuntimeException("SQL bestand niet gevonden: {$sqlPath}");
        }

        $sql = file_get_contents($sqlPath);

        if ($sql === false) {
            throw new RuntimeException("SQL bestand kan niet gelezen worden: {$sqlPath}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $this->clearOldTables();

            $statements = $this->splitSqlStatements($sql);
            $insertStatements = $this->filterAndNormalizeInsertStatements($statements);

            if (count($insertStatements) === 0) {
                throw new RuntimeException('Geen INSERT statements gevonden in het SQL bestand.');
            }

            foreach ($insertStatements as $statement) {
                DB::unprepared($statement);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function clearOldTables(): void
    {
        DB::table('specialiteiten')->truncate();
        DB::table('sales')->truncate();
        DB::table('menu_pdf')->truncate();
        DB::table('menu')->truncate();
        DB::table('gebruiker')->truncate();
        DB::table('rijst_enzo')->truncate();
    }

    private function filterAndNormalizeInsertStatements(array $statements): array
    {
        $allowedTables = [
            'gebruiker',
            'menu',
            'menu_pdf',
            'rijst_enzo',
            'sales',
            'specialiteiten',
        ];

        $insertStatements = [];

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if (! str_starts_with(strtoupper($statement), 'INSERT INTO')) {
                continue;
            }

            preg_match('/INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?/i', $statement, $matches);

            if (! isset($matches[1])) {
                continue;
            }

            $tableName = $matches[1];

            if (! in_array($tableName, $allowedTables, true)) {
                continue;
            }

            $insertStatements[] = $this->normalizeInsertStatement($statement, $tableName);
        }

        return $insertStatements;
    }

    private function normalizeInsertStatement(string $statement, string $tableName): string
    {
        /*
         * Als de SQL al kolomnamen heeft, bijvoorbeeld:
         * INSERT INTO `menu` (`id`, `naam`) VALUES (...)
         * dan laten we het statement ongemoeid.
         */
        if ($this->insertStatementHasColumnList($statement, $tableName)) {
            return $statement;
        }

        $valueCount = $this->countValuesInFirstInsertRow($statement);
        $columns = $this->getLegacyColumnsForTable($tableName);

        if ($valueCount !== count($columns)) {
            throw new RuntimeException(
                "Kolom-mapping voor tabel '{$tableName}' klopt niet. " .
                "INSERT heeft {$valueCount} waardes, mapping heeft " . count($columns) . ' kolommen.'
            );
        }

        foreach ($columns as $column) {
            if (! $this->columnExists($tableName, $column)) {
                throw new RuntimeException("Kolom '{$column}' bestaat niet in tabel '{$tableName}'.");
            }
        }

        $quotedColumns = implode(', ', array_map(
            static fn (string $column): string => '`' . str_replace('`', '``', $column) . '`',
            $columns
        ));

        $pattern = '/^(\s*INSERT\s+INTO\s+`?' . preg_quote($tableName, '/') . '`?\s+)VALUES\b/i';

        $normalizedStatement = preg_replace_callback(
            $pattern,
            static fn (array $matches): string => $matches[1] . '(' . $quotedColumns . ') VALUES',
            $statement,
            1
        );

        if ($normalizedStatement === null) {
            throw new RuntimeException("Kon INSERT statement voor tabel '{$tableName}' niet normaliseren.");
        }

        return $normalizedStatement;
    }

    private function getLegacyColumnsForTable(string $tableName): array
    {
        /*
         * Dit zijn de kolommen zoals ze in de OUDE SQL dump staan,
         * gemapt naar de kolomnamen van jouw huidige Laravel-tabellen.
         */
        $legacyColumnMap = [
            'gebruiker' => [
                'id',
                'wachtwoord',
                'isAdmin',
            ],

            'menu' => [
                'id',
                'menunummer',
                'menu_toevoeging',
                'naam',
                'price',
                'soortgerecht',
                'beschrijving',
            ],

            'menu_pdf' => [
                'id',
                'menu_pdf',
            ],

            'rijst_enzo' => [
                'id',
                'beschrijving',
                'extra_prijs',
            ],

            'sales' => [
                'id',
                'itemId',
                'amount',
                'saleDate',
            ],

            'specialiteiten' => [
                'id',
                'name',
                'informatie',
                'prijs',
                'rijst_keuze_etc',
            ],
        ];

        if (! isset($legacyColumnMap[$tableName])) {
            throw new RuntimeException("Geen legacy kolom-mapping gevonden voor tabel '{$tableName}'.");
        }

        return $legacyColumnMap[$tableName];
    }

    private function insertStatementHasColumnList(string $statement, string $tableName): bool
    {
        $pattern = '/^\s*INSERT\s+INTO\s+`?' . preg_quote($tableName, '/') . '`?\s*\(/i';

        return preg_match($pattern, $statement) === 1;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $databaseName = DB::getDatabaseName();

        $rows = DB::select(
            '
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1
            ',
            [$databaseName, $tableName, $columnName]
        );

        return count($rows) > 0;
    }

    private function countValuesInFirstInsertRow(string $statement): int
    {
        if (! preg_match('/\bVALUES\b/i', $statement, $matches, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException('Geen VALUES gevonden in INSERT statement.');
        }

        $start = $matches[0][1] + strlen($matches[0][0]);
        $length = strlen($statement);

        $inSingleQuote = false;
        $inDoubleQuote = false;
        $escaped = false;

        $rowStarted = false;
        $parenthesesDepth = 0;
        $valueCount = 1;
        $hasValueContent = false;

        for ($i = $start; $i < $length; $i++) {
            $char = $statement[$i];

            if ($escaped) {
                $escaped = false;

                if ($rowStarted) {
                    $hasValueContent = true;
                }

                continue;
            }

            if ($char === '\\') {
                $escaped = true;

                if ($rowStarted) {
                    $hasValueContent = true;
                }

                continue;
            }

            if ($char === "'" && ! $inDoubleQuote) {
                $inSingleQuote = ! $inSingleQuote;

                if ($rowStarted) {
                    $hasValueContent = true;
                }

                continue;
            }

            if ($char === '"' && ! $inSingleQuote) {
                $inDoubleQuote = ! $inDoubleQuote;

                if ($rowStarted) {
                    $hasValueContent = true;
                }

                continue;
            }

            if ($inSingleQuote || $inDoubleQuote) {
                if ($rowStarted) {
                    $hasValueContent = true;
                }

                continue;
            }

            if (! $rowStarted) {
                if ($char === '(') {
                    $rowStarted = true;
                    $parenthesesDepth = 1;
                }

                continue;
            }

            if ($char === '(') {
                $parenthesesDepth++;
                $hasValueContent = true;
                continue;
            }

            if ($char === ')') {
                $parenthesesDepth--;

                if ($parenthesesDepth === 0) {
                    return $hasValueContent ? $valueCount : 0;
                }

                $hasValueContent = true;
                continue;
            }

            if ($char === ',' && $parenthesesDepth === 1) {
                $valueCount++;
                continue;
            }

            if (! ctype_space($char)) {
                $hasValueContent = true;
            }
        }

        throw new RuntimeException('Kon eerste rij van INSERT statement niet uitlezen.');
    }

    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $statement = '';

        $inSingleQuote = false;
        $inDoubleQuote = false;
        $escaped = false;

        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $statement .= $char;

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === "'" && ! $inDoubleQuote) {
                $inSingleQuote = ! $inSingleQuote;
                continue;
            }

            if ($char === '"' && ! $inSingleQuote) {
                $inDoubleQuote = ! $inDoubleQuote;
                continue;
            }

            if ($char === ';' && ! $inSingleQuote && ! $inDoubleQuote) {
                $statements[] = trim($statement);
                $statement = '';
            }
        }

        if (trim($statement) !== '') {
            $statements[] = trim($statement);
        }

        return $statements;
    }
}
