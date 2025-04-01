<?php
/**
 * Product Type Controller
 * Handles all product type operations
 */
class ProductTypeController
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
     * Get all product types
     * 
     * @return array List of all product types
     */
    public function getAllProductTypes()
    {
        try {
            $query = "SELECT * FROM product_types ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch product types: ' . $e->getMessage()];
        }
    }

    /**
     * Get product type by ID
     * 
     * @param int $id Product type ID
     * @return array Product type data
     */
    public function getProductTypeById($id)
    {
        try {
            $query = "SELECT * FROM product_types WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $type = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$type) {
                return ['error' => 'Product type not found'];
            }
            
            return $type;
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch product type: ' . $e->getMessage()];
        }
    }

    /**
     * Create new product type
     * 
     * @param string $name Product type name
     * @param string $description Product type description
     * @return array Result of operation
     */
    public function createProductType($name, $description = '')
    {
        try {
            // Validate inputs
            if (empty($name)) {
                return ['error' => 'Type name is required'];
            }
            
            $query = "INSERT INTO product_types (name, description) 
                      VALUES (:name, :description)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Product type created successfully',
                'id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ['error' => 'Failed to create product type: ' . $e->getMessage()];
        }
    }

    /**
     * Update existing product type
     * 
     * @param int $id Product type ID
     * @param string $name Product type name
     * @param string $description Product type description
     * @return array Result of operation
     */
    public function updateProductType($id, $name, $description = '')
    {
        try {
            // Validate inputs
            if (empty($name)) {
                return ['error' => 'Type name is required'];
            }
            
            // Check if product type exists
            $checkQuery = "SELECT id FROM product_types WHERE id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return ['error' => 'Product type not found'];
            }
            
            $query = "UPDATE product_types 
                      SET name = :name, 
                          description = :description 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Product type updated successfully',
                'rows_affected' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            return ['error' => 'Failed to update product type: ' . $e->getMessage()];
        }
    }

    /**
     * Delete product type
     * 
     * @param int $id Product type ID
     * @return array Result of operation
     */
    public function deleteProductType($id)
    {
        try {
            // Begin transaction to ensure data integrity
            $this->db->beginTransaction();
            
            // Check if there are products using this type
            $checkQuery = "SELECT COUNT(*) as count FROM products WHERE type_id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                // Option 1: Prevent deletion
                // $this->db->rollBack();
                // return ['error' => 'Cannot delete: This product type is used by ' . $result['count'] . ' product(s)'];
                
                // Option 2: Set type_id to NULL for associated products
                $updateQuery = "UPDATE products SET type_id = NULL WHERE type_id = :id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
                $updateStmt->execute();
            }
            
            // Delete the product type
            $query = "DELETE FROM product_types WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                return ['error' => 'Product type not found or already deleted'];
            }
            
            // Commit the transaction
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Product type deleted successfully',
                'rows_affected' => $stmt->rowCount(),
                'products_updated' => isset($result['count']) ? $result['count'] : 0
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Failed to delete product type: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get products by type ID
     * 
     * @param int $typeId Product type ID
     * @return array List of products of the specified type
     */
    public function getProductsByType($typeId)
    {
        try {
            $query = "SELECT * FROM products WHERE type_id = :type_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Failed to fetch products by type: ' . $e->getMessage()];
        }
    }
}
?>