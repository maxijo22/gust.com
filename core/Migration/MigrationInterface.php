<?php

namespace Myframework\Migration;

interface MigrationInterface
{
    public function up();
    public function down();
}
