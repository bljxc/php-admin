<?php
require_once 'config.php';

// 获取所有科目的平均分
$avg_scores = $conn->query("
    SELECT subject, AVG(score) as avg_score
    FROM scores
    GROUP BY subject
")->fetch_all(MYSQLI_ASSOC);

// 获取各分数段的学生人数
$score_distribution = $conn->query("
    SELECT 
        subject,
        SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) as excellent,
        SUM(CASE WHEN score >= 80 AND score < 90 THEN 1 ELSE 0 END) as good,
        SUM(CASE WHEN score >= 70 AND score < 80 THEN 1 ELSE 0 END) as fair,
        SUM(CASE WHEN score >= 60 AND score < 70 THEN 1 ELSE 0 END) as pass,
        SUM(CASE WHEN score < 60 THEN 1 ELSE 0 END) as fail
    FROM scores
    GROUP BY subject
")->fetch_all(MYSQLI_ASSOC);

// 获取最近考试的成绩趋势
$score_trends = $conn->query("
    SELECT 
        subject,
        exam_date,
        AVG(score) as avg_score
    FROM scores
    GROUP BY subject, exam_date
    ORDER BY exam_date
")->fetch_all(MYSQLI_ASSOC);
?>

<div style="display: flex; flex-wrap: wrap; gap: 20px; padding: 20px;">
    <!-- 平均分统计 -->
    <div class="form-container" style="flex: 1; min-width: 300px;">
        <h2>各科目平均分</h2>
        <div id="avgScoreChart" style="width: 100%; height: 400px;"></div>
    </div>
    
    <!-- 分数段分布 -->
    <div class="form-container" style="flex: 1; min-width: 300px;">
        <h2>成绩分布</h2>
        <div id="scoreDistributionChart" style="width: 100%; height: 400px;"></div>
    </div>
    
    <!-- 成绩趋势 -->
    <div class="form-container" style="flex: 2; min-width: 600px;">
        <h2>成绩趋势</h2>
        <div id="scoreTrendChart" style="width: 100%; height: 400px;"></div>
    </div>
</div>

<script>
// 平均分柱状图
const avgScoreChart = echarts.init(document.getElementById('avgScoreChart'));
avgScoreChart.setOption({
    title: {
        text: '各科目平均分'
    },
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow'
        }
    },
    xAxis: {
        type: 'category',
        data: <?php echo json_encode(array_column($avg_scores, 'subject')); ?>
    },
    yAxis: {
        type: 'value',
        min: 0,
        max: 100
    },
    series: [{
        data: <?php echo json_encode(array_map(function($item) {
            return round($item['avg_score'], 2);
        }, $avg_scores)); ?>,
        type: 'bar',
        showBackground: true,
        backgroundStyle: {
            color: 'rgba(180, 180, 180, 0.2)'
        }
    }]
});

// 分数段分布堆叠图
const scoreDistributionChart = echarts.init(document.getElementById('scoreDistributionChart'));
scoreDistributionChart.setOption({
    title: {
        text: '成绩分布'
    },
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow'
        }
    },
    legend: {
        data: ['优秀', '良好', '中等', '及格', '不及格']
    },
    xAxis: {
        type: 'category',
        data: <?php echo json_encode(array_column($score_distribution, 'subject')); ?>
    },
    yAxis: {
        type: 'value'
    },
    series: [
        {
            name: '优秀',
            type: 'bar',
            stack: 'total',
            data: <?php echo json_encode(array_column($score_distribution, 'excellent')); ?>
        },
        {
            name: '良好',
            type: 'bar',
            stack: 'total',
            data: <?php echo json_encode(array_column($score_distribution, 'good')); ?>
        },
        {
            name: '中等',
            type: 'bar',
            stack: 'total',
            data: <?php echo json_encode(array_column($score_distribution, 'fair')); ?>
        },
        {
            name: '及格',
            type: 'bar',
            stack: 'total',
            data: <?php echo json_encode(array_column($score_distribution, 'pass')); ?>
        },
        {
            name: '不及格',
            type: 'bar',
            stack: 'total',
            data: <?php echo json_encode(array_column($score_distribution, 'fail')); ?>
        }
    ]
});

// 成绩趋势折线图
const scoreTrendChart = echarts.init(document.getElementById('scoreTrendChart'));

// 处理数据
const trendData = <?php echo json_encode($score_trends); ?>;
const subjects = [...new Set(trendData.map(item => item.subject))];
const dates = [...new Set(trendData.map(item => item.exam_date))];
const series = subjects.map(subject => ({
    name: subject,
    type: 'line',
    data: dates.map(date => {
        const point = trendData.find(item => item.subject === subject && item.exam_date === date);
        return point ? Number(point.avg_score).toFixed(2) : null;
    })
}));

scoreTrendChart.setOption({
    title: {
        text: '成绩趋势'
    },
    tooltip: {
        trigger: 'axis'
    },
    legend: {
        data: subjects
    },
    xAxis: {
        type: 'category',
        data: dates
    },
    yAxis: {
        type: 'value',
        min: 0,
        max: 100
    },
    series: series
});

// 响应式调整
window.addEventListener('resize', function() {
    avgScoreChart.resize();
    scoreDistributionChart.resize();
    scoreTrendChart.resize();
});
</script> 