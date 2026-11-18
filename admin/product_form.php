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
$fieldErrors = [];
$posted      = null;
$franchiseChoice = '';
$newFranchiseValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors[] = 'That photo is larger than the server allows. Try a smaller image.';
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $edition = $_POST['edition'] ?? 'Regular';
        $franchiseChoice = trim((string) ($_POST['category_id'] ?? ''));
        $newFranchiseValue = sanitizeInput($_POST['new_franchise'] ?? '');
        $posted = [
            'name'           => sanitizeInput($_POST['name'] ?? ''),
            'category_id'    => ctype_digit($franchiseChoice) ? (int) $franchiseChoice : 0,
            'edition'        => in_array($edition, editionOptions(), true) ? $edition : 'Regular',
            'price'          => is_numeric($_POST['price'] ?? '') ? round((float) $_POST['price'], 2) : 0.0,
            'stock_quantity' => max(0, (int) ($_POST['stock_quantity'] ?? 0)),
            'description'    => trim(strip_tags((string) ($_POST['description'] ?? ''))),
            'is_featured'    => $product && isset($_POST['is_featured']) ? 1 : 0,   // the toggle only exists on the edit screen
            'collection_ids' => array_map('intval', (array) ($_POST['collection_ids'] ?? [])),
        ];

        // A typed franchise name wins over the dropdown: it is matched to an existing
        // franchise or added to the categories table, then used for this product.
        if ($franchiseChoice === 'new' || $newFranchiseValue !== '') {
            $newFranchiseLength = function_exists('mb_strlen') ? mb_strlen($newFranchiseValue, 'UTF-8') : strlen($newFranchiseValue);
            if ($newFranchiseValue === '') {
                $message = 'Please enter a name for the new franchise.';
                $errors[] = $message;
                $fieldErrors['new_franchise'] = $message;
            } elseif ($newFranchiseLength > 50) {
                $message = 'The franchise name is too long (50 characters at most).';
                $errors[] = $message;
                $fieldErrors['new_franchise'] = $message;
            } else {
                try {
                    $franchiseDb = Database::getInstance()->getConnection();
                    $findFranchise = $franchiseDb->prepare('SELECT category_id FROM categories WHERE category_name = ? LIMIT 1');
                    $findFranchise->execute([$newFranchiseValue]);
                    $newFranchiseId = (int) $findFranchise->fetchColumn();

                    if ($newFranchiseId === 0) {
                        $baseSlug  = slugify($newFranchiseValue);
                        $slug      = $baseSlug;
                        $suffix    = 2;
                        $slugTaken = $franchiseDb->prepare('SELECT 1 FROM categories WHERE slug = ? LIMIT 1');
                        while (true) {
                            $slugTaken->execute([$slug]);
                            if (!$slugTaken->fetchColumn()) {
                                break;
                            }
                            $slug = $baseSlug . '-' . $suffix++;
                        }

                        $insertFranchise = $franchiseDb->prepare("INSERT INTO categories (category_name, slug, description) VALUES (?, ?, '')");
                        $insertFranchise->execute([$newFranchiseValue, $slug]);
                        $newFranchiseId = (int) $franchiseDb->lastInsertId();
                    }

                    $posted['category_id'] = $newFranchiseId;
                    $franchiseChoice = (string) $newFranchiseId;
                    $franchises = getAllCategories();   // keep the dropdown in alphabetical order
                } catch (PDOException $e) {
                    $message = 'The new franchise could not be added. Please try again.';
                    $errors[] = $message;
                    $fieldErrors['new_franchise'] = $message;
                }
            }
        }

        $validFranchiseIds = array_map('intval', array_column($franchises, 'category_id'));
        if ($posted['name'] === '' || strlen($posted['name']) > 255) {
            $errors[] = 'Please enter a name for the pop.';
            $fieldErrors['name'] = 'Please enter a name for the pop.';
        }
        if (!in_array($posted['category_id'], $validFranchiseIds, true) && !isset($fieldErrors['new_franchise'])) {
            $errors[] = 'Please choose a franchise, or type a new one below.';
            $fieldErrors['category_id'] = 'Please choose a franchise, or type a new one below.';
        }
        if ($posted['price'] <= 0 || $posted['price'] > 99999999) {
            $errors[] = 'Please enter a price greater than zero.';
            $fieldErrors['price'] = 'Please enter a price greater than zero.';
        }
        if (strlen($posted['description']) > 5000) {
            $errors[] = 'The description is too long (5000 characters at most).';
            $fieldErrors['description'] = 'The description is too long (5000 characters at most).';
        }

        $upload = ['error' => null];
        if (empty($errors)) {
            $upload = saveProductImage($_FILES['photo'] ?? []);
            if (isset($upload['error'])) {
                $errors[] = $upload['error'];
                $fieldErrors['photo'] = $upload['error'];
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
$newFranchiseOpen = $franchiseChoice === 'new' || $newFranchiseValue !== '';

$pageTitle   = ($product ? 'Edit' : 'Add') . ' product — ' . SITE_NAME . ' Admin';
$adminCrumbs = [['Products', 'admin/products.php'], [$product ? 'Edit' : 'Add', null]];
$adminErrors = $errors;
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 class="admin-title"><?php echo $product ? 'Edit product' : 'Add product'; ?></h1>

<?php if (empty($franchises)): ?>
    <div class="admin-banner admin-banner-error" role="alert">
        <?php echo $bannerIcon['error']; ?>
        <p>There are no franchises yet. Open &ldquo;Add a new franchise&rdquo; below and type the first one.</p>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card product-form" id="product-form" action="" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

    <?php $err = $fieldErrors['photo'] ?? null; ?>
    <label class="photo-drop <?php echo $currentImage ? 'has-photo' : ''; ?><?php echo $err ? ' is-invalid' : ''; ?>" for="photo" data-photo-drop>
        <?php if ($currentImage): ?><img class="photo-preview" src="<?php echo e($currentImage); ?>" alt="Current photo" data-photo-preview><?php else: ?><img class="photo-preview" alt="" hidden data-photo-preview><?php endif; ?>
        <span class="photo-prompt">
            <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M4 17l5-4.5 3.5 3L16 12l4.5 4.5"/></svg>
            <span data-photo-label><?php echo $product && $currentImage ? 'Current photo - Replace' : 'Upload photo'; ?></span>
        </span>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp"<?php echo $err ? ' aria-invalid="true" aria-describedby="photo-error"' : ''; ?>>
    </label>
    <?php if ($err): ?><p class="field-error" id="photo-error"><?php echo e($err); ?></p><?php endif; ?>

    <div class="form-row">
        <label for="name">Name</label>
        <?php $err = $fieldErrors['name'] ?? null; ?>
        <input type="text" id="name" name="name" required maxlength="255" placeholder="e.g. Cosmic Hulk" value="<?php echo e($values['name']); ?>"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="name-error"' : ''; ?>>
        <?php if ($err): ?><p class="field-error" id="name-error"><?php echo e($err); ?></p><?php endif; ?>
    </div>

    <div class="form-row form-row-split">
        <div>
            <label for="category_id">Franchise</label>
            <?php $err = $fieldErrors['category_id'] ?? null; ?>
            <select id="category_id" name="category_id" required<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="category_id-error"' : ''; ?>>
                <option value="">Choose...</option>
                <?php foreach ($franchises as $c): ?>
                    <option value="<?php echo (int) $c['category_id']; ?>" <?php echo $franchiseChoice !== 'new' && (int) $values['category_id'] === (int) $c['category_id'] ? 'selected' : ''; ?>><?php echo e($c['category_name']); ?></option>
                <?php endforeach; ?>
                <option value="new" <?php echo $franchiseChoice === 'new' ? 'selected' : ''; ?>>+ New franchise</option>
            </select>
            <?php if ($err): ?><p class="field-error" id="category_id-error"><?php echo e($err); ?></p><?php endif; ?>
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

    <details class="new-franchise" id="new-franchise-field" <?php echo $newFranchiseOpen ? 'open' : ''; ?>>
        <summary>Add a new franchise</summary>
        <div class="form-row">
            <label for="new_franchise">New franchise name</label>
            <?php
                $err = $fieldErrors['new_franchise'] ?? null;
                $describedBy = 'new_franchise-hint' . ($err ? ' new_franchise-error' : '');
            ?>
            <input type="text" id="new_franchise" name="new_franchise" maxlength="50" placeholder="e.g. One Piece" aria-describedby="<?php echo e($describedBy); ?>" value="<?php echo e($newFranchiseValue); ?>"<?php echo $err ? ' class="is-invalid" aria-invalid="true"' : ''; ?>>
            <p class="field-hint" id="new_franchise-hint">This adds the franchise to the list and uses it for this pop. If the name already exists, the existing franchise is used.</p>
            <?php if ($err): ?><p class="field-error" id="new_franchise-error"><?php echo e($err); ?></p><?php endif; ?>
        </div>
    </details>

    <div class="form-row form-row-split">
        <div>
            <label for="price">Price (<?php echo e(SITE_CURRENCY_SYMBOL); ?>)</label>
            <?php $err = $fieldErrors['price'] ?? null; ?>
            <input type="number" id="price" name="price" step="0.01" min="0.01" required placeholder="0.00" value="<?php echo e($values['price']); ?>"<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="price-error"' : ''; ?>>
            <?php if ($err): ?><p class="field-error" id="price-error"><?php echo e($err); ?></p><?php endif; ?>
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
        <?php $err = $fieldErrors['description'] ?? null; ?>
        <textarea id="description" name="description" rows="5" maxlength="5000" placeholder="Add product details..."<?php echo $err ? ' class="is-invalid" aria-invalid="true" aria-describedby="description-error"' : ''; ?>><?php echo e($values['description']); ?></textarea>
        <?php if ($err): ?><p class="field-error" id="description-error"><?php echo e($err); ?></p><?php endif; ?>
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
