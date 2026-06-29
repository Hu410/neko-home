<?php
function getFavicon($url) {
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return '';
    return "https://www.google.com/s2/favicons?domain=" . $host . "&sz=64";
}

function loadBookmarks($file) {
    $categories = [];
    $currentCategory = null;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        if (preg_match('/^\[(.+)\]$/', $line, $matches)) {
            $currentCategory = $matches[1];
            $categories[] = [
                'name' => $currentCategory,
                'icon' => getCategoryIcon($currentCategory),
                'items' => []
            ];
        } elseif (strpos($line, '|') !== false && $currentCategory !== null) {
            $parts = explode('|', $line, 3);
            if (count($parts) === 3) {
                $categories[count($categories) - 1]['items'][] = [
                    'name' => trim($parts[0]),
                    'url' => trim($parts[1]),
                    'desc' => trim($parts[2])
                ];
            }
        }
    }

    return $categories;
}

function getCategoryIcon($name) {
    $icons = [
        '开发工具' => '🔧',
        'AI 工具' => '🤖',
        '云服务' => '☁️',
        '论坛社区' => '📖',
        '效率工具' => '⚡',
    ];
    return $icons[$name] ?? '📌';
}

$categories = loadBookmarks(__DIR__ . '/.env');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeKo导航 - 精确搜索导航站</title>
    <meta name="description" content="NeKo导航 - 简洁高效的精确搜索导航网站，收录开发工具、AI工具、设计资源等优质站点">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='12' fill='%23fff'/><circle cx='10' cy='14' r='3' fill='%23409eff'/><circle cx='22' cy='14' r='3' fill='%23409eff'/><path d='M8 22 Q16 28 24 22' stroke='%23e48694' stroke-width='2' fill='none'/><path d='M6 8 L10 14' stroke='%2379c90d' stroke-width='2'/><path d='M26 8 L22 14' stroke='%23a186f2' stroke-width='2'/></svg>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #409eff;
            --primary-light: #ecf5ff;
            --bg: #f5f7fa;
            --card-bg: #ffffff;
            --text: #303133;
            --text-secondary: #909399;
            --border: #dcdfe6;
            --border-light: #e4ebf3;
            --shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 6px 24px rgba(0, 0, 0, 0.12);
            --radius: 15px;
            --gradient-cat: linear-gradient(135deg, #409eff 0%, #337ecc 100%);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'PingFang SC', 'Microsoft YaHei', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .header {
            background: rgba(255,255,255,0.88);
            border-bottom: 1px solid var(--border-light);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .header-inner {
            max-width: 1170px;
            margin: 0 auto;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text);
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--gradient-cat);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            box-shadow: 0 2px 8px rgba(64,158,255,0.3);
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(45deg, #409eff, #67c23a, #e6a23c, #f56c6c);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 8s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-stats {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .search-wrapper {
            max-width: 680px;
            margin: 0 auto;
            padding: 32px 20px 24px;
        }

        .search-title {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }

        .search-subtitle {
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: var(--card-bg);
            border: 2px solid var(--border-light);
            border-radius: 50px;
            padding: 4px 4px 4px 20px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            border-color: var(--primary);
            box-shadow: 0 4px 20px rgba(64,158,255,0.15);
        }

        .search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 16px;
            background: transparent;
            color: var(--text);
            padding: 10px 0;
        }

        .search-box input::placeholder {
            color: #c0c4cc;
        }

        .search-btn {
            background: var(--gradient-cat);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .search-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(64,158,255,0.3);
        }

        .site-filter {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .site-filter-btn {
            font-size: 12px;
            color: var(--text-secondary);
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            padding: 5px 14px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .site-filter-btn:hover,
        .site-filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .main {
            max-width: 1170px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        .category-section {
            margin-bottom: 36px;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light);
        }

        .category-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--gradient-cat);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 2px 8px rgba(64,158,255,0.25);
        }

        .category-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }

        .category-count {
            font-size: 13px;
            color: var(--text-secondary);
            background: #f0f2f5;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .site-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }

        .site-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            padding: 14px 16px;
            text-decoration: none;
            color: var(--text);
            transition: all 0.3s ease;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }

        .site-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .site-icon {
            font-size: 24px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-light);
            border-radius: 10px;
            flex-shrink: 0;
        }

        .site-icon img {
            width: 24px;
            height: 24px;
            object-fit: contain;
        }

        .site-info h3 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .site-info p {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .footer {
            text-align: center;
            padding: 32px 20px;
            color: var(--text-secondary);
            font-size: 13px;
            border-top: 1px solid var(--border-light);
            background: var(--card-bg);
        }

        @media (max-width: 768px) {
            .header-inner {
                padding: 12px 16px;
            }
            .logo-text {
                font-size: 16px;
            }
            .header-stats {
                display: none;
            }
            .search-wrapper {
                padding: 24px 16px 16px;
            }
            .search-title {
                font-size: 22px;
            }
            .search-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            .main {
                padding: 0 12px 40px;
            }
            .site-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .search-box {
                border-radius: 12px;
                padding: 4px 4px 4px 14px;
            }
            .search-btn {
                border-radius: 10px;
                padding: 10px 18px;
            }
            .site-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-inner">
        <a href="/" class="logo">
            <div class="logo-icon">🐱</div>
            <span class="logo-text">NeKo 导航</span>
        </a>
        <div class="header-right">
            <span class="header-stats">收录 <?php
                $total = 0;
                foreach ($categories as $cat) {
                    $total += count($cat['items']);
                }
                echo $total;
            ?> 个优质站点</span>
        </div>
    </div>
</div>

<div class="search-wrapper">
    <h1 class="search-title">精确搜索导航</h1>
    <p class="search-subtitle">输入关键词，通过 Google 搜索你需要的内容</p>
    <form id="searchForm" action="https://www.google.com/search" method="GET" target="_blank" onsubmit="return injectQuery();">
        <input type="hidden" name="q" id="hiddenQuery">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="搜索网站名称或描述..." autofocus autocomplete="off">
            <button type="submit" class="search-btn">Google 搜索</button>
        </div>
    </form>
    <div class="site-filter">
        <a href="javascript:void(0)" class="site-filter-btn active" onclick="setSite('',this)">全部</a>
        <a href="javascript:void(0)" class="site-filter-btn" onclick="setSite('github.com',this)">GitHub</a>
        <a href="javascript:void(0)" class="site-filter-btn" onclick="setSite('linux.do',this)">Linux.do</a>
        <a href="javascript:void(0)" class="site-filter-btn" onclick="setSite('v2ex.com',this)">V2EX</a>
        <a href="javascript:void(0)" class="site-filter-btn" onclick="setSite('juejin.cn',this)">掘金</a>
    </div>
</div>

<div class="main">
    <?php foreach ($categories as $cat): ?>
        <div class="category-section">
            <div class="category-header">
                <div class="category-icon"><?php echo $cat['icon']; ?></div>
                <h2 class="category-name"><?php echo $cat['name']; ?></h2>
                <span class="category-count"><?php echo count($cat['items']); ?> 个</span>
            </div>
            <div class="site-grid">
                <?php foreach ($cat['items'] as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" rel="noopener" class="site-card">
                        <div class="site-icon">
                            <?php $favicon = getFavicon($item['url']); ?>
                            <?php if ($favicon): ?>
                                <img src="<?php echo $favicon; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.style.display='none';this.parentNode.innerHTML='🌐';">
                            <?php else: ?>
                                🌐
                            <?php endif; ?>
                        </div>
                        <div class="site-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['desc']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<footer class="footer">
    <p>NeKo导航 &copy; <?php echo date('Y'); ?> — 精确搜索导航站</p>
</footer>

<script>
let currentSite = '';

function setSite(site, el) {
    currentSite = site;
    document.querySelectorAll('.site-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    el.classList.add('active');
    document.getElementById('searchInput').focus();
}

function injectQuery() {
    const query = document.getElementById('searchInput').value.trim();
    if (!query) return false;

    let searchQuery = query;
    if (currentSite) {
        searchQuery = 'site:' + currentSite + ' ' + query;
    }

    document.getElementById('hiddenQuery').value = searchQuery;
    return true;
}
</script>

</body>
</html>
