<?
if(preg_match("/(^\d+(\.\d+)?)\=/", $_GET['s'], $matches)) {
    include "generateCurrency.php";
    $money = floatval($matches[1]);
    $array = [$_GET['s']];
    $array[] = genSuggestQueries($money);
    echo json_encode($array); exit();
} else if($_GET['s']) {
    header('Location: https://suggestqueries.google.com/complete/search?client=firefox&q='.urlencode($_GET['s']));
    exit();
} else if($_GET['q']) {
    if(preg_match("/(^\d+(\.\d+)?)\=/", $_GET['q'], $matches)) {
        include "generateCurrency.php";
        $money = floatval($matches[1]);
        $results = genSuggestQueries($money);
    } else {
        header('Location: https://www.google.com/search?client=firefox-b-d&q='.urlencode($_GET['q']));
        exit();
    }
} else {
    $money = 1000;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>CurrencySearch - 快速匯率轉換工具</title>
    <!-- Facebook Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://grassboy.tw/CurrencySearch/">
    <meta property="og:title" content="CurrencySearch - 快速匯率轉換工具">
    <meta property="og:description" content="一鍵快速查詢台幣與五大貨幣（日幣、韓元、美金、歐元、港幣）的即時匯率轉換結果。支援 Firefox 搜尋列整合，讓匯率查詢更便利！">
    <meta property="og:image" content="/CurrencySearch/preview.png">
    <meta property="og:locale" content="zh_TW">
    
    <link rel="icon" type="image/png" href="/CurrencySearch/icon.png">
    <link rel="search" type="application/opensearchdescription+xml" title="CurrencySearch" href="/CurrencySearch/template.xml">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(145deg, #f5f5f5 0%, #e5e5e5 100%);
            min-height: 100vh;
        }
        .gradient-text {
            font-size: 4.5rem;
            font-weight: bold;
            background: linear-gradient(to right, #0088cc 0%, #ff9900 50%, #ff4444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .subtitle {
            color: #666;
            font-size: 1.8rem;
            font-weight: 300;
        }
        .card {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 15px;
        }
        .result-list {
            list-style: none;
            padding: 0;
            text-align: left;
            align-self: center;
        }
        .result-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .result-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="gradient-text mb-4">CurrencySearch</h1>
                <p class="subtitle mb-5">利用 Firefox Search Box 快速查詢匯率</p>
                <?php if(isset($money)): ?>
                <div class="card shadow mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-center mb-4">
                            <input type="number" class="form-control text-center" style="max-width: 150px;" id="moneyInput" value="<?php echo $money; ?>">
                            <span class="ms-2">的匯率轉換結果</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 d-flex flex-column">
                                <h6 class="mb-3">各國轉台幣</h6>
                                <ul class="result-list" id="foreignToTWD"></ul>
                            </div>
                            <div class="col-md-6 d-flex flex-column">
                                <h6 class="mb-3">台幣轉各國</h6>
                                <ul class="result-list" id="TWDToForeign"></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {
                        const flagEmoji = {
                            "TWD": "🇹🇼",
                            "JPY": "🇯🇵",
                            "USD": "🇺🇸",
                            "EUR": "🇪🇺",
                            "HKD": "🇭🇰",
                            "KRW": "🇰🇷"
                        };

                        let currencyData = null;

                        // 載入匯率資料
                        $.getJSON('currency.json', function(data) {
                            currencyData = data;
                            updateResults($('#moneyInput').val());
                            $('#updatedAt').text(currencyData.updated_at);
                        });

                        // 更新結果函數
                        function updateResults(amount) {
                            if (!currencyData || !amount || isNaN(amount)) return;

                            const currencies = currencyData.currency;
                            const foreignToTWD = currencies.slice(0, 5);
                            const TWDToForeign = currencies.slice(5, 10);

                            // 清空現有列表
                            $('#foreignToTWD, #TWDToForeign').empty();

                            // 產生各國轉台幣的列表
                            foreignToTWD.forEach(function(item) {
                                const result = `${amount} ${flagEmoji[item.from]}${item.from} = ${(amount * item.rate).toFixed(2)} ${flagEmoji[item.to]}${item.to}`;
                                $('#foreignToTWD').append(`<li>${result}</li>`);
                            });

                            // 產生台幣轉各國的列表
                            TWDToForeign.forEach(function(item) {
                                const result = `${amount} ${flagEmoji[item.from]}${item.from} = ${(amount * item.rate).toFixed(2)} ${flagEmoji[item.to]}${item.to}`;
                                $('#TWDToForeign').append(`<li>${result}</li>`);
                            });
                        }

                        // 監聽輸入框變化
                        $('#moneyInput').on('input', function() {
                            updateResults(this.value);
                        });
                    });
                </script>
                <?php endif; ?>
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">基本使用方式</h5>
                        <p class="card-text">在上方輸入一個數字</p>
                        <p class="card-text">即可顯示該金額的五國貨幣(日幣、韓元、美金、歐元、港幣)轉成台幣的匯率</p>
                        <p class="card-text">亦會顯示該金額的台幣轉成五國貨幣(日幣、韓元、美金、歐元、港幣)的匯率</p>
                        <div class="alert alert-info mt-4">
                            <h5 class="alert-heading">✨ 進階用法</h5>
                            <p class="mb-0">使用 Firefox 將此頁加入搜尋引擎，並且將此設為預設搜尋引擎，<br>接著只要在 Firefox Search Bar 輸入金額，加上等號即可顯示結果！<br><span class="text-muted">(如果不是數字和等號的格式，會跳轉到 Google 搜尋)</span></p>
                        </div>
                    </div>
                </div>
                <div class="text-muted mt-4">
                    <p>匯率更新時間：<span id="updatedAt"></span></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
