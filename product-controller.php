<?php
/**
 * Product Controller
 * Handles all product-related operations
 */
class ProductController
{
    private $db;

    /**
     * Constructor - initializes database connection
     */
    public function __construct($database)
    {
        $this->db = $database;
    }

/**
     * Get all products with their type names
     * 
     * @return array List of all products
     */
    public function getAllProducts()
    {
        try {
            $query = "SELECT p.*, pt.name as type_name 
                     FROM products p
                     LEFT JOIN product_types pt ON p.type_id = pt.id
                     ORDER BY p.id DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch products: ' . $e->getMessage()];
        }
    }
 
  /**
     * Get product by ID
     * 
     * @param int $id Product ID
     * @return array Product data
     */
    public function getProductById($id)
    {
        try {
            $query = "SELECT p.*, pt.name as type_name 
                     FROM products p
                     LEFT JOIN product_types pt ON p.type_id = pt.id
                     WHERE p.id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                return ['error' => 'Product not found'];
            }
            
            return $product;
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch product: ' . $e->getMessage()];
        }
    }
    /**
     * Create new product
     * 
     * @param string $name Product name
     * @param int $typeId Product type ID
     * @param string $date Date (YYYY-MM-DD)
     * @param int $quantity Product quantity
     * @return array Result of operation
     */
    public function createProduct($name, $typeId, $date, $quantity)
    {
        try {
            // Validate inputs
            if (empty($name)) {
                return ['error' => 'Product name is required'];
            }
            
            if (!is_numeric($quantity) || $quantity < 0) {
                return ['error' => 'Quantity must be a positive number'];
            }
            
            // Validate date format
            $dateObj = DateTime::createFromFormat('Y-m-d', $date);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
                return ['error' => 'Invalid date format. Use YYYY-MM-DD'];
            }
            
            $query = "INSERT INTO products (name, type_id, date, quantity) 
                      VALUES (:name, :type_id, :date, :quantity)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Product created successfully',
                'id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ['error' => 'Failed to create product: ' . $e->getMessage()];
        }
    }

 /**
     * Update existing product
     * 
     * @param int $id Product ID
     * @param string $name Product name
     * @param int $typeId Product type ID
     * @param string $date Date (YYYY-MM-DD)
     * @param int $quantity Product quantity
     * @return array Result of operation
     */
    public function updateProduct($id, $name, $typeId, $date, $quantity)
    {
        try {
            // Validate inputs
            if (empty($name)) {
                return ['error' => 'Product name is required'];
            }
            
            if (!is_numeric($quantity) || $quantity < 0) {
                return ['error' => 'Quantity must be a positive number'];
            }
            
            // Validate date format
            $dateObj = DateTime::createFromFormat('Y-m-d', $date);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
                return ['error' => 'Invalid date format. Use YYYY-MM-DD'];
            }
            
            // Check if product exists
            $checkQuery = "SELECT id FROM products WHERE id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return ['error' => 'Product not found'];
            }
            
            $query = "UPDATE products 
                      SET name = :name, 
                          type_id = :type_id, 
                          date = :date, 
                          quantity = :quantity 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Product updated successfully',
                'rows_affected' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return ['error' => 'Failed to update product: ' . $e->getMessage()];
        }
    }

    /**
     * Delete product
     * 
     * @param int $id Product ID
     * @return array Result of operation
     */
    public function deleteProduct($id)
    {
        try {
            $query = "DELETE FROM products WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return ['error' => 'Product not found or already deleted'];
            }
            
            return [
                'success' => true,
                'message' => 'Product deleted successfully',
                'rows_affected' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return ['error' => 'Failed to delete product: ' . $e->getMessage()];
        }
    }  
}
?>