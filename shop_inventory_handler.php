<?php
require_once 'C:/xampp/htdocs/demo_mediLatest6/scripts/connect.php';

class ShopInventoryHandler {
    private $db;
    public $shop_id;
    public $shop_name;
    public $products = [];
    public $categories = [];
    public $current_page = 1;
    public $total_pages = 1;
    public $items_per_page = 10;
    
    // Filter properties
    public $search_term = '';
    public $category_filter = '';
    public $price_min = null;
    public $price_max = null;
    public $stock_min = null;
    public $expiry_filter = '';

    public function __construct($db) {
        $this->db = $db;
        $this->shop_id = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : 0;
        $this->loadShopDetails();
        $this->loadFilters();
        $this->loadCategories();
        $this->loadProducts();
    }

    private function loadShopDetails() {
        $stmt = $this->db->prepare("SELECT shop_name FROM shopdetails WHERE id = ?");
        $stmt->execute([$this->shop_id]);
        $shop = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->shop_name = $shop['shop_name'] ?? 'Unknown Shop';
    }

    private function loadFilters() {
        $this->search_term = $_GET['search'] ?? '';
        $this->category_filter = $_GET['category'] ?? '';
        $this->price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : null;
        $this->price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : null;
        $this->stock_min = isset($_GET['stock_min']) ? (int)$_GET['stock_min'] : null;
        $this->expiry_filter = $_GET['expiry'] ?? '';
        $this->current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    }

    private function loadCategories() {
        $stmt = $this->db->prepare("SELECT DISTINCT category FROM products WHERE shop_id = ? AND category IS NOT NULL");
        $stmt->execute([$this->shop_id]);
        $this->categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function loadProducts() {
        // Base query with count
        $query = "SELECT SQL_CALC_FOUND_ROWS p.* FROM products p WHERE p.shop_id = ? AND p.is_unlisted = 0";
        $params = [$this->shop_id];

        // Apply filters
        if (!empty($this->search_term)) {
            $query .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
            $search_param = "%{$this->search_term}%";
            $params[] = $search_param;
            $params[] = $search_param;
        }

        if (!empty($this->category_filter)) {
            $query .= " AND p.category = ?";
            $params[] = $this->category_filter;
        }

        if ($this->price_min !== null) {
            $query .= " AND p.price >= ?";
            $params[] = $this->price_min;
        }

        if ($this->price_max !== null) {
            $query .= " AND p.price <= ?";
            $params[] = $this->price_max;
        }

        if ($this->stock_min !== null) {
            $query .= " AND p.quantity >= ?";
            $params[] = $this->stock_min;
        }

        if (!empty($this->expiry_filter)) {
            $today = date('Y-m-d');
            switch ($this->expiry_filter) {
                case 'expired':
                    $query .= " AND p.expiry_date < ?";
                    $params[] = $today;
                    break;
                case 'expiring_soon':
                    $query .= " AND p.expiry_date >= ? AND p.expiry_date <= DATE_ADD(?, INTERVAL 30 DAY)";
                    $params[] = $today;
                    $params[] = $today;
                    break;
                case 'valid':
                    $query .= " AND p.expiry_date > ?";
                    $params[] = $today;
                    break;
            }
        }

        // Add pagination
        $offset = ($this->current_page - 1) * $this->items_per_page;
        $query .= " ORDER BY p.product_name LIMIT ? OFFSET ?";
        $params[] = $this->items_per_page;
        $params[] = $offset;

        // Execute query
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $this->products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $total_stmt = $this->db->query("SELECT FOUND_ROWS()");
        $total_rows = $total_stmt->fetchColumn();
        $this->total_pages = max(1, ceil($total_rows / $this->items_per_page));
    }

    public function getPaginationLinks() {
        $links = [];
        $base_url = "?shop_id={$this->shop_id}";
        
        // Add search term
        if (!empty($this->search_term)) {
            $base_url .= "&search=" . urlencode($this->search_term);
        }
        
        // Add other filters
        $filters = [
            'category' => $this->category_filter,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'stock_min' => $this->stock_min,
            'expiry' => $this->expiry_filter
        ];
        
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $base_url .= "&$key=" . urlencode($value);
            }
        }
        
        // Previous page
        if ($this->current_page > 1) {
            $links['prev'] = $base_url . "&page=" . ($this->current_page - 1);
        }
        
        // Page numbers
        for ($i = 1; $i <= $this->total_pages; $i++) {
            $links['pages'][$i] = $base_url . "&page=$i";
        }
        
        // Next page
        if ($this->current_page < $this->total_pages) {
            $links['next'] = $base_url . "&page=" . ($this->current_page + 1);
        }
        
        return $links;
    }
}
?>