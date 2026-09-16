<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/includes/auth.php";

requireAdmin();


// =====================================================
// TOTAL PRODUCTS
// =====================================================

$totalProductsResult = $db->from("products")
                          ->select("COUNT(*) AS total")
                          ->one();

$totalProducts = (int)($totalProductsResult["total"] ?? 0);


// =====================================================
// PRODUCTS + CURRENT STOCK
// =====================================================

$products = $db->from("products")
               ->select("*")
               ->many();

$totalStockQuantity = 0;
$lowStockProducts = 0;

foreach ($products as $product) {

    $stockIn = $db->from("stock_movements")
                  ->where("product_id", $product["id"])
                  ->where("type", "IN")
                  ->select("SUM(quantity) AS total_in")
                  ->one();

    $stockOut = $db->from("stock_movements")
                   ->where("product_id", $product["id"])
                   ->where("type", "OUT")
                   ->select("SUM(quantity) AS total_out")
                   ->one();

    $totalIn = (int)($stockIn["total_in"] ?? 0);
    $totalOut = (int)($stockOut["total_out"] ?? 0);

    $currentStock = $totalIn - $totalOut;

    $totalStockQuantity += $currentStock;

    if ($currentStock <= (int)$product["minimum_stock"]) {
        $lowStockProducts++;
    }
}


// =====================================================
// TOTAL CUSTOMERS
// =====================================================

$totalCustomersResult = $db->from("customers")
                            ->select("COUNT(*) AS total")
                            ->one();

$totalCustomers = (int)($totalCustomersResult["total"] ?? 0);


// =====================================================
// TODAY'S STOCK IN
// =====================================================

$today = date("Y-m-d");

$todayStockInResult = $db->from("stock_movements")
                         ->where("type", "IN")
                         ->where("date", $today)
                         ->select("SUM(quantity) AS total")
                         ->one();

$todayStockIn = (int)($todayStockInResult["total"] ?? 0);


// =====================================================
// TODAY'S STOCK OUT
// =====================================================

$todayStockOutResult = $db->from("stock_movements")
                          ->where("type", "OUT")
                          ->where("date", $today)
                          ->select("SUM(quantity) AS total")
                          ->one();

$todayStockOut = (int)($todayStockOutResult["total"] ?? 0);


// =====================================================
// TOTAL ORDERS
// =====================================================

$totalOrdersResult = $db->from("orders")
                        ->select("COUNT(*) AS total")
                        ->one();

$totalOrders = (int)($totalOrdersResult["total"] ?? 0);


// =====================================================
// TODAY'S ORDERS
// =====================================================

$todayOrdersResult = $db->from("orders")
                        ->where("order_date", $today)
                        ->select("COUNT(*) AS total")
                        ->one();

$todayOrders = (int)($todayOrdersResult["total"] ?? 0);


// =====================================================
// RECENT STOCK MOVEMENTS
// =====================================================

$recentMovements = $db->from("stock_movements")
                       ->select("*")
                       ->limit(5)
                       ->orderBy("id", "DESC")
                       ->many();


// =====================================================
// RECENT CUSTOMER ORDERS
// =====================================================

$recentOrders = $db->from("orders")
                   ->select("*")
                   ->limit(5)
                   ->orderBy("id", "DESC")
                   ->many();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Inventory System</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>


<body>


<div class="dashboard-container">


    <!-- =================================================
         SIDEBAR
         ================================================= -->

    <aside class="sidebar">

        <div class="sidebar-logo">
            Inventory System
        </div>


        <ul class="sidebar-menu">


            <li>
                <a href="index.php" class="active">
                    Dashboard
                </a>
            </li>


            <li>
                <a href="admin/products.php">
                    Products
                </a>
            </li>


            <li>
                <a href="admin/categories.php">
                    Categories
                </a>
            </li>


            <li>
                <a href="admin/customers.php">
                    Customers
                </a>
            </li>


            <li>
                <a href="admin/stock.php">
                    Current Stock
                </a>
            </li>


            <li>
                <a href="admin/stock_in.php">
                    Stock In
                </a>
            </li>


            <li>
                <a href="admin/stock_out.php">
                    Stock Out
                </a>
            </li>


            <li>
                <a href="admin/reports.php">
                    Reports
                </a>
            </li>


        </ul>


    </aside>



    <!-- =================================================
         MAIN CONTENT
         ================================================= -->

    <main class="main-content">


        <!-- =================================================
             TOPBAR
             ================================================= -->

        <header class="topbar">


            <h1>
                Dashboard
            </h1>


            <div class="user-info">

                <span class="user-name">
                    <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                </span>


                <a href="logout.php" class="logout-btn">
                    Logout
                </a>

            </div>


        </header>



        <!-- =================================================
             PAGE CONTENT
             ================================================= -->

        <section class="page-content">


            <!-- PAGE HEADER -->

            <div class="page-header">

                <div>

                    <h2>
                        Admin Overview
                    </h2>

                    <p>
                        Monitor inventory, customers, orders and stock activity
                    </p>

                </div>

            </div>



            <!-- =================================================
                 MAIN DASHBOARD CARDS
                 ================================================= -->

            <div class="dashboard-cards">


                <!-- TOTAL PRODUCTS -->

                <div class="dashboard-card">

                    <h3>
                        Total Products
                    </h3>

                    <div class="value">
                        <?php echo $totalProducts; ?>
                    </div>

                    <div class="description">
                        Products in inventory
                    </div>

                </div>



                <!-- TOTAL STOCK -->

                <div class="dashboard-card">

                    <h3>
                        Total Stock Quantity
                    </h3>

                    <div class="value">
                        <?php echo $totalStockQuantity; ?>
                    </div>

                    <div class="description">
                        Currently available
                    </div>

                </div>



                <!-- LOW STOCK -->

                <div class="dashboard-card">

                    <h3>
                        Low Stock Products
                    </h3>

                    <div class="value">
                        <?php echo $lowStockProducts; ?>
                    </div>

                    <div class="description">
                        Products requiring attention
                    </div>

                </div>



                <!-- CUSTOMERS -->

                <div class="dashboard-card">

                    <h3>
                        Total Customers
                    </h3>

                    <div class="value">
                        <?php echo $totalCustomers; ?>
                    </div>

                    <div class="description">
                        Registered customers
                    </div>

                </div>


            </div>



            <!-- =================================================
                 TODAY'S ACTIVITY
                 ================================================= -->

            <div class="dashboard-cards">


                <!-- TODAY STOCK IN -->

                <div class="dashboard-card">

                    <h3>
                        Today's Stock In
                    </h3>

                    <div class="value">
                        <?php echo $todayStockIn; ?>
                    </div>

                    <div class="description">
                        Quantity received today
                    </div>

                </div>



                <!-- TODAY STOCK OUT -->

                <div class="dashboard-card">

                    <h3>
                        Today's Stock Out
                    </h3>

                    <div class="value">
                        <?php echo $todayStockOut; ?>
                    </div>

                    <div class="description">
                        Quantity issued today
                    </div>

                </div>



                <!-- TOTAL ORDERS -->

                <div class="dashboard-card">

                    <h3>
                        Total Orders
                    </h3>

                    <div class="value">
                        <?php echo $totalOrders; ?>
                    </div>

                    <div class="description">
                        Customer orders
                    </div>

                </div>



                <!-- TODAY ORDERS -->

                <div class="dashboard-card">

                    <h3>
                        Today's Orders
                    </h3>

                    <div class="value">
                        <?php echo $todayOrders; ?>
                    </div>

                    <div class="description">
                        Orders placed today
                    </div>

                </div>


            </div>



            <!-- =================================================
                 RECENT STOCK MOVEMENTS
                 ================================================= -->

            <div class="content-card">


                <div class="page-header">

                    <div>

                        <h2>
                            Recent Stock Movements
                        </h2>

                        <p>
                            Latest inventory activity
                        </p>

                    </div>


                    <a href="admin/stock.php" class="btn btn-primary">
                        View Stock
                    </a>

                </div>



                <div class="table-container">


                    <table class="data-table">


                        <thead>

                            <tr>

                                <th>
                                    Product
                                </th>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Quantity
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Remarks
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (!empty($recentMovements)): ?>


                            <?php foreach ($recentMovements as $movement): ?>


                                <?php

                                $product = $db->from("products")
                                              ->where("id", $movement["product_id"])
                                              ->select("*")
                                              ->one();

                                ?>


                                <tr>


                                    <td>

                                        <?php if ($product): ?>

                                            <strong>
                                                <?php echo htmlspecialchars($product["name"]); ?>
                                            </strong>

                                            <br>

                                            <small>
                                                SKU:
                                                <?php echo htmlspecialchars($product["sku"]); ?>
                                            </small>

                                        <?php else: ?>

                                            Product #<?php echo (int)$movement["product_id"]; ?>

                                        <?php endif; ?>

                                    </td>



                                    <td>


                                        <?php if ($movement["type"] === "IN"): ?>

                                            <span class="status status-in">
                                                Stock In
                                            </span>

                                        <?php else: ?>

                                            <span class="status status-out">
                                                Stock Out
                                            </span>

                                        <?php endif; ?>


                                    </td>



                                    <td>

                                        <?php echo (int)$movement["quantity"]; ?>

                                    </td>



                                    <td>

                                        <?php echo htmlspecialchars($movement["date"]); ?>

                                    </td>



                                    <td>

                                        <?php echo htmlspecialchars($movement["remarks"] ?? ""); ?>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <tr>

                                <td colspan="5" class="empty-state">

                                    No stock movements found.

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </div>



            <!-- =================================================
                 RECENT CUSTOMER ORDERS
                 ================================================= -->

            <div class="content-card">


                <div class="page-header">


                    <div>

                        <h2>
                            Recent Customer Orders
                        </h2>

                        <p>
                            Latest orders placed by customers
                        </p>

                    </div>


                    <a href="manager/orders.php" class="btn btn-primary">
                        View Orders
                    </a>


                </div>



                <div class="table-container">


                    <table class="data-table">


                        <thead>

                            <tr>

                                <th>
                                    Order ID
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Product
                                </th>

                                <th>
                                    Quantity
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (!empty($recentOrders)): ?>


                            <?php foreach ($recentOrders as $order): ?>


                                <?php

                                $customer = $db->from("customers")
                                               ->where("id", $order["customer_id"])
                                               ->select("*")
                                               ->one();


                                $product = $db->from("products")
                                              ->where("id", $order["product_id"])
                                              ->select("*")
                                              ->one();

                                ?>


                                <tr>


                                    <td>

                                        #
                                        <?php echo (int)$order["id"]; ?>

                                    </td>



                                    <td>

                                        <?php echo htmlspecialchars(
                                            $customer["name"] ?? "Unknown"
                                        ); ?>

                                    </td>



                                    <td>

                                        <?php echo htmlspecialchars(
                                            $product["name"] ?? "Unknown"
                                        ); ?>

                                    </td>



                                    <td>

                                        <?php echo (int)$order["quantity"]; ?>

                                    </td>



                                    <td>

                                        <?php echo htmlspecialchars(
                                            $order["order_date"]
                                        ); ?>

                                    </td>



                                    <td>


                                        <?php if ($order["status"] === "completed"): ?>

                                            <span class="status status-in">
                                                Completed
                                            </span>

                                        <?php else: ?>

                                            <span class="status status-out">
                                                Cancelled
                                            </span>

                                        <?php endif; ?>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <tr>

                                <td colspan="6" class="empty-state">

                                    No customer orders found.

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </div>



            <!-- =================================================
                 ADMIN QUICK ACTIONS
                 ================================================= -->

            <div class="content-card">


                <div class="page-header">


                    <div>

                        <h2>
                            Quick Actions
                        </h2>

                        <p>
                            Frequently used inventory functions
                        </p>

                    </div>


                </div>



                <div class="button-group">


                    <a href="admin/product_add.php" class="btn btn-primary">
                        Add Product
                    </a>


                    <a href="admin/category_add.php" class="btn btn-primary">
                        Add Category
                    </a>


                    <a href="admin/stock_in.php" class="btn btn-success">
                        Stock In
                    </a>


                    <a href="admin/stock_out.php" class="btn btn-danger">
                        Stock Out
                    </a>


                    <a href="admin/customers.php" class="btn btn-secondary">
                        Customers
                    </a>


                    <a href="admin/reports.php" class="btn btn-secondary">
                        Reports
                    </a>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>