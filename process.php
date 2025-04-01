<?php
/**
 * Process.php - Main entry point for handling all AJAX requests
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include controllers
require_once 'product-controller.php';
require_once 'product-type-controller.php';

// Database connection
function getDatabase() {
    $host = 'localhost';
    $dbname = 'product_inventory';
    $username = 'root';     // Change these credentials
    $password = '';         // for your environment

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        return ['error' => 'Database connection failed: ' . $e->getMessage()];
    }
}

// Send JSON response
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Get request action
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
}

// // Initialize database
// $db = getDatabase();
// if (isset($db['error'])) {
//     jsonResponse($db);
// }

// Initialize database
$db = getDatabase();
if (is_array($db) && isset($db['error'])) {
    jsonResponse($db);
}

// Initialize controllers
$productController = new ProductController($db);
$productTypeController = new ProductTypeController($db);

// Route the request based on action
switch ($action) {
    // Product actions
    case 'get_all_products':
        $products = $productController->getAllProducts();
        jsonResponse($products);
        break;

    case 'get_product':
        if (!isset($_GET['id'])) {
            jsonResponse(['error' => 'Product ID is required']);
        }
        $product = $productController->getProductById($_GET['id']);
        jsonResponse($product);
        break;

    case 'create_product':
        if (!isset($_POST['name']) || !isset($_POST['type_id']) || 
            !isset($_POST['date']) || !isset($_POST['quantity'])) {
            jsonResponse(['error' => 'Missing required fields']);
        }
        
        $result = $productController->createProduct(
            $_POST['name'],
            $_POST['type_id'],
            $_POST['date'],
            $_POST['quantity']
        );
        jsonResponse($result);
        break;

    case 'update_product':
        if (!isset($_POST['id']) || !isset($_POST['name']) || 
            !isset($_POST['type_id']) || !isset($_POST['date']) || 
            !isset($_POST['quantity'])) {
            jsonResponse(['error' => 'Missing required fields']);
        }
        
        $result = $productController->updateProduct(
            $_POST['id'],
            $_POST['name'],
            $_POST['type_id'],
            $_POST['date'],
            $_POST['quantity']
        );
        jsonResponse($result);
        break;

    case 'delete_product':
        if (!isset($_GET['id'])) {
            jsonResponse(['error' => 'Product ID is required']);
        }
        $result = $productController->deleteProduct($_GET['id']);
        jsonResponse($result);
        break;

    // Product Type actions
    case 'get_all_product_types':
        $types = $productTypeController->getAllProductTypes();
        jsonResponse($types);
        break;

    case 'get_product_type':
        if (!isset($_GET['id'])) {
            jsonResponse(['error' => 'Product Type ID is required']);
        }
        $type = $productTypeController->getProductTypeById($_GET['id']);
        jsonResponse($type);
        break;

    case 'create_product_type':
        if (!isset($_POST['name'])) {
            jsonResponse(['error' => 'Name is required']);
        }
        
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $result = $productTypeController->createProductType($_POST['name'], $description);
        jsonResponse($result);
        break;

    case 'update_product_type':
        if (!isset($_POST['id']) || !isset($_POST['name'])) {
            jsonResponse(['error' => 'Missing required fields']);
        }
        
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $result = $productTypeController->updateProductType(
            $_POST['id'],
            $_POST['name'],
            $description
        );
        jsonResponse($result);
        break;

    case 'delete_product_type':
        if (!isset($_GET['id'])) {
            jsonResponse(['error' => 'Product Type ID is required']);
        }
        $result = $productTypeController->deleteProductType($_GET['id']);
        jsonResponse($result);
        break;

    case 'get_products_by_type':
        if (!isset($_GET['type_id'])) {
            jsonResponse(['error' => 'Type ID is required']);
        }
        $products = $productTypeController->getProductsByType($_GET['type_id']);
        jsonResponse($products);
        break;

    default:
        jsonResponse(['error' => 'Invalid action']);
}
?>