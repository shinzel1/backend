<?php
require_once './db.php';

try {
    $stmt = $pdo->query("SELECT category FROM restaurants");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allCategories = [];

    foreach ($rows as $row) {
        $cats = json_decode($row['category'], true);
        if (is_array($cats)) {
            foreach ($cats as $cat) {
                $allCategories[] = trim($cat);
            }
        }
    }

    // Count duplicates
    $counts = array_count_values($allCategories);

    // Format output
    $categories = [];
    foreach ($counts as $cat => $total) {
        $categories[] = ["category" => $cat, "total" => $total];
    }

} catch (PDOException $e) {
    echo "err";
}
?>

<?php require_once 'navbar/navbar.php'; ?>
<div class="container">
    <?php foreach ($categories as $cat): ?>
        <?php print_r($cat["category"]); ?>
    <?php endforeach ?>

    <table>
        <tr>
            <th>Category</th>
            <th>Count</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?php print_r($cat["category"]); ?></td>
                <td><?php print_r($cat["total"]); ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</div>