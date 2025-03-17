<?
$currency_json = json_decode(file_get_contents("currency.json"), true);
$flag_emoji_map = [
    "TWD" => "🇹🇼",
    "JPY" => "🇯🇵",
    "KRW" => "🇰🇷",
    "USD" => "🇺🇸",
    "EUR" => "🇪🇺",
    "HKD" => "🇭🇰",
];
if($currency_json["date"] != date("Y-m-d")){
    $batch_list = [
        ["from" => "JPY", "to" => "TWD", "amount" => 1],
        ["from" => "KRW", "to" => "TWD", "amount" => 1],
        ["from" => "USD", "to" => "TWD", "amount" => 1],
        ["from" => "EUR", "to" => "TWD", "amount" => 1],
        ["from" => "HKD", "to" => "TWD", "amount" => 1],
        //台幣轉日幣、韓元、美金、歐元、港幣 
        ["from" => "TWD", "to" => "JPY", "amount" => 1],
        ["from" => "TWD", "to" => "KRW", "amount" => 1],
        ["from" => "TWD", "to" => "USD", "amount" => 1],
        ["from" => "TWD", "to" => "EUR", "amount" => 1],
        ["from" => "TWD", "to" => "HKD", "amount" => 1],
    ];
    foreach($batch_list as $i=>$batch){
        $url = sprintf("https://tw.valutafx.com/LookupRate.aspx?to=%s&from=%s&amount=%s&offset=-480&fi=", $batch["to"], $batch["from"], $batch["amount"]);
        $json = json_decode(file_get_contents($url), true);
        $rate = $json["Rate"];
        $batch_list[$i]["rate"] = $rate;
    }
    $currency_json = [
        "date" => date("Y-m-d"),
        "currency" => $batch_list
    ];
    file_put_contents("currency.json", json_encode($currency_json));
}
function genSuggestQueries($amount){
    global $currency_json, $flag_emoji_map;
    $suggest_queries = [];
    foreach($currency_json["currency"] as $currency){
        $suggest_queries[] = $amount." ".$flag_emoji_map[$currency["from"]].$currency["from"]." = " . $amount*$currency["rate"]." ".$flag_emoji_map[$currency["to"]].$currency["to"];
    }
    return $suggest_queries;
}
?>
