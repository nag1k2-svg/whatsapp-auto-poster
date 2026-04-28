<?php
// إخبار السيرفر أننا استلمنا الطلب بنجاح
http_response_code(200); 

// تعريف المنفذ لـ Railway
$port = getenv('PORT') ?: 8080;

/**
 * 👑 نظام محرك الروابط الملكي 🇾🇪
 * المطور والمالك الرسمي: البرنس نجيب
 * جميع الحقوق محفوظة لـ @nag1k2
 */

// --- 1. الإعدادات الجوهرية (التوكن الجديد) ---
$token = "8628823665:AAES9G97DmPQF5IKeEW8TQ0d3nli9vtoy_4"; 
$admin_id = 7996191937; // آيدي البرنس نجيب
$channel = "@XFY_F";   // قناة النشر الرسمية

// قاعدة البيانات النصية كما تظهر في مستودعك
$dbUsers = 'users.txt';
$dbLinks = 'published_links.txt';

// استقبال البيانات الواردة
$input = file_get_contents("php://input");
$update = json_decode($input, TRUE);

// واجهة السيرفر عند الفحص من المتصفح
if (!$update) {
    echo "<center><h1>🛡️ نظام البرنس نجيب يعمل بأمان ✅</h1></center>";
    echo "<center><p>السيرفر متصل تماماً ومنتظر أوامرك يا برنس.</p></center>";
    exit;
}

// دالة الإرسال الأكيدة لضمان استقرار البوت
function telegram($method, $data) {
    global $token;
    $url = "https://api.telegram.org/bot$token/$method";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

if (isset($update["message"])) {
    $msg = $update["message"];
    $chat_id = $msg["chat"]["id"];
    $text = trim($msg["text"]);
    $name = htmlspecialchars($msg["from"]["first_name"]);

    // حفظ المستخدمين تلقائياً
    if (!file_exists($dbUsers)) file_put_contents($dbUsers, "");
    $allUsers = file_get_contents($dbUsers);
    if (strpos($allUsers, (string)$chat_id) === false) {
        file_put_contents($dbUsers, $chat_id . PHP_EOL, FILE_APPEND);
    }

    // --- نظام الأوامر ---
    if ($text == "/start") {
        telegram('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "👑 أهلاً بك في نظام البرنس نجيب لنشر الروابط 🇾🇪\n\nأرسل رابط الواتساب الآن ليتم نشره فوراً في القناة الرسمية @XFY_F"
        ]);
    } 
    
    // فلترة ونشر روابط الواتساب
    elseif (preg_match('/chat.whatsapp.com|whatsapp.com\/channel/', $text)) {
        
        if (!file_exists($dbLinks)) file_put_contents($dbLinks, "");
        $oldLinks = file_get_contents($dbLinks);

        if (strpos($oldLinks, $text) !== false) {
            telegram('sendMessage', ['chat_id' => $chat_id, 'text' => "📌 هذا الرابط سبق وأن تم نشره في القناة."]);
        } else {
            // صياغة المنشور الملكي المعتمدة
            $post = "🔗 **رابط واتساب جديد تم نشره**\n";
            $post .= "━━━━━━━━━━━━━━\n";
            $post .= "👤 **بواسطة المطور:** البرنس نجيب\n\n";
            $post .= "$text\n";
            $post .= "━━━━━━━━━━━━━━\n";
            $post .= "🤖 البوت الرسمي: @YemenLinksBot\n";
            $post .= "✅ القناة: $channel";

            $send = telegram('sendMessage', [
                'chat_id' => $channel,
                'text' => $post,
                'parse_mode' => 'Markdown'
            ]);

            if ($send['ok']) {
                file_put_contents($dbLinks, $text . PHP_EOL, FILE_APPEND);
                telegram('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ تم النشر بنجاح يا بطل! تابع القناة الآن."]);
            } else {
                telegram('sendMessage', ['chat_id' => $chat_id, 'text' => "⚠️ تأكد من رفع البوت 'مشرف' في القناة ليعمل النشر."]);
            }
        }
    }
}
