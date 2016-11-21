<?php

 namespace PimcoreMigrations\Migration;


 interface MigrationInterface
 {

     const UP = 'up';

     const DOWN = 'down';

     /**
      * Migrate Up
      *
      * @return void
      */
     public function up();

     /**
      * Migrate Down
      *
      * @return void
      */
     public function down();


     /**
      * Returns the version number of the migration
      * @return int
      */
     public function getVersion();


 }
