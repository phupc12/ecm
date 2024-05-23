<?php get_header(); ?>

<h1 class="text-center mt-5 mb-5">Giỏ hàng của bạn</h1>

<?php
// Kiểm tra nếu session chưa được khởi động
if (!session_id()) {
    session_start();
}
// Check if the order form has been submitted
if (isset($_POST['order'])) {
    // Retrieve form data
    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
    $note = isset($_POST['note']) ? wp_kses_post($_POST['note']) : '';

    // Retrieve cart data from session
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

    // Calculate total price and product names
    $productNames = array();
    $totalCart = 0;

    foreach ($cart as $product) {
        $productName = $product['name'];
        $quantity = $product['quantity'];
        $totalProduct = $product['price'] * $quantity;
        $totalCart += $totalProduct;
        $productNames[] = "$productName x $quantity";
    }

    // Compose email content
    $subject = 'Thông tin đơn hàng';
    $message = 'Tên khách hàng: ' . $username . "\n" .
        'Số điện thoại: ' . $phone . "\n" .
        'Email: ' . $email . "\n" .
        'Địa chỉ giao hàng: ' . $address . "\n" .
        'Ghi chú: ' . $note . "\n" .
        'Sản phẩm: ' . implode(', ', $productNames) . "\n" .
        'Tổng cộng: ' . number_format($totalCart, 2) . ' VNĐ';

    // Send email using WP SMTP
    $to = 'votanphu.lop12a7@gmail.com'; // Replace 'yourgmail@gmail.com' with your Gmail address
    $headers = 'From: ' . $email;

    $mail_sent = wp_mail($to, $subject, $message, $headers);

    if ($mail_sent) {
        // Clear the cart after successful order
        unset($_SESSION['cart']);

        // Redirect to a thank you page or display success message
        header('Location: thank_you_page.php');
        exit;
    } else {
        // Redirect to an error page or display error message
        header('Location: error_page.php');
        exit;
    }
}
$username = isset($_POST['username']) ? $_POST['username'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : '';
$note = isset($_POST['note']) ? $_POST['note'] : '';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();


// Kiểm tra nếu có yêu cầu cập nhật số lượng từ request POST
if (isset($_POST['update_quantity']) && isset($_POST['index']) && isset($_POST['quantity'])) {
    // Lấy index của sản phẩm và số lượng mới từ request POST
    $index = $_POST['index'];
    $newQuantity = $_POST['quantity'];

    // Cập nhật số lượng mới cho sản phẩm trong giỏ hàng
    $cart[$index]['quantity'] = $newQuantity;

    // Lưu lại giỏ hàng mới vào session
    $_SESSION['cart'] = $cart;
}

?>



<div class="container card-list" id="container">
    <ul class="d-flex list-unstyled text-start border-bottom border-3 p-2">
        <li class="col-md-3 col-4"><b>Sản phẩm</b></li>
        <li class="col-md-2 col-2"><b>Giá</b></li>
        <li class="col-md-2 col-2"><b>Số lượng</b></li>
        <li class="col-md-3 col-2"><b>Tổng</b></li>
        <li class="col-md-1 col-1"><b>Thao tác</b></li>
    </ul>

    <?php
    $total_cart = 0;

    foreach ($cart as $index => $product):
        $total_product = $product['price'] * $product['quantity'];
        $total_cart += $total_product;
        ?>

        <ul class="d-flex list-unstyled border-bottom border-3 border-gray p-2">
            <li class="col-sm-3 img-product d-flex align-self-center text-start">
                <?php if (has_post_thumbnail($product['id'])): ?>
                    <?php echo get_the_post_thumbnail($product['id'], 'thumbnail', array('alt' => $product['name'], 'class' => 'mr-3')); ?>
                <?php else: ?>
                    <img src="<?php echo $product['image']; ?>" class="img-product mr-3">
                <?php endif; ?>
                <div class="align-self-center ps-3">
                    <p><?php echo $product['name']; ?></p>
                </div>
            </li>
            <li class="col-md-2 col-2 quantity align-self-center "><?php echo $product['price']; ?> VNĐ</li>
            <li class="col-md-2 col-2 quantity align-self-center text-start">
                <div class="w-50">
                    <div class="input-group">
                        <button class="btn btn-sm btn-outline-secondary decrement-btn" type="button"
                            onclick="updateQuantity(<?php echo $index; ?>, -1)">-</button>
                        <input type="text" step="1" min="1" max="" name="quantity"
                            value="<?php echo $product['quantity']; ?>" title="Qty" class="form-control quantity-input"
                            size="4" pattern="[0-9]*" inputmode="numeric" data-index="<?php echo $index; ?>"
                            onchange="updateQuantityFromInput(this)">
                        <button class="btn btn-sm btn-outline-secondary increment-btn" type="button"
                            onclick="updateQuantity(<?php echo $index; ?>, 1)">+</button>
                    </div>
                </div>
            </li>
            <li class="col-md-3 col-3 align-self-center total" id="total-price-<?php echo $index; ?>"
                data-price="<?php echo $product['price']; ?>"><?php echo $total_product; ?> VNĐ</li>
            <li class="col-md-1 col-1 align-self-center section">
                <div class="col-md-1 col-1 align-self-center section">
                    <button type="button" class="btn btn-sm btn-danger btn-delete-item"
                        data-id="<?php echo $product['id']; ?>">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </li>
        </ul>

    <?php endforeach; ?>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-7">
            <div class="page-1">
                <h5 class="mb-4">Thông Tin Khách Hàng</h5>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="username" placeholder="Họ và tên">
                        </div>
                        <div class="col-md-6">
                            <input type="tel" class="form-control" name="phone" placeholder="Số điện thoại">
                        </div>
                        <div class="col-12">
                            <input type="email" class="form-control" name="email" placeholder="Email">
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control" name="address" placeholder="Địa chỉ giao hàng">
                        </div>
                        <div class="col-12">
                            <h5><b>THÔNG TIN BỔ SUNG</b></h5>
                            <h7><b>Ghi chú đơn hàng (tùy chọn)</b></h7>
                            <textarea class="form-control" rows="8" name="note"
                                placeholder="Ghi chú đơn hàng của bạn"></textarea>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <div class="col-md-5">
            <div class="card donhang">
                <div class="card-body">
                    <h5 class="card-title"><b>Đơn hàng của bạn</b></h5>
                    <div class="row mb-3">
                        <div class="col-6 border-bottom border-2"><b>Sản phẩm</b></div>
                        <div class="col-6 text-end border-bottom border-2"><b>Tổng</b></div>
                        <div class="col-6 mt-3" id="product-names"><?php echo implode(', ', $productNames); ?></div>
                        <div class="col-6 text-end mt-3" id="total-cart"><?php echo number_format($total_cart, 2); ?>
                            VNĐ</div>
                        <div class="col-6 mt-3">Tạm tính:</div>
                        <div class="col-6 mt-3 text-end" id="sub-total"><?php echo number_format($total_cart, 2); ?> VNĐ
                        </div>
                        <div class="col-6 mt-3">Thành tiền:</div>
                        <div class="col-6 mt-3 text-end" id="grand-total"><?php echo number_format($total_cart, 2); ?>
                            VNĐ</div>
                    </div>

                    <div class="text-center pt-2 border-top border-2">
                        <div class="row">
                            <h5 class="text-start">Hình Thức Thanh Toán</h5>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="transferPayment">
                                    <label class="form-check-label" for="transferPayment">
                                        Thanh Toán Bằng Chuyển Khoản
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="cashPayment">
                                    <label class="form-check-label" for="cashPayment">
                                        Thanh Toán Bằn Tiền Mặt
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-2 border-top border-bottom border-2">
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading">Cảm ơn bạn đã đặt hàng!</h4>
                            <p class="mb-0">Chúng tôi sẽ gửi thông tin đơn hàng của bạn qua email đến địa chỉ Gmail của
                                bạn. Vui lòng kiểm tra hộp thư đến và nhấn vào nút xác nhận để hoàn tất quá trình đặt
                                hàng.</p>
                        </div>
                        <div class="mb-3 mt-3 text-end">
                            <button type="submit" class="btn btn-primary" name="order">Đặt hàng</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php

?>



<?php get_footer(); ?>