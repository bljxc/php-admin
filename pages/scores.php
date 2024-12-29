<?php
/**
 * 成绩管理页面
 * 
 * 该页面提供学生成绩的增删改查功能
 * 包括添加新成绩、修改成绩、删除成绩记录等操作
 * 
 * @author Your Name
 * @version 1.0
 */

// 引入数据库配置文件
require_once 'config.php';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 处理添加成绩请求
    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO scores (student_id, subject, score, exam_date) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("准备语句失败: " . $conn->error);
        }
        $stmt->bind_param("isds", 
            $_POST['student_id'],  // 学生ID
            $_POST['subject'],     // 科目
            $_POST['score'],       // 分数
            $_POST['exam_date']    // 考试日期
        );
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
    }
    // 处理删除成绩请求
    else if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $conn->prepare("DELETE FROM scores WHERE id = ?");
        if ($stmt === false) {
            die("准备语句失败: " . $conn->error);
        }
        $stmt->bind_param("i", $_POST['id']);
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
    }
    // 处理更新成绩请求
    else if ($_POST['action'] === 'update') {
        $stmt = $conn->prepare("UPDATE scores SET student_id = ?, subject = ?, score = ?, exam_date = ? WHERE id = ?");
        if ($stmt === false) {
            die("准备语句失败: " . $conn->error);
        }
        $stmt->bind_param("isdsi", 
            $_POST['student_id'], 
            $_POST['subject'], 
            $_POST['score'], 
            $_POST['exam_date'], 
            $_POST['id']
        );
        if (!$stmt->execute()) {
            die("执行失败: " . $stmt->error);
        }
    }
}

// 获取所有学生信息（用于下拉选择）
$students = $conn->query("SELECT id, name, student_number FROM students ORDER BY name");
if ($students === false) {
    die("查询学生失败: " . $conn->error);
}
$students = $students->fetch_all(MYSQLI_ASSOC);

// 获取所有成绩记录（包含学生信息）
$scores = $conn->query("
    SELECT scores.*, students.name as student_name, students.student_number 
    FROM scores 
    JOIN students ON scores.student_id = students.id 
    ORDER BY scores.exam_date DESC
");
if ($scores === false) {
    die("查询成绩失败: " . $conn->error);
}
$scores = $scores->fetch_all(MYSQLI_ASSOC);
?>

<!-- 添加成绩表单 -->
<div class="form-container">
    <h2>添加成绩</h2>
    <form method="POST" action="" id="scoreForm">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" id="scoreId">
        
        <div class="form-group">
            <label for="student_id">学生</label>
            <select class="form-control" id="student_id" name="student_id" required>
                <option value="">请选择学生</option>
                <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['id']; ?>">
                    <?php echo htmlspecialchars($student['student_number'] . ' - ' . $student['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="subject">科目</label>
            <select class="form-control" id="subject" name="subject" required>
                <option value="">请选择科目</option>
                <option value="计算机网络技术">计算机网络技术</option>
                <option value="计算机应用基础">计算机应用基础</option>
                <option value="计算机组成原理">计算机组成原理</option>
                <option value="数据结构">数据结构</option>
                <option value="VUE">VUE</option>
                <option value="GO语言">GO语言</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="score">分数</label>
            <input type="number" class="form-control" id="score" name="score" min="0" max="100" step="0.5" required>
        </div>
        
        <div class="form-group">
            <label for="exam_date">考试日期</label>
            <input type="date" class="form-control" id="exam_date" name="exam_date" required>
        </div>
        
        <button type="submit" class="btn btn-primary">添加成绩</button>
    </form>
</div>

<!-- 成绩列表 -->
<div class="table-container">
    <h2>成绩列表</h2>
    <table>
        <thead>
            <tr>
                <th>学号</th>
                <th>姓名</th>
                <th>科目</th>
                <th>分数</th>
                <th>考试日期</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($scores as $score): ?>
            <tr>
                <td><?php echo htmlspecialchars($score['student_number']); ?></td>
                <td><?php echo htmlspecialchars($score['student_name']); ?></td>
                <td><?php echo htmlspecialchars($score['subject']); ?></td>
                <td><?php echo htmlspecialchars($score['score']); ?></td>
                <td><?php echo htmlspecialchars($score['exam_date']); ?></td>
                <td>
                    <button class="btn btn-primary" onclick="editScore(<?php echo htmlspecialchars(json_encode($score)); ?>)">编辑</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $score['id']; ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除这条成绩记录吗？')">删除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
/**
 * 编辑成绩信息
 * @param {Object} score - 成绩信息对象
 */
function editScore(score) {
    // 填充表单
    document.getElementById('scoreId').value = score.id;
    document.getElementById('student_id').value = score.student_id;
    document.getElementById('subject').value = score.subject;
    document.getElementById('score').value = score.score;
    document.getElementById('exam_date').value = score.exam_date;
    
    // 更改表单动作和按钮文字
    document.querySelector('input[name="action"]').value = 'update';
    document.querySelector('button[type="submit"]').textContent = '更新成绩';
}
</script> 