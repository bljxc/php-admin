<?php
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// 获取当前页面
$page = isset($_GET['page']) ? $_GET['page'] : 'students';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>学生成绩管理系统 - 后台管理</title>
    <!-- 引入 ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .header {
            background-color: #333;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome {
            font-size: 18px;
        }

        .logout {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid white;
            border-radius: 4px;
        }

        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .sidebar {
            width: 200px;
            background-color: #444;
            padding: 20px 0;
            
        }

        .nav-item {
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }

        .nav-item:hover, .nav-item.active {
            background-color: #555;
        }

        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        /* 表单样式 */
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: white;
        }

        .btn-primary {
            background-color: #4CAF50;
        }

        .btn-danger {
            background-color: #f44336;
        }

        /* 表格样式 */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        /* 聊天界面样式 */
        .chat-container {
            display: flex;
            height: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .chat-list {
            width: 250px;
            border-right: 1px solid #ddd;
            overflow-y: auto;
        }

        .chat-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .chat-input {
            padding: 20px;
            border-top: 1px solid #ddd;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .message-content {
            max-width: 70%;
            padding: 10px;
            border-radius: 8px;
            margin: 5px 0;
        }

        .message.sent {
            align-items: flex-end;
        }

        .message.received {
            align-items: flex-start;
        }

        .message.sent .message-content {
            background-color: #4CAF50;
            color: white;
        }

        .message.received .message-content {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="welcome">欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <a href="logout.php" class="logout">退出登录</a>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <a href="?page=students" class="nav-item <?php echo $page === 'students' ? 'active' : ''; ?>">学生管理</a>
            <a href="?page=scores" class="nav-item <?php echo $page === 'scores' ? 'active' : ''; ?>">成绩管理</a>
            <a href="?page=statistics" class="nav-item <?php echo $page === 'statistics' ? 'active' : ''; ?>">成绩统计</a>
            <a href="?page=chat" class="nav-item <?php echo $page === 'chat' ? 'active' : ''; ?>">联系学生</a>
        </div>
        
        <div class="content">
            <?php
            switch($page) {
                case 'students':
                    include 'pages/students.php';
                    break;
                case 'scores':
                    include 'pages/scores.php';
                    break;
                case 'statistics':
                    include 'pages/statistics.php';
                    break;
                case 'chat':
                    include 'pages/chat.php';
                    break;
                default:
                    include 'pages/students.php';
            }
            ?>
        </div>
    </div>
</body>
</html> 