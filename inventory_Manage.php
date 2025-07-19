<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'C:/xampp/htdocs/demo_mediLatest6/scripts/connect.php';

class InventoryManager {
    private $db;
    public $locationFilter;
    public $productFilter;
    public $searchTerm;
    public $result;
    public $districts;
    public $currentPage = 1;
    public $totalPages = 1;
    public $itemsPerPage = 6;

    public function __construct($db) {
        $this->db = $db;
        $this->getFilters();
        $this->getDistricts();
        $this->getShopInventory();
    }

    private function getFilters() {
        $this->locationFilter = $_GET['location'] ?? '';
        $this->productFilter = $_GET['product'] ?? '';
        $this->searchTerm = $_GET['search'] ?? '';
        $this->currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    }

    private function getDistricts() {
        $districtQuery = "SELECT DISTINCT district FROM shopdetails ORDER BY district";
        $this->districts = $this->db->query($districtQuery)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getShopInventory() {
        // Base query
        $query = "SELECT s.*, COUNT(p.id) as product_count 
                  FROM shopdetails s
                  LEFT JOIN products p ON s.id = p.shop_id";

        $whereConditions = [];
        $params = [];

        if (!empty($this->searchTerm)) {
            $whereConditions[] = "s.shop_name LIKE ?";
            $params[] = "%".$this->searchTerm."%";
        }

        if (!empty($this->locationFilter)) {
            $whereConditions[] = "s.district = ?";
            $params[] = $this->locationFilter;
        }

        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $query .= " GROUP BY s.id";

        if ($this->productFilter === 'with_products') {
            $query .= " HAVING product_count > 0";
        } elseif ($this->productFilter === 'without_products') {
            $query .= " HAVING product_count = 0";
        }

        // First get total count
        $countQuery = "SELECT COUNT(*) FROM ($query) AS total_shops";
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute($params);
        $totalShops = $stmt->fetchColumn();
        $this->totalPages = max(1, ceil($totalShops / $this->itemsPerPage));

        // Adjust current page if out of bounds
        $this->currentPage = min($this->currentPage, $this->totalPages);

        // Add pagination to main query
        $query .= " ORDER BY s.shop_name";
        $query .= " LIMIT ? OFFSET ?";
        
        // Calculate offset
        $offset = ($this->currentPage - 1) * $this->itemsPerPage;
      
        $stmt = $this->db->prepare($query);
        
    // Bind all parameters including LIMIT and OFFSET
    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param);
    }
    $stmt->bindValue($paramIndex++, $this->itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $this->result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}

// Create instance and process data
$inventoryManager = new InventoryManager($db);
?>