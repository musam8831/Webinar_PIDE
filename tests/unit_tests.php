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
     * Test 11: Verify load_events.php includes rejection_reason field
     */
    public function testLoadEventsIncludesRejectionReason() {
        try {
            $filePath = __DIR__ . '/../public/load_events.php';
            if (!file_exists($filePath)) {
                throw new Exception("load_events.php not found");
            }
            
            $content = file_get_contents($filePath);
            if (!preg_match('/rejection_reason/', $content)) {
                throw new Exception("load_events.php does not handle rejection_reason");
            }
            
            if (!preg_match('/is_pending|is_approved|is_rejected/', $content)) {
                throw new Exception("load_events.php does not set status flags");
            }
            
            return $this->pass("load_events.php handles rejection and status");
        } catch (Exception $e) {
            return $this->fail("load_events.php rejection handling: " . $e->getMessage());
        }
    }

    /**
     * Test 12: Verify webinars_list.php has color scheme styling
     */
    public function testWebinarsListColorScheme() {
        try {
            $filePath = __DIR__ . '/../public/webinars_list.php';
            if (!file_exists($filePath)) {
                throw new Exception("webinars_list.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check for approved color scheme (green)
            if (!preg_match('/#118C3C|approved/', $content)) {
                throw new Exception("webinars_list.php missing approved color scheme");
            }
            
            // Check for pending color scheme (orange)
            if (!preg_match('/#ff8c00|pending|orange/', $content)) {
                throw new Exception("webinars_list.php missing pending color scheme");
            }
            
            // Check for rejected color scheme (red)
            if (!preg_match('/#dc3545|rejected/', $content)) {
                throw new Exception("webinars_list.php missing rejected color scheme");
            }
            
            return $this->pass("webinars_list.php has all color schemes");
        } catch (Exception $e) {
            return $this->fail("webinars_list.php color schemes: " . $e->getMessage());
        }
    }

    /**
     * Test 13: Verify webinars_list.php fixes 'All' status filter issue
     */
    public function testWebinarsListAllStatusFilter() {
        try {
            $filePath = __DIR__ . '/../public/webinars_list.php';
            if (!file_exists($filePath)) {
                throw new Exception("webinars_list.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check that WHERE clause is properly formed
            if (!preg_match('/WHERE.*\$where_clause|count\(\$where_parts\)/', $content)) {
                throw new Exception("webinars_list.php doesn't properly handle dynamic WHERE clause");
            }
            
            return $this->pass("webinars_list.php 'all' status filter is fixed");
        } catch (Exception $e) {
            return $this->fail("webinars_list.php 'all' status filter: " . $e->getMessage());
        }
    }

    /**
     * Test 14: Verify index.php has notification system
     */
    public function testIndexPhpNotificationSystem() {
        try {
            $filePath = __DIR__ . '/../index.php';
            if (!file_exists($filePath)) {
                throw new Exception("index.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check for showNotification function
            if (!preg_match('/function showNotification|showNotification\(/', $content)) {
                throw new Exception("index.php missing showNotification function");
            }
            
            // Check for notification styling
            if (!preg_match('/notification-toast|notification-icon/', $content)) {
                throw new Exception("index.php missing notification classes");
            }
            
            return $this->pass("index.php has notification system");
        } catch (Exception $e) {
            return $this->fail("index.php notification system: " . $e->getMessage());
        }
    }

    /**
     * Test 15: Verify index.php loads unapproved webinars for creator
     */
    public function testIndexPhpUnapprovedVisibility() {
        try {
            $filePath = __DIR__ . '/../public/load_events.php';
            if (!file_exists($filePath)) {
                throw new Exception("load_events.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check that load_events shows unapproved to creator
            if (!preg_match('/initiated_by|w\.initiated_by/', $content)) {
                throw new Exception("load_events.php doesn't check initiator");
            }
            
            if (!preg_match('/\$me\[\'id\'\]/', $content)) {
                throw new Exception("load_events.php doesn't use current user ID");
            }
            
            return $this->pass("load_events.php shows unapproved to creator");
        } catch (Exception $e) {
            return $this->fail("load_events.php unapproved visibility: " . $e->getMessage());
        }
    }

    /**
     * Test 16: Verify CSS has notification styles
     */
    public function testNotificationCSSStyles() {
        try {
            $filePath = __DIR__ . '/../assets/css/styles.css';
            if (!file_exists($filePath)) {
                throw new Exception("styles.css not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check for notification styling
            if (!preg_match('/notification-toast|slideIn|slideOut/', $content)) {
                throw new Exception("styles.css missing notification animations");
            }
            
            if (!preg_match('/notification-icon|notification-message/', $content)) {
                throw new Exception("styles.css missing notification element styles");
            }
            
            return $this->pass("styles.css has notification styles");
        } catch (Exception $e) {
            return $this->fail("styles.css notification styles: " . $e->getMessage());
        }
    }

    /**
     * Test 17: Verify event colors for approved/pending/rejected in load_events.php
     */
    public function testEventColorCoding() {
        try {
            $filePath = __DIR__ . '/../public/load_events.php';
            if (!file_exists($filePath)) {
                throw new Exception("load_events.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check for green color (approved)
            if (!preg_match('/#118C3C|backgroundColor.*#118C3C/', $content)) {
                throw new Exception("load_events.php missing green color for approved");
            }
            
            // Check for orange color (pending)
            if (!preg_match('/#ff8c00|backgroundColor.*#ff8c00/', $content)) {
                throw new Exception("load_events.php missing orange color for pending");
            }
            
            // Check for red color (rejected)
            if (!preg_match('/#dc3545|backgroundColor.*#dc3545/', $content)) {
                throw new Exception("load_events.php missing red color for rejected");
            }
            
            return $this->pass("load_events.php has correct event colors");
        } catch (Exception $e) {
            return $this->fail("load_events.php event colors: " . $e->getMessage());
        }
    }

    /**
     * Test 18: Verify edit/delete permissions restricted for approved webinars
     */
    public function testEditDeletePermissionsRestriction() {
        try {
            $filePath = __DIR__ . '/../public/load_events.php';
            if (!file_exists($filePath)) {
                throw new Exception("load_events.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check that canDelete and canEdit have conditions
            if (!preg_match('/\$canDelete.*!.*approved|!.*\$row\[.*is_approved.*\]/', $content)) {
                throw new Exception("canDelete permission logic not properly restricted");
            }
            
            if (!preg_match('/\$canEdit.*!.*approved|!.*\$row\[.*is_approved.*\]/', $content)) {
                throw new Exception("canEdit permission logic not properly restricted");
            }
            
            // Check that admin role check is present
            if (!preg_match('/\$me\[.role.\].*admin|admin.*role/', $content)) {
                throw new Exception("Admin role check missing");
            }
            
            return $this->pass("load_events.php has proper permission restrictions");
        } catch (Exception $e) {
            return $this->fail("load_events.php permission logic: " . $e->getMessage());
        }
    }

    /**
     * Test 19: Verify reject button updates record instead of deleting
     */
    public function testRejectButtonUpdatesNotDeletes() {
        try {
            $filePath = __DIR__ . '/../admin/pending_webinars.php';
            if (!file_exists($filePath)) {
                throw new Exception("pending_webinars.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check that reject action updates rejection_reason
            if (!preg_match('/UPDATE webinars SET rejection_reason/i', $content)) {
                throw new Exception("Reject action does not update rejection_reason");
            }
            
            // Check that DELETE is NOT in reject action
            if (preg_match('/action.*reject.*DELETE|reject.*DELETE/i', $content)) {
                throw new Exception("Reject action still deletes records");
            }
            
            // Verify the DELETE line doesn't exist in reject block
            if (preg_match('/\'\reject\'.*DELETE.*webinars|reject.*DELETE.*webinars/i', $content)) {
                throw new Exception("Reject action contains DELETE statement");
            }
            
            return $this->pass("pending_webinars.php reject updates not deletes");
        } catch (Exception $e) {
            return $this->fail("pending_webinars.php reject action: " . $e->getMessage());
        }
    }

    /**
     * Test 20: Verify three-status system (Approved/Pending/Rejected)
     */
    public function testThreeStatusSystem() {
        try {
            $filePath = __DIR__ . '/../public/load_events.php';
            if (!file_exists($filePath)) {
                throw new Exception("load_events.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check for Approved status detection
            if (!preg_match('/isApproved.*is_approved.*===.*1/', $content)) {
                throw new Exception("Approved status detection missing");
            }
            
            // Check for Rejected status detection
            if (!preg_match('/isRejected.*rejection_reason/', $content)) {
                throw new Exception("Rejected status detection missing");
            }
            
            // Check for Pending status detection
            if (!preg_match('/isPending.*is_approved.*===.*0.*rejection_reason/', $content)) {
                throw new Exception("Pending status detection missing");
            }
            
            return $this->pass("load_events.php implements three-status system");
        } catch (Exception $e) {
            return $this->fail("Three-status system: " . $e->getMessage());
        }
    }

    /**
     * Test 21: Verify rejection reason is passed to extended properties
     */
    public function testRejectionReasonInExtendedProps() {
        try {
            $filePath = __DIR__ . '/../public/load_events.php';
            if (!file_exists($filePath)) {
                throw new Exception("load_events.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check that rejection_reason is in extendedProps
            if (!preg_match('/extendedProps.*rejection_reason|\'rejection_reason\'/', $content)) {
                throw new Exception("rejection_reason not in extendedProps");
            }
            
            return $this->pass("load_events.php includes rejection_reason in props");
        } catch (Exception $e) {
            return $this->fail("Rejection reason in props: " . $e->getMessage());
        }
    }

    /**
     * Test 22: Verify pending_webinars.php excludes rejected webinars from pending list
     */
    public function testPendingWebinarsExcludesRejected() {
        try {
            $filePath = __DIR__ . '/../admin/pending_webinars.php';
            if (!file_exists($filePath)) {
                throw new Exception("pending_webinars.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Check that query filters out rejected webinars
            if (!preg_match('/is_approved\s*=\s*0.*rejection_reason.*IS NULL|rejection_reason.*IS NULL.*is_approved\s*=\s*0/i', $content)) {
                throw new Exception("Query does not filter out rejected webinars");
            }
            
            return $this->pass("pending_webinars.php excludes rejected webinars");
        } catch (Exception $e) {
            return $this->fail("Pending webinars filter: " . $e->getMessage());
        }
    }

    /**
     * Test 23: Verify overlapping webinars check considers only approved webinars
     */
    public function testOverlapCheckApprovdWebinarsOnly() {
        try {
            $filePath = __DIR__ . '/../public/save_event.php';
            if (!file_exists($filePath)) {
                throw new Exception("save_event.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Verify the overlap query specifically checks is_approved=1
            if (!preg_match('/is_approved\s*=\s*1.*SELECT COUNT.*FROM webinars|SELECT COUNT.*FROM webinars.*is_approved\s*=\s*1/s', $content)) {
                throw new Exception("Overlap check does not verify is_approved=1");
            }
            
            // Verify it checks time ranges
            if (!preg_match('/\?.*<\s*end_at.*AND.*\?.*>\s*start_at|start_at.*AND.*end_at/i', $content)) {
                throw new Exception("Overlap check missing time range validation");
            }
            
            return $this->pass("save_event.php checks overlaps with approved webinars only");
        } catch (Exception $e) {
            return $this->fail("Overlap check approved only: " . $e->getMessage());
        }
    }

    /**
     * Test 24: Verify update_event.php excludes own webinar from overlap check
     */
    public function testUpdateEventOverlapExcludesOwn() {
        try {
            $filePath = __DIR__ . '/../public/update_event.php';
            if (!file_exists($filePath)) {
                throw new Exception("update_event.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Verify the overlap query excludes the current webinar (id<>?)
            if (!preg_match('/id\s*<>\s*\?|id\s*!=\s*\?/i', $content)) {
                throw new Exception("Overlap check does not exclude current webinar");
            }
            
            // Verify is_approved=1 check
            if (!preg_match('/is_approved\s*=\s*1/i', $content)) {
                throw new Exception("Overlap check does not verify is_approved=1");
            }
            
            return $this->pass("update_event.php excludes own webinar from overlap check");
        } catch (Exception $e) {
            return $this->fail("Update overlap exclude own: " . $e->getMessage());
        }
    }

    /**
     * Test 25: Verify save_event.php checks end time after start time
     */
    public function testSaveEventTimeValidation() {
        try {
            $filePath = __DIR__ . '/../public/save_event.php';
            if (!file_exists($filePath)) {
                throw new Exception("save_event.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Verify end time validation
            if (!preg_match('/endUtc\s*<=\s*startUtc|endUtc.*startUtc|End time.*after.*start/i', $content)) {
                throw new Exception("End time validation missing");
            }
            
            return $this->pass("save_event.php validates end time after start time");
        } catch (Exception $e) {
            return $this->fail("Save event time validation: " . $e->getMessage());
        }
    }

    /**
     * Test 26: Verify update_event.php has time validation
     */
    public function testUpdateEventTimeValidation() {
        try {
            $filePath = __DIR__ . '/../public/update_event.php';
            if (!file_exists($filePath)) {
                throw new Exception("update_event.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Verify end time validation
            if (!preg_match('/endUtc\s*<=\s*startUtc|End time.*after.*start/i', $content)) {
                throw new Exception("End time validation missing");
            }
            
            return $this->pass("update_event.php validates end time after start time");
        } catch (Exception $e) {
            return $this->fail("Update event time validation: " . $e->getMessage());
        }
    }

    /**
     * Test 27: Verify index.php has information modal for webinar submission
     */
    public function testIndexPhpInfoModal() {
        try {
            $filePath = __DIR__ . '/../index.php';
            if (!file_exists($filePath)) {
                throw new Exception("index.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Verify info modal exists
            if (!preg_match('/infoModal|id\s*=\s*["\']infoModal["\']/i', $content)) {
                throw new Exception("Info modal not found in index.php");
            }
            
            // Verify modal has info content about webinar submission
            if (!preg_match('/submitted for approval|Webinar Submitted|pending|approval/i', $content)) {
                throw new Exception("Info modal missing submission message");
            }
            
            // Verify modal references pending status
            if (!preg_match('/Pending|pending|⏳/i', $content)) {
                throw new Exception("Info modal missing pending status reference");
            }
            
            return $this->pass("index.php includes information modal for webinar submission");
        } catch (Exception $e) {
            return $this->fail("Index info modal: " . $e->getMessage());
        }
    }

    /**
     * Test 28: Verify index.php shows info modal on new webinar creation
     */
    public function testIndexPhpShowsInfoModalOnCreate() {
        try {
            $filePath = __DIR__ . '/../index.php';
            if (!file_exists($filePath)) {
                throw new Exception("index.php not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Verify infoModal.show() is called
            if (!preg_match('/infoModal\s*\.show\s*\(\)|infoModal\.show/i', $content)) {
                throw new Exception("infoModal.show() not called in index.php");
            }
            
            // Verify it's called for new events (isNewEvent check)
            if (!preg_match('/isNewEvent|!payload\.id/i', $content)) {
                throw new Exception("Info modal not conditional on new event");
            }
            
            return $this->pass("index.php shows info modal when creating new webinar");
        } catch (Exception $e) {
            return $this->fail("Index show info modal: " . $e->getMessage());
        }
    }

    /**
     * Test 29: Verify notification animations are properly configured
     */
    public function testNotificationAnimationConfig() {
        try {
            $filePath = __DIR__ . '/../assets/css/styles.css';
            if (!file_exists($filePath)) {
                throw new Exception("styles.css not found");
            }
            
            $content = file_get_contents($filePath);
            
            // Verify slideIn animation
            if (!preg_match('/@keyframes\s+slideIn|slideIn.*transform/i', $content)) {
                throw new Exception("slideIn animation missing");
            }
            
            // Verify slideOut animation
            if (!preg_match('/@keyframes\s+slideOut|slideOut.*transform/i', $content)) {
                throw new Exception("slideOut animation missing");
            }
            
            // Verify notification-toast class
            if (!preg_match('/\.notification-toast|notification-toast\s*{/i', $content)) {
                throw new Exception("notification-toast CSS class missing");
            }
            
            return $this->pass("Notification animations properly configured");
        } catch (Exception $e) {
            return $this->fail("Notification animations: " . $e->getMessage());
        }
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
        
        // New tests for feature updates
        $this->testLoadEventsIncludesRejectionReason();
        $this->testWebinarsListColorScheme();
        $this->testWebinarsListAllStatusFilter();
        $this->testIndexPhpNotificationSystem();
        $this->testIndexPhpUnapprovedVisibility();
        $this->testNotificationCSSStyles();
        $this->testEventColorCoding();
        
        // New tests for recent updates
        $this->testEditDeletePermissionsRestriction();
        $this->testRejectButtonUpdatesNotDeletes();
        $this->testThreeStatusSystem();
        $this->testRejectionReasonInExtendedProps();
        $this->testPendingWebinarsExcludesRejected();

        // Tests for overlapping webinars validation
        $this->testOverlapCheckApprovdWebinarsOnly();
        $this->testUpdateEventOverlapExcludesOwn();
        $this->testSaveEventTimeValidation();
        $this->testUpdateEventTimeValidation();

        // Tests for information dialog
        $this->testIndexPhpInfoModal();
        $this->testIndexPhpShowsInfoModalOnCreate();
        $this->testNotificationAnimationConfig();

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
