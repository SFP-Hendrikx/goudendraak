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
            $insertStatements = $this->filterInsertStatements($statements);

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

    private function filterInsertStatements(array $statements): array
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

            preg_match('/INSERT INTO\s+`?([a-zA-Z0-9_]+)`?/i', $statement, $matches);

            if (! isset($matches[1])) {
                continue;
            }

            $tableName = $matches[1];

            if (in_array($tableName, $allowedTables, true)) {
                $insertStatements[] = $statement;
            }
        }

        return $insertStatements;
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