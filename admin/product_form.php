<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Product.php';

requireAdmin();

$productModel = new Product();
$id      = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = $id ? $productModel->getById($id) : null;

if ($id && !$product) {
    setFlash('error', 'That product no longer exists.');
    redirect('admin/products.php');
}

$franchises  = getAllCategories();
$collections = $productModel->getCollections();
$errors      = [];
$posted      = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors[] = 'That photo is larger than the server allows. Try a smaller image.';
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $edition = $_POST['edition'] ?? 'Regular';
        $posted = [
            'name'           => sanitizeInput($_POST['name'] ?? ''),
            'category_id'    => (int) ($_POST['category_id'] ?? 0),
            'edition'        => in_array($edition, editionOptions(), true) ? $edition : 'Regular',
            'price'          => is_numeric($_POST['price'] ?? '') ? round((float) $_POST['price'], 2) : 0.0,
            'stock_quantity' => max(0, (int) ($_POST['stock_quantity'] ?? 0)),
            'description'    => trim(strip_tags((string) ($_POST['description'] ?? ''))),
            'is_featured'    => $product && isset($_POST['is_featured']) ? 1 : 0,   // the toggle only exists on the edit screen
            'collection_ids' => array_map('intval', (array) ($_POST['collection_ids'] ?? [])),
        ];

        $validFranchiseIds = array_map('intval', array_column($franchises, 'category_id'));
        if ($posted['name'] === '' || strlen($posted['name']) > 255) {
            $errors[] = 'Please enter a name for the pop.';
        }
        if (!in_array($posted['category_id'], $validFranchiseIds, true)) {
            $errors[] = 'Please choose a franchise.';
        }
        if ($posted['price'] <= 0 || $posted['price'] > 99999999) {
            $errors[] = 'Please enter a price greater than zero.';
        }
        if (strlen($posted['description']) > 5000) {
            $errors[] = 'The description is too long (5000 characters at most).';
        }

        $upload = ['error' => null];
        if (empty($errors)) {
            $upload = saveProductImage($_FILES['photo'] ?? []);
            if (isset($upload['error'])) {
                $errors[] = $upload['error'];
            }
        }

        if (empty($errors)) {
            $posted['image_url'] = $upload['path'] ?? ($product['image_url'] ?? null);

            // Keep the existing URL slug if the name did not change; otherwise build a new unique one.
            $posted['slug'] = ($product && $product['name'] === $posted['name'])
                ? $product['slug']
                : $productModel->uniqueSlug(slugify($posted['name']), $product ? (int) $product['product_id'] : null);

            if ($product) {
                $ok = $productModel->update((int) $product['product_id'], $posted);
            } else {
                $ok = $productModel->create($posted) > 0;
            }

            if ($ok) {
                if ($product && isset($upload['path'])) {
                    deleteProductImage($product['image_url'] ?? null);   // the old photo was replaced
                }
                setFlash('success', $posted['name'] . ($product ? ' was updated.' : ' was added.'));
                redirect('admin/products.php');
            }
            if (isset($upload['path'])) {
                deleteProductImage($upload['path']);
            }
            $errors[] = 'The product could not be saved. Please check the values and try again.';
        }
    }
}

// Values shown in the form: the product being edited, overlaid with anything just posted.
$values = $product ?: [
    'name' => '', 'category_id' => '', 'edition' => 'Regular', 'price' => '', 'stock_quantity' => 0,
    'description' => '', 'is_featured' => 0, 'image_url' => '',
];
$selectedCollections = $product ? $productModel->getCollectionIdsFor((int) $product['product_id']) : [];
if ($posted !== null) {
    $values = array_merge($values, $posted);
    $selectedCollections = $posted['collection_ids'];
}
$currentImage = imgSrc($product['image_url'] ?? '');

$pageTitle   = ($product ? 'Edit' : 'Add') . ' product — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['Products', 'admin/products.php'], [$product ? 'Edit' : 'Add', null]];
$adminErrors = $errors;
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title"><?php echo $product ? 'Edit product' : 'Add product'; ?></h1>

<?php if (empty($franchises)): ?>
    <div class="admin-banner admin-banner-error" role="alert">
        <?php echo $bannerIcon['error']; ?>
        <p>There are no franchises yet. Add at least one row to the categories table (for example in phpMyAdmin) before adding products.</p>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card product-form" id="product-form" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

    <label class="photo-drop <?php echo $currentImage ? 'has-photo' : ''; ?>" for="photo" data-photo-drop>
        <?php if ($currentImage): ?><img class="photo-preview" src="<?php echo e($currentImage); ?>" alt="Current photo" data-photo-preview><?php else: ?><img class="photo-preview" alt="" hidden data-photo-preview><?php endif; ?>
        <span class="photo-prompt">
            <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M4 17l5-4.5 3.5 3L16 12l4.5 4.5"/></svg>
            <span data-photo-label><?php echo $product && $currentImage ? 'Current photo - Replace' : 'Upload photo'; ?></span>
        </span>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
    </label>

    <div class="form-row">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required maxlength="255" placeholder="e.g. Cosmic Hulk" value="<?php echo e($values['name']); ?>">
    </div>

    <div class="form-row form-row-split">
        <div>
            <label for="category_id">Franchise</label>
            <select id="category_id" name="category_id" required>
                <option value="">Choose...</option>
                <?php foreach ($franchises as $c): ?>
                    <option value="<?php echo (int) $c['category_id']; ?>" <?php echo (int) $values['category_id'] === (int) $c['category_id'] ? 'selected' : ''; ?>><?php echo e($c['category_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="edition">Edition</label>
            <select id="edition" name="edition">
                <?php foreach (editionOptions() as $opt): ?>
                    <option value="<?php echo e($opt); ?>" <?php echo ($values['edition'] ?? 'Regular') === $opt ? 'selected' : ''; ?>><?php echo e($opt); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row form-row-split">
        <div>
            <label for="price">Price (<?php echo e(SITE_CURRENCY_SYMBOL); ?>)</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" required placeholder="0.00" value="<?php echo e($values['price']); ?>">
        </div>
        <div>
            <label for="stock_quantity">Stock</label>
            <input type="number" id="stock_quantity" name="stock_quantity" min="0" step="1" required placeholder="0" value="<?php echo e($values['stock_quantity']); ?>">
        </div>
    </div>

    <fieldset class="form-row pills-field">
        <legend>Categories</legend>
        <div class="pills">
            <?php foreach ($collections as $col): ?>
                <label class="pill">
                    <input type="checkbox" name="collection_ids[]" value="<?php echo (int) $col['collection_id']; ?>" <?php echo in_array((int) $col['collection_id'], $selectedCollections, true) ? 'checked' : ''; ?>>
                    <span><?php echo e($col['collection_name']); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="form-row">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" maxlength="5000" placeholder="Add product details..."><?php echo e($values['description']); ?></textarea>
    </div>

    <?php if ($product): ?>
        <label class="switch-row" for="is_featured">
            <span>Show on home page</span>
            <input type="checkbox" class="switch" id="is_featured" name="is_featured" role="switch" <?php echo !empty($values['is_featured']) ? 'checked' : ''; ?>>
        </label>
    <?php endif; ?>
</form>

<div class="form-actions">
    <a class="btn btn-outline" href="<?php echo SITE_URL; ?>/admin/products.php">Cancel</a>
    <button type="submit" class="btn btn-dark" form="product-form"><?php echo $product ? 'Save changes' : 'Add product'; ?></button>
</div>
<?php if ($product): ?>
    <a class="btn btn-outline btn-block btn-lg delete-link" href="<?php echo SITE_URL; ?>/admin/product_delete.php?id=<?php echo (int) $product['product_id']; ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9.5 7V4.5h5V7M6.5 7l.8 12.5h9.4L17.5 7M10 11v5.5M14 11v5.5"/></svg>
        Delete this product
    </a>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
