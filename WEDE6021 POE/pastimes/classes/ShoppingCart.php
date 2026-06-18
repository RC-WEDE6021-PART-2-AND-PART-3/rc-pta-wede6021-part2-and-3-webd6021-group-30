<?php
/**
 * ShoppingCart.php — Object-Oriented Shopping Cart for Pastimes e-Store
 *
 * Student:  Vukosi Rikhotso            Student No: ST10439408
 * Partner:  Theo Golele               Student No: ST10439863
 * Group:    Code Couture (Group 02)
 * Institution: IIE Rosebank College, Pretoria
 * Module:   WEB DEVELOPMENT (INTERMEDIATE) — WEDE6021
 * Lecturer: [Lecturer Name]
 *
 * Declaration: This class is our own original work except where
 * explicitly referenced in inline comments.
 *
 * Purpose:
 *   Encapsulates all shopping-cart state and operations.
 *   Cart data is stored in PHP $_SESSION so it persists across
 *   page-loads for the lifetime of the browser session.
 */

class ShoppingCart
{
    // ---------------------------------------------------------------
    // Private Properties
    // ---------------------------------------------------------------

    /** @var array<int,array> Associative array keyed by clothes_id */
    private array $items;

    /** @var int|null The authenticated user's ID, or null if not signed in */
    private ?int $userId;

    // ---------------------------------------------------------------
    // Constructor
    // ---------------------------------------------------------------

    /**
     * __construct
     * Starts (or resumes) the PHP session and loads the cart from it.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->items  = $_SESSION['cart']    ?? [];
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    // ---------------------------------------------------------------
    // Public Member Functions (required by POE spec)
    // ---------------------------------------------------------------

    /**
     * AddItem
     * Adds an item to the cart.  If the same clothes_id already exists,
     * the quantity is incremented rather than creating a duplicate entry.
     *
     * @param array $item  Row from tblClothes: must include clothes_id,
     *                     brand, item_name, sell_price, image_file, size.
     */
    public function AddItem(array $item): void
    {
        $id = (int)$item['clothes_id'];

        if (isset($this->items[$id])) {
            // Item already in cart — just increase the quantity
            $this->items[$id]['qty']++;
        } else {
            // New item — store full detail snapshot so display never
            // needs to re-query the database for cart contents
            $this->items[$id] = [
                'clothes_id' => $id,
                'brand'      => (string)($item['brand']      ?? ''),
                'item_name'  => (string)($item['item_name']  ?? ''),
                'sell_price' => (float) ($item['sell_price'] ?? 0),
                'image_file' => (string)($item['image_file'] ?? ''),
                'size'       => (string)($item['size']       ?? ''),
                'qty'        => 1,
            ];
        }
        $this->SaveToSession();
    }

    /**
     * RemoveItem
     * Removes an item from the cart entirely.
     *
     * @param int $clothesId  The clothes_id of the item to remove.
     */
    public function RemoveItem(int $clothesId): void
    {
        unset($this->items[$clothesId]);
        $this->SaveToSession();
    }

    /**
     * UpdateQuantity
     * Sets a specific quantity for an existing cart item.
     * Removes the item when quantity is set to zero or below.
     *
     * @param int $clothesId
     * @param int $qty
     */
    public function UpdateQuantity(int $clothesId, int $qty): void
    {
        if ($qty < 1) {
            $this->RemoveItem($clothesId);
        } elseif (isset($this->items[$clothesId])) {
            $this->items[$clothesId]['qty'] = $qty;
            $this->SaveToSession();
        }
    }

    /**
     * EmptyCart
     * Clears all items from the cart.
     */
    public function EmptyCart(): void
    {
        $this->items = [];
        $this->SaveToSession();
    }

    /**
     * Login
     * Sets the authenticated user's ID so Checkout can authorise the order.
     * Also writes it to the session to keep state consistent.
     *
     * @param int $userId
     */
    public function Login(int $userId): void
    {
        $this->userId            = $userId;
        $_SESSION['user_id']     = $userId;
    }

    /**
     * ProcessInput
     * Handles POST form actions submitted by the cart view (cart.php).
     * Supported $post['action'] values:
     *   - 'update' : requires 'id' (int) and 'qty' (int)
     *   - 'remove' : requires 'id' (int)
     *   - 'clear'  : empties entire cart
     *
     * @param array $post  Typically $_POST
     */
    public function ProcessInput(array $post): void
    {
        $action = $post['action'] ?? '';
        $id     = (int)($post['id'] ?? 0);

        switch ($action) {
            case 'update':
                $qty = (int)($post['qty'][$id] ?? $post['qty'] ?? 1);
                $this->UpdateQuantity($id, $qty);
                break;

            case 'remove':
                $this->RemoveItem($id);
                break;

            case 'clear':
                $this->EmptyCart();
                break;

            default:
                // Unknown action — no-op; avoids crashing on stale form data
                break;
        }
    }

    /**
     * Checkout
     * Persists the current cart as an order in the database.
     *
     * Steps performed inside a single transaction:
     *   1. Validates that a user is logged in and the cart is not empty.
     *   2. Generates a unique order reference (format: PP-XXXXXXXX).
     *   3. Inserts a row into tblOrder.
     *   4. Inserts one row per cart line into tblOrderItem.
     *   5. Decrements tblClothes.stock_qty for each line item.
     *   6. Commits and empties the cart on success; rolls back on failure.
     *
     * @param mysqli $db  An open MySQLi connection to ClothingStore.
     * @return array{success: bool, ref: string, sessionId: string, error: string}
     */
    public function Checkout(mysqli $db, string $payMethod = 'cod'): array
    {
        // Guard: must be logged in
        if (!$this->userId) {
            return [
                'success'   => false,
                'ref'       => '',
                'sessionId' => session_id(),
                'error'     => 'You must be signed in to complete your order.',
            ];
        }

        // Guard: cart must not be empty
        if (empty($this->items)) {
            return [
                'success'   => false,
                'ref'       => '',
                'sessionId' => session_id(),
                'error'     => 'Your cart is empty.',
            ];
        }

        // Unique, human-readable order reference
        $orderRef  = 'PP-' . strtoupper(substr(md5($this->userId . microtime()), 0, 8));
        $total     = $this->GetTotal();
        $sessionId = session_id();

        // Fetch shipping address stored on the user's profile
        $st = $db->prepare('SELECT address FROM tblUser WHERE user_id = ?');
        $st->bind_param('i', $this->userId);
        $st->execute();
        $row      = $st->get_result()->fetch_assoc();
        $st->close();
        $shipping = $row['address'] ?? '';

        // Begin atomic transaction
        $db->begin_transaction();
        $ok    = true;
        $dbErr = '';

        // Step 1: Insert order header into tblOrder
        $ins = $db->prepare(
            'INSERT INTO tblOrder (user_id, order_ref, total_price, shipping_address, session_id, payment_method)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        if (!$ins) {
            $ok = false; $dbErr = 'prepare tblOrder: ' . $db->error;
        } else {
            $ins->bind_param('isdsss', $this->userId, $orderRef, $total, $shipping, $sessionId, $payMethod);
            if (!$ins->execute()) {
                $ok = false; $dbErr = 'insert tblOrder: ' . $ins->error;
            }
            $orderId = (int)$db->insert_id;
            $ins->close();
        }

        // Step 2: Insert order lines and reduce stock quantities
        if ($ok) {
            foreach ($this->items as $item) {
                $cid       = (int)  $item['clothes_id'];
                $qty       = (int)  $item['qty'];
                $unitPrice = (float)$item['sell_price'];

                // Insert order line into tblOrderItem
                $ins2 = $db->prepare(
                    'INSERT INTO tblOrderItem (order_id, clothes_id, quantity, unit_price)
                     VALUES (?, ?, ?, ?)'
                );
                if (!$ins2) {
                    $ok = false; $dbErr = 'prepare tblOrderItem: ' . $db->error; break;
                }
                $ins2->bind_param('iiid', $orderId, $cid, $qty, $unitPrice);
                if (!$ins2->execute()) {
                    $ok = false; $dbErr = 'insert tblOrderItem: ' . $ins2->error;
                    $ins2->close(); break;
                }
                $ins2->close();

                // Decrement stock — floor at 0 to avoid negative quantities
                $upd = $db->prepare(
                    'UPDATE tblClothes
                     SET stock_qty = GREATEST(stock_qty - ?, 0)
                     WHERE clothes_id = ?'
                );
                if ($upd) {
                    $upd->bind_param('ii', $qty, $cid);
                    $upd->execute();
                    $upd->close();
                }
            }
        }

        // Step 3: Commit or rollback
        if ($ok) {
            $db->commit();
            $this->EmptyCart();  // Cart must be empty after successful checkout
            return ['success' => true, 'ref' => $orderRef, 'sessionId' => $sessionId, 'error' => ''];
        }

        $db->rollback();
        return ['success' => false, 'ref' => '', 'sessionId' => $sessionId, 'error' => 'Checkout failed: ' . $dbErr];
    }

    // ---------------------------------------------------------------
    // Getters / Helper Methods
    // ---------------------------------------------------------------

    /**
     * GetItems — returns the full cart contents array.
     * @return array<int,array>
     */
    public function GetItems(): array
    {
        return $this->items;
    }

    /**
     * GetCount — returns the total number of items (sum of all quantities).
     */
    public function GetCount(): int
    {
        return (int)array_sum(array_column($this->items, 'qty'));
    }

    /**
     * GetTotal — returns the total monetary value of the cart.
     */
    public function GetTotal(): float
    {
        return (float)array_sum(
            array_map(fn(array $i): float => (float)$i['sell_price'] * (int)$i['qty'], $this->items)
        );
    }

    /**
     * IsEmpty — returns true when no items are in the cart.
     */
    public function IsEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * IsLoggedIn — returns true when a user is authenticated.
     */
    public function IsLoggedIn(): bool
    {
        return !empty($this->userId);
    }

    /**
     * GetUserId — returns the current user's ID or null.
     */
    public function GetUserId(): ?int
    {
        return $this->userId;
    }

    // ---------------------------------------------------------------
    // Private Helper
    // ---------------------------------------------------------------

    /**
     * SaveToSession — persists the in-memory items array to $_SESSION.
     * Called automatically after every mutation.
     */
    private function SaveToSession(): void
    {
        $_SESSION['cart'] = $this->items;
    }
}
