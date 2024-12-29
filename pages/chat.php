<?php
require_once 'config.php';

// 获取所有学生列表
$students = $conn->query("SELECT id, name, student_number FROM students ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// 获取当前选中的学生
$selected_student = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;


?>

<div class="chat-container">
    <div class="chat-list">
        <div style="padding: 15px; border-bottom: 1px solid #ddd;">
            <input type="text" class="form-control" id="searchStudent" placeholder="搜索学生..." oninput="filterStudents(this.value)">
        </div>
        <div id="studentList">
            <?php foreach ($students as $student): ?>
            <div class="student-item <?php echo $selected_student === $student['id'] ? 'active' : ''; ?>" 
                 onclick="selectStudent(<?php echo $student['id']; ?>)"
                 data-id="<?php echo $student['id']; ?>"
                 data-name="<?php echo htmlspecialchars($student['name']); ?>">
                <div class="student-avatar"><?php echo mb_substr($student['name'], 0, 1); ?></div>
                <div class="student-info">
                    <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                    <div class="student-number"><?php echo htmlspecialchars($student['student_number']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="chat-content">
        <?php if ($selected_student): ?>
        <div class="chat-header">
            <span id="currentStudentName"></span>
        </div>
        <div class="chat-messages" id="messageContainer"></div>
        <div class="chat-input">
            <textarea class="form-control" id="messageInput" placeholder="输入消息..." rows="3"></textarea>
            <button class="btn btn-primary" onclick="sendMessage()">发送</button>
        </div>
        <?php else: ?>
        <div class="chat-placeholder">
            <p>请选择一个学生开始聊天</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.chat-container {
    display: flex;
    height: calc(100vh - 120px);
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chat-list {
    width: 250px;
    border-right: 1px solid #ddd;
    overflow-y: auto;
    background: #f5f5f5;
}

.student-item {
    display: flex;
    align-items: center;
    padding: 15px;
    cursor: pointer;
    transition: background-color 0.3s;
    border-bottom: 1px solid #eee;
}

.student-item:hover, .student-item.active {
    background-color: #e8e8e8;
}

.student-avatar {
    width: 40px;
    height: 40px;
    background-color: #4CAF50;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-right: 10px;
}

.student-info {
    flex: 1;
}

.student-name {
    font-weight: bold;
    margin-bottom: 3px;
}

.student-number {
    font-size: 12px;
    color: #666;
}

.chat-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-header {
    padding: 15px;
    background: #f8f8f8;
    border-bottom: 1px solid #ddd;
    font-weight: bold;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f5f5f5;
}

.chat-input {
    padding: 15px;
    border-top: 1px solid #ddd;
    background: white;
    display: flex;
    gap: 10px;
}

.chat-input textarea {
    flex: 1;
    resize: none;
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
    word-break: break-word;
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
    background-color: white;
}

.message-time {
    font-size: 12px;
    color: #666;
    margin: 2px 5px;
}

.chat-placeholder {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 16px;
}
</style>

<script>
let currentStudentId = <?php echo $selected_student ?: 'null'; ?>;
let lastMessageTime = null;

function selectStudent(studentId) {
    currentStudentId = studentId;
    const studentElement = document.querySelector(`.student-item[data-id="${studentId}"]`);
    
    // 更新选中状态
    document.querySelectorAll('.student-item').forEach(item => item.classList.remove('active'));
    studentElement.classList.add('active');
    
    // 更新聊天标题
    document.getElementById('currentStudentName').textContent = studentElement.getAttribute('data-name');
    
    // 加载消息
    loadMessages();
}

function loadMessages() {
    if (!currentStudentId) return;
    
    fetch(`?page=chat&action=get_messages&student_id=${currentStudentId}`)
        .then(response => response.json())
        .then(messages => {
            const container = document.getElementById('messageContainer');
            container.innerHTML = '';
            
            messages.forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${message.sender_id === 0 ? 'sent' : 'received'}`;
                
                const time = new Date(message.created_at);
                const timeStr = time.toLocaleString();
                
                messageDiv.innerHTML = `
                    <div class="message-time">${timeStr}</div>
                    <div class="message-content">${message.content}</div>
                `;
                
                container.appendChild(messageDiv);
            });
            
            // 滚动到底部
            container.scrollTop = container.scrollHeight;
        });
}

function sendMessage() {
    if (!currentStudentId) return;
    
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content) return;
    
    const formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('receiver_id', currentStudentId);
    formData.append('content', content);
    
    fetch('?page=chat', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            input.value = '';
            loadMessages();
        }
    });
}

function filterStudents(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.student-item').forEach(item => {
        const name = item.querySelector('.student-name').textContent.toLowerCase();
        const number = item.querySelector('.student-number').textContent.toLowerCase();
        
        if (name.includes(query) || number.includes(query)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// 初始化
if (currentStudentId) {
    selectStudent(currentStudentId);
}

// 定期刷新消息
setInterval(loadMessages, 5000);

// 绑定回车发送
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});
</script> 