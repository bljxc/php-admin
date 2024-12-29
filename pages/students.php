<?php
/**
 * 学生管理页面
 * 
 * 该页面提供学生信息的增删改查功能
 * 包括添加新学生、修改学生信息、删除学生记录等操作
 * 
 * @author Your Name
 * @version 1.0
 */

// 引入数据库配置文件
require_once 'config.php';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 处理添加学生请求
    if ($_POST['action'] === 'add') {
        // 准备SQL语句
        $stmt = $conn->prepare("INSERT INTO students (student_number, name, gender, class, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("准备语句失败: " . $conn->error);
        }
        
        // 绑定参数并执行
        $stmt->bind_param("ssssss", 
            $_POST['student_number'],  // 学号
            $_POST['name'],           // 姓名
            $_POST['gender'],         // 性别
            $_POST['class'],          // 班级
            $_POST['phone'],          // 电话
            $_POST['email']           // 邮箱
        );
        
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
    }
    // 处理删除学生请求
    else if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        if ($stmt === false) {
            die("准备语句失败: " . $conn->error);
        }
        $stmt->bind_param("i", $_POST['id']);
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
    }
    // 处理更新学生信息请求
    else if ($_POST['action'] === 'update') {
        $stmt = $conn->prepare("UPDATE students SET student_number = ?, name = ?, gender = ?, class = ?, phone = ?, email = ? WHERE id = ?");
        if ($stmt === false) {
            die("准备语句失败: " . $conn->error);
        }
        $stmt->bind_param("ssssssi", 
            $_POST['student_number'], 
            $_POST['name'], 
            $_POST['gender'], 
            $_POST['class'], 
            $_POST['phone'], 
            $_POST['email'], 
            $_POST['id']
        );
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
    }
}

// 获取所有学生信息
$result = $conn->query("SELECT * FROM students ORDER BY created_at DESC");
if ($result === false) {
    die("查询失败: " . $conn->error);
}
$students = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- 添加学生表单 -->
<div class="form-container">
    <h2>添加学生</h2>
    <form method="POST" action="" id="studentForm">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" id="studentId">
        
        <div class="form-group">
            <label for="student_number">学号</label>
            <input type="text" class="form-control" id="student_number" name="student_number" required>
        </div>
        
        <div class="form-group">
            <label for="name">姓名</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="gender">性别</label>
            <select class="form-control" id="gender" name="gender" required>
                <option value="男">男</option>
                <option value="女">女</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="class">班级</label>
            <input type="text" class="form-control" id="class" name="class" required>
        </div>
        
        <div class="form-group">
            <label for="phone">电话</label>
            <input type="tel" class="form-control" id="phone" name="phone">
        </div>
        
        <div class="form-group">
            <label for="email">邮箱</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        
        <button type="submit" class="btn btn-primary">添加学生</button>
    </form>
</div>

<!-- 学生列表 -->
<div class="table-container">
    <h2>学生列表</h2>
    <table>
        <thead>
            <tr>
                <th>学号</th>
                <th>姓名</th>
                <th>性别</th>
                <th>班级</th>
                <th>电话</th>
                <th>邮箱</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['gender']); ?></td>
                <td><?php echo htmlspecialchars($student['class']); ?></td>
                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
                <td>
                    <button class="btn btn-primary" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">编辑</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除这个学生吗？')">删除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
/**
 * 编辑学生信息
 * @param {Object} student - 学生信息对象
 */
function editStudent(student) {
    // 填充表单
    document.getElementById('studentId').value = student.id;
    document.getElementById('student_number').value = student.student_number;
    document.getElementById('name').value = student.name;
    document.getElementById('gender').value = student.gender;
    document.getElementById('class').value = student.class;
    document.getElementById('phone').value = student.phone;
    document.getElementById('email').value = student.email;
    
    // 更改表单动作和按钮文字
    document.querySelector('input[name="action"]').value = 'update';
    document.querySelector('button[type="submit"]').textContent = '更新学生';
}
</script> 