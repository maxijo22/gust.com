<?php

namespace Myframework\Migration;

use Myframework\db\QueryBuilder as DB;
use Myframework\db\Transaction;

class BaseMigration
{

    private const TABLE = 'migrations';
    public function __construct(private string $migrations_path)
    {

    }

    public function apply()
    {

        $this->createTable();
        $appliedMigrations = $this->appliedMigrations();
        $migrations = $this->getMigrations();
        $toAppliedMigrations = array_diff($migrations, $appliedMigrations);


        if (empty($toAppliedMigrations)) {
            dd("All migrations have already been applied");
        }

        echo "Appling migrations \n\n";

        foreach ($toAppliedMigrations as $toBeApplied) {
            # code...

            require_once $this->migrations_path . DS . $toBeApplied;

            $class = ucfirst(preg_replace("#['0-9_?(.php)']#", '', $toBeApplied));

            $classInstance = new $class();
            if (!$classInstance instanceof MigrationInterface) {
                dd("$toBeApplied migration does must implement the MigrationInterface");
            }

            $classInstance->up();

            DB::table(self::TABLE)->insert(['migration' => $toBeApplied])->execute();


            echo " migration $toBeApplied applied \n";
        }



        die("finished appling migrations \n");
    }

    private function createTable(): bool
    {
        $db = new Transaction();
        $query = "CREATE TABLE IF NOT EXISTS migrations (
           `id` int NOT NULL AUTO_INCREMENT , 
           `migration` VARCHAR(255) NOT NULL ,
           `created_at` DATETIME NOT NULL DEFAULT NOW() ,
            PRIMARY KEY(`id`)
        )";
        return ($db->execute($query) > 0);
    }

    private function appliedMigrations(): array
    {
        return DB::select('migration')->from('migrations')->query('column');
    }

    private function getMigrations(): array
    {
        $migrations = scandir($this->migrations_path);
        $migrations = array_filter($migrations, function ($array) {
            if ($array == '.' | $array == '..') {
                return false;
            }
            return true;
        });
        return array_values($migrations);
    }

}