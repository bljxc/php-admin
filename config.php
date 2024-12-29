<?php
/**
 * 数据库配置文件
 * 
 * 该文件包含了数据库连接的所有配置信息
 * 包括数据库的创建、表的创建以及初始管理员账户的设置
 * 
 * @author Your Name
 * @version 1.0
 */

// 数据库连接参数
$host = 'localhost';        // 数据库服务器地址
$dbname = 'student_management';  // 数据库名
$username = 'admin';         // 数据库用户名
$password = '123456';         // 数据库密码

// 创建数据库连接
$conn = new mysqli($host, $username, $password);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 设置数据库字符集为 UTF-8
$conn->set_charset("utf8");

// 检查数据库是否存在，如果不存在则创建
$sql = "CREATE DATABASE IF NOT EXISTS $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
if (!$conn->query($sql)) {
    die("创建数据库失败: " . $conn->error);
}

// 选择要使用的数据库
if (!$conn->select_db($dbname)) {
    die("选择数据库失败: " . $conn->error);
}

// 读取SQL文件并创建表结构
$tables = file_get_contents('127.sql');
if (!empty($tables)) {
    // 分割多个SQL语句并执行
    $queries = explode(';', $tables);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (!$conn->query($query)) {
                die("创建表失败: " . $conn->error . "<br>SQL: " . $query);
            }
        }
    }
}

// 检查admin表中是否存在默认管理员账户
$result = $conn->query("SELECT COUNT(*) as count FROM admin");
if ($result) {
    $row = $result->fetch_assoc();
    // 如果没有管理员账户，则创建默认账户
    if ($row['count'] == 0) {
        $sql = "INSERT INTO admin (username, password) VALUES ('admin', 'admin123')";
        if (!$conn->query($sql)) {
            die("插入管理员数据失败: " . $conn->error);
        }
    }
}
?> 