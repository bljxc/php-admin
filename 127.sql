-- --------------------------------------------------------
-- 学生成绩管理系统数据库结构
-- 版本: 1.0
-- --------------------------------------------------------

--
-- 表结构 `admin`
-- 管理员表，存储系统管理员账户信息
--
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,    -- 管理员ID，自增主键
    username VARCHAR(50) NOT NULL UNIQUE,  -- 用户名，唯一
    password VARCHAR(255) NOT NULL,        -- 密码
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- 创建时间
);

--
-- 表结构 `students`
-- 学生表，存储学生基本信息
--
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,    -- 学生ID，自增主键
    student_number VARCHAR(20) NOT NULL UNIQUE,  -- 学号，唯一
    name VARCHAR(50) NOT NULL,            -- 学生姓名
    gender ENUM('男', '女') NOT NULL,      -- 性别
    class VARCHAR(50) NOT NULL,           -- 班级
    phone VARCHAR(20),                    -- 联系电话
    email VARCHAR(100),                   -- 电子邮箱
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- 创建时间
);

--
-- 表结构 `scores`
-- 成绩表，存储学生各科目成绩信息
--
CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,    -- 成绩ID，自增主键
    student_id INT NOT NULL,              -- 学生ID，关联students表
    subject VARCHAR(50) NOT NULL,         -- 科目名称
    score DECIMAL(5,2) NOT NULL,          -- 分数，支持两位小数
    exam_date DATE NOT NULL,              -- 考试日期
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- 创建时间
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE  -- 外键约束，关联学生表，级联删除
);

