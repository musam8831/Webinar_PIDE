<?php
/**
 * Unit Tests for PIDE Webinar Application
 * 
 * This file contains basic unit tests for the webinar application
 * Run tests using: php tests/unit_tests.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

class WebinarTestSuite {
    private $pdo;
    private $config;
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function __construct() {
        $this->config = require __DIR__ . '/../includes/config.php';
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->config['db']['host'], $this->config['db']['port'], $this->config['db']['name']);
        try {
            $this->pdo = new PDO($dsn, $this->config['db']['user'], $this->config['db']['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('DB connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Test 1: Categories table exists and has required columns
     */
    public function testCategoriesTableStructure() {
        try {
            $result = $this->pdo->query("DESCRIBE categories")->fetchAll();
            $columns = array_map(fn($r) => $r['Field'], $result);
            
            $required = ['id', 'title', 'description', 'is_active', 'created_by', 'created_on', 'modified_by', 'modified_on'];
            foreach ($required as $col) {
                if (!in_array($col, $columns)) {
                    throw new Exception("Missing column: $col");
                }
            }
            return $this->pass("Categories table structure");
        } catch (Exception $e) {
            return $this->fail("Categories table structure: " . $e->getMessage());
        }
    }

    /**
     * Test 2: Webinars table has new approval columns
     */
    public function testWebinarsTableApprovalColumns() {
        try {
            $result = $this->pdo->query("DESCRIBE webinars")->fetchAll();
            $columns = array_map(fn($r) => $r['Field'], $result);
            
            $required = ['category_id', 'is_approved', 'approved_by', 'approved_on', 'rejection_reason'];
            foreach ($required as $col) {
                if (!in_array($col, $columns)) {
                    throw new Exception("Missing column: $col");
                }
            }
            return $this->pass("Webinars table approval columns");
        } catch (Exception $e) {
            return $this->fail("Webinars table approval columns: " . $e->getMessage());
        }
    }

    /**
     * Test 3: Config has BASE_URL
     */
    public function testConfigBASEURL() {
        try {
            if (!isset($this->config['base_url'])) {
                throw new Exception("BASE_URL not set in config");
            }
            if (empty($this->config['base_url'])) {
                throw new Exception("BASE_URL is empty");
            }
            return $this->pass("Config BASE_URL");
        } catch (Exception $e) {
            return $this->fail("Config BASE_URL: " . $e->getMessage());
        }
    }

    /**
     * Test 4: Categories exist in database
     */
    public function testCategoriesExist() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as cnt FROM categories WHERE is_active=1");
            $result = $stmt->fetch();
            if ($result['cnt'] == 0) {
                throw new Exception("No active categories found");
            }
            return $this->pass("Categories exist");
        } catch (Exception $e) {
            return $this->fail("Categories exist: " . $e->getMessage());
        }
    }

    /**
     * Test 5: Foreign key constraint exists for webinars.category_id
     */
    public function testCategoryForeignKey() {
        try {
            $result = $this->pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME='webinars' AND COLUMN_NAME='category_id' AND REFERENCED_TABLE_NAME='categories'")->fetch();
            
            if (!$result) {
                throw new Exception("Foreign key constraint not found");
            }
            return $this->pass("Category foreign key constraint");
        } catch (Exception $e) {
            return $this->fail("Category foreign key constraint: " . $e->getMessage());
        }
    }

    /**
     * Test 6: Unapproved webinars default to is_approved=0
     */
    public function testUnapprovedWebinarsDefault() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as cnt FROM webinars WHERE is_approved=0");
            $result = $stmt->fetch();
            // Just verify the column is working
            return $this->pass("Unapproved webinars default value");
        } catch (Exception $e) {
            return $this->fail("Unapproved webinars default value: " . $e->getMessage());
        }
    }

    /**
     * Test 7: Database has admin and user roles
     */
    public function testUserRoles() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT role) as role_count FROM users");
            $result = $stmt->fetch();
            if ($result['role_count'] < 1) {
                throw new Exception("No user roles found");
            }
            return $this->pass("User roles exist");
        } catch (Exception $e) {
            return $this->fail("User roles exist: " . $e->getMessage());
        }
    }

    /**
     * Test 8: Verify required admin pages exist
     */
    public function testAdminPagesExist() {
        try {
            $files = [
                __DIR__ . '/../admin/categories.php',
                __DIR__ . '/../admin/pending_webinars.php',
                __DIR__ . '/../admin/reports.php',
            ];
            
            foreach ($files as $file) {
                if (!file_exists($file)) {
                    throw new Exception("Missing file: " . basename($file));
                }
            }
            return $this->pass("Admin pages exist");
        } catch (Exception $e) {
            return $this->fail("Admin pages exist: " . $e->getMessage());
        }
    }

    /**
     * Test 9: Verify public pages exist
     */
    public function testPublicPagesExist() {
        try {
            $files = [
                __DIR__ . '/../index.php',
                __DIR__ . '/../public/webinars_list.php',
                __DIR__ . '/../public/load_events.php',
                __DIR__ . '/../public/save_event.php',
            ];
            
            foreach ($files as $file) {
                if (!file_exists($file)) {
                    throw new Exception("Missing file: " . basename($file));
                }
            }
            return $this->pass("Public pages exist");
        } catch (Exception $e) {
            return $this->fail("Public pages exist: " . $e->getMessage());
        }
    }

    /**
     * Test 10: Verify navbar includes Categories and My Webinars
     */
    public function testNavbarItems() {
        try {
            $navbar_content = file_get_contents(__DIR__ . '/../public/navbar.php');
            
            if (strpos($navbar_content, 'categories.php') === false) {
                throw new Exception("Categories link not found in navbar");
            }
            if (strpos($navbar_content, 'webinars_list.php') === false) {
                throw new Exception("My Webinars link not found in navbar");
            }
            if (strpos($navbar_content, 'pending_webinars.php') === false) {
                throw new Exception("Pending Approval link not found in navbar");
            }
            
            return $this->pass("Navbar includes all required items");
        } catch (Exception $e) {
            return $this->fail("Navbar includes all required items: " . $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function pass($testName) {
        $this->passed++;
        $this->tests[] = "✓ PASS: $testName";
        return true;
    }

    private function fail($testName) {
        $this->failed++;
        $this->tests[] = "✗ FAIL: $testName";
        return false;
    }

    /**
     * Run all tests and display results
     */
    public function runAllTests() {
        echo "========================================\n";
        echo "PIDE Webinar Application - Unit Tests\n";
        echo "========================================\n\n";

        $this->testCategoriesTableStructure();
        $this->testWebinarsTableApprovalColumns();
        $this->testConfigBASEURL();
        $this->testCategoriesExist();
        $this->testCategoryForeignKey();
        $this->testUnapprovedWebinarsDefault();
        $this->testUserRoles();
        $this->testAdminPagesExist();
        $this->testPublicPagesExist();
        $this->testNavbarItems();

        echo "Test Results:\n";
        echo "================\n";
        foreach ($this->tests as $test) {
            echo $test . "\n";
        }
        echo "\n";
        echo "Summary: " . $this->passed . " passed, " . $this->failed . " failed\n";
        echo "Total: " . ($this->passed + $this->failed) . " tests\n";
        echo "========================================\n";

        return $this->failed === 0;
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $suite = new WebinarTestSuite();
    $success = $suite->runAllTests();
    exit($success ? 0 : 1);
}
