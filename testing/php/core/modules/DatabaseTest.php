<?php
/**
 * Test the Module\Core\Database class.
 */

namespace Test\Core\Module;

use \PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase {

    /**
     * Make sure no errors are thrown instantiating a new Database.
     *
     * @return Database The Database instance on success.
     */
    public function testDatabaseClassInstantiatedSuccessfully() {
        $database = new \Module\Core\Database();
        $this->assertIsObject($database);
        return $database;
    }

    /**
     * Test that we can connect to the test database.
     * 
     * @param Database $database The Database instance.
     * 
     * @return mysqli The active MySQL connection object on success.
     * 
     * @depends testDatabaseClassInstantiatedSuccessfully
     */
    public function testConnectedSuccessfully($database) {
        $db = $database->connect();
        $this->assertIsObject($db);
        $result = $db->query("SHOW TABLES");
        $this->assertIsObject($result);
        $this->assertIsInt($result->field_count);
        return $db;
    }

    /**
     * Test that we can create tables in the test database.
     * 
     * @param mysqli $db The active MySQL connection object.
     * 
     * @return mysqli The active MySQL connection on success.
     * 
     * @depends testConnectedSuccessfully
     */
    public function testCreatedTable($db) {
        $sql    = 'CREATE TABLE create_test ( id INT NOT NULL AUTO_INCREMENT, created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  inserted DATETIME NULL,  updated DATETIME NULL, PRIMARY KEY (id)) ENGINE = InnoDB;';
        $result = $db->query($sql);
        $this->assertTrue($result);
        return $db;
    }

    /**
     * Test that we can insert new data into the test table.
     * 
     * @param mysqli $db The active MySQL connection object.
     * 
     * @return mysqli The active MySQL connection on success.
     * 
     * @depends testCreatedTable
     */
    public function testInsertData($db) {
        $sql    = "INSERT INTO create_test (created, inserted, updated) VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL), (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);";
        $result = $db->query($sql);
        $this->assertTrue($result);
        return $db;
    }

    /**
     * Test that we can update and delete records in the test database.
     *  
     * @param mysqli $db The active MySQL connection object.
     * 
     * @return mysqli The active MySQL connection on success.
     * 
     * @depends testInsertData
     */
    public function testUpdatedAndDeletedRecords($db) {
        // Update record 1.
        $sql    = "UPDATE create_test SET updated = CURRENT_TIMESTAMP WHERE id = 1;";
        $result = $db->query($sql);
        $this->assertTrue($result);
        // Update record 2.
        $sql    = "UPDATE create_test SET updated = CURRENT_TIMESTAMP WHERE id = 2;";
        $result = $db->query($sql);
        $this->assertTrue($result);
        // Delete record 1.
        $sql    = "DELETE FROM create_test WHERE id = 1;";
        $result = $db->query($sql);
        $this->assertTrue($result);
        // Delete record 2.
        $sql    = "DELETE FROM create_test WHERE id = 2;";
        $result = $db->query($sql);
        $this->assertTrue($result);
        return $db;
    }

    /**
     * Test that we can drop a table in the test database.
     * 
     * @param mysqli $db The active MySQL connection object.
     * 
     * @return mysqli The active MySQL connection on success.
     *  
     * @depends testUpdatedAndDeletedRecords
     */
    public function testDroppedTestTables($db) {
        // Drop the tables from this test.
        $sql    = 'DROP TABLE create_test;';
        $result = $db->query($sql);
        $this->assertTrue($result);
        return $db;
    }

    /**
     * Test that we can close the database connection without triggering any
     * errors; a common error is connection already closed.
     * 
     * @param mysqli $db The active MySQL connection object.
     * 
     * @return void
     * 
     * @depends testDroppedTestTables
     */
    public function testClosedConnectionWithoutError($db) {
        try {
            $this->assertTrue($db->close());
        } catch(\Exception $e) {
            $this->fail('Database connection did not close properly.');
        }
    }

}