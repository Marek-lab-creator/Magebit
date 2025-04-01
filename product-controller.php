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

  
}
?>