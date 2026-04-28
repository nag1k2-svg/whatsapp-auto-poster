<?php

// --- إعدادات القمة ---
define('BOT_TOKEN', "8628823665:AAFRpccVFIxqT7OzbaTcFMjGpaX5kHVD6YE");
define('CHANNEL_ID', "@XFY_F"); 
define('OWNER_ID', 7996191937); 
define('API_URL', "https://api.telegram.org/bot".BOT_TOKEN."/");

// ملفات النظام
$dbFiles = ['links' => 'links.txt', 'users' => 'users.txt', 'ban' => 'ban.txt'];
foreach($dbFiles as $f) { if(!file_exists($f)) file_put_contents($f, ""); }

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

if (!$update) exit;

// دالة الإرسال الذكية
function send($method, $data) {
    $url = API_URL . $method . "?" . http_build_query($data);
    return json_decode(file_get_contents($url), true);
}

if (isset($update["message"])) {
    $msg = $update["message"];
    $text = trim($msg["text"]);
    $chatId = $msg["chat"]["id"];
    $uid = $msg["from"]["id"];
    $name = htmlspecialchars($msg["from"]["first_name"]);
    $user = isset($msg["from"]["username"]) ? "@".$msg["from"]["username"] : $name;

    // فحص الحظر
    if (in_array($uid, file($dbFiles['ban'], 8))) exit;

    // 🕹 لوحة التحكم الملكية
    if ($text == "/admin" && $uid == OWNER_ID) {
        $stats = [
            'u' => count(file($dbFiles['users'], 8)),
            'l' => count(file($dbFiles['links'], 8)),
            'b' => count(file($dbFiles['ban'], 8))
        ];
        
        $kb = ['inline_keyboard' => [
            [['text' => "📊 الإحصائيات", 'callback_data' => 'st'], ['text' => "🧹 تنظيف", 'callback_data' => 'cl']],
            [['text' => "📢 إذاعة عامة", 'callback_data' => 'bc']],
            [['text' => "🚫 قائمة المحظورين", 'callback_data' => 'lb']]
        ]];

        send('sendMessage', [
            'chat_id' => $chatId,
            'text' => "👑 **أهلاً بك يا إمبراطور نجيب**\n\n🔹 المستخدمين: {$stats['u']}\n🔹 الروابط: {$stats['l']}\n🔹 المحظورين: {$stats['b']}\n\nنظامك يعمل بأعلى كفاءة 🚀",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($kb)
        ]);
        exit;
    }

    // 🚀 نظام النشر والذكاء الصناعي المبسط
    if ($text == "/start") {
        send('sendMessage', ['chat_id' => $chatId, 'text' => "مرحباً بك يا $name في منصة @YemenLinksBot\nأرسل رابط الواتساب لنشره عالمياً 🌍"]);
    } 
    elseif (preg_match('/(chat.whatsapp.com|whatsapp.com\/channel)/', $text)) {
        
        // فلتر الكلمات (قوي جداً)
        $blacklist = ['سكس', 'نيج', 'فضيحة', 'مطلقة', 'شات', 'تعارف', 'بنات', 'رقص'];
        foreach($blacklist as $w) {
            if (mb_stripos($text, $w) !== false) {
                send('sendMessage', ['chat_id' => $chatId, 'text' => "⚠️ عذراً، محتوى الرابط مخالف لسياسة الخصوصية."]);
                send('sendMessage', ['chat_id' => OWNER_ID, 'text' => "🚨 **محاولة اختراق فلتر!**\nالاسم: $user\nالآيدي: $uid\nالرابط: $text"]);
                exit;
            }
        }

        // فحص التكرار
        if (in_array($text, file($dbFiles['links'], 8))) {
            send('sendMessage', ['chat_id' => $chatId, 'text' => "📌 هذا الرابط محمي ومنشور مسبقاً في القناة."]);
        } else {
            // التنسيق الأسطوري
            $post = "🌟 **رابط واتساب جديد تم التحقق منه** 🌟\n";
            $post .= "━━━━━━━━━━━━━━\n";
            $post .= "💠 **المصدر:** $user\n";
            $post .= "🇾🇪 **البلد:** اليمن و الوطن العربي\n";
            $post .= "⏱ **التوقيت:** ".date("h:i A")."\n\n";
            $post .= "🔗 **رابط الانضمام:**\n$text\n";
            $post .= "━━━━━━━━━━━━━━\n";
            $post .= "🤖 انشر مجاناً: @YemenLinksBot\n";
            $post .= "✅ القناة الرسمية: ".CHANNEL_ID;

            send('sendMessage', ['chat_id' => CHANNEL_ID, 'text' => $post, 'parse_mode' => 'Markdown']);
            file_put_contents($dbFiles['links'], $text.PHP_EOL, FILE_APPEND);
            send('sendMessage', ['chat_id' => $chatId, 'text' => "🎉 مبروك يا $name! تم النشر بنجاح."]);
            
            // إضافة المستخدم للقاعدة
            $users = file($dbFiles['users'], 8);
            if(!in_array($uid, $users)) file_put_contents($dbFiles['users'], $uid.PHP_EOL, FILE_APPEND);
        }
    } else {
        send('sendMessage', ['chat_id' => $chatId, 'text' => "☝️ يرجى إرسال روابط واتساب فقط لضمان الجودة."]);
    }
}

// معالجة الأزرار التفاعلية
if (isset($update["callback_query"])) {
    $cb = $update["callback_query"];
    if ($cb["from"]["id"] == OWNER_ID) {
        if ($cb["data"] == 'st') {
            send('answerCallbackQuery', ['callback_query_id' => $cb['id'], 'text' => "النظام مستقر ✅", 'show_alert' => true]);
        }
        if ($cb["data"] == 'cl') {
            file_put_contents($dbFiles['links'], "");
            send('answerCallbackQuery', ['callback_query_id' => $cb['id'], 'text' => "تم تصفير قاعدة الروابط 🧹"]);
        }
    }
}
