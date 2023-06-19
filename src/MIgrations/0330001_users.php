<?php
use Myframework\Migration\MigrationInterface;
// use
class Users implements MigrationInterface
{
    public function up()
    {
        echo "called the up function for the migration user";

    }
    public function down()
    {
        echo "called the down function for the migration user";
    }
}  