<?php

// --- 1. الإعدادات الأساسية ---
$botToken = "8628823665:AAFRpccVFIxqT7OzbaTcFMjGpaX5kHVD6YE";
$chatId = "@XFY_F"; 
$adminId = 7996191937; // آيدي نجيب
$databaseFile = "published_links.txt"; 
$usersFile = "users.txt"; 
$banFile = "banned_users.txt"; // ملف المحظورين

// قائمة الكلمات السيئة (يمكنك إضافة أي كلمات تريد منعها هنا)
$badWords = ["سكس", "اباحي", "فضيحة", "بنات", "تعارف", "شات", "قمار"];

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

if (isset($update["message"])) {
    $message = $update["message"];
    $text = trim($message["text"]);
    $userId = $message["from"]["id"];
    $firstName = $message["from"]["first_name"];
    $username = isset($message["from"]["username"]) ? "@" . $message["from"]["username"] : $firstName;

    // --- فحص هل المستخدم محظور ---
    $bannedUsers = file_exists($banFile) ? file($banFile, FILE_IGNORE_NEW_LINES) : [];
    if (in_array($userId, $bannedUsers)) {
        exit; // إذا كان محظوراً، البوت يتجاهله تماماً
    }

    // حفظ المستخدم الجديد للإذاعة
    if (!file_exists($usersFile) || !in_array($userId, file($usersFile, FILE_IGNORE_NEW_LINES))) {
        file_put_contents($usersFile, $userId . PHP_EOL, FILE_APPEND);
    }

    // --- 2. أوامر المدير (نجيب) ---
    if ($text == "/admin" && $userId == $adminId) {
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📊 إحصائيات', 'callback_data' => 'stats'], ['text' => '🧹 تصفير المكرر', 'callback_data' => 'clear_links']],
                [['text' => '📢 إذاعة للكل', 'callback_data' => 'broadcast_msg']],
                [['text' => '🚫 قائمة المحظورين', 'callback_data' => 'list_banned']]
            ]
        ];
        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$userId&text=" . urlencode("🕹 **لوحة تحكم الإمبراطور نجيب**") . "&reply_markup=" . json_encode($keyboard));
        exit;
    }

    // أمر الحظر (للمدير فقط): اكتب (حظر: آيدي_المستخدم)
    if (strpos($text, "حظر:") !== false && $userId == $adminId) {
        $targetId = trim(explode(":", $text)[1]);
        file_put_contents($banFile, $targetId . PHP_EOL, FILE_APPEND);
        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$userId&text=" . urlencode("✅ تم حظر المستخدم $targetId بنجاح."));
        exit;
    }

    // --- 3. نظام النشر الذكي مع الفلترة ---
    if ($text == "/start") {
        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$userId&text=" . urlencode("أهلاً بك في @YemenLinksBot 🇾🇪\nأرسل رابط واتساب لنشره فوراً في القناة!"));
    } 
    elseif (strpos($text, 'chat.whatsapp.com') !== false || strpos($text, 'whatsapp.com/channel') !== false) {
        
        // فحص الكلمات السيئة في النص
        $isBad = false;
        foreach ($badWords as $word) {
            if (strpos($text, $word) !== false) { $isBad = true; break; }
        }

        if ($isBad) {
            file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$userId&text=" . urlencode("❌ عذراً يا $firstName، الرابط يحتوي على كلمات غير مسموح بها!"));
            // إبلاغ المدير بمحاولة نشر سيئة
            file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$adminId&text=" . urlencode("⚠️ تنبيه: حاول $username نشر رابط سيء.\nالآيدي الخاص به: $userId"));
        } else {
            // [هنا كود النشر الاحترافي الذي استخدمناه سابقاً]
            $publishedLinks = file_exists($databaseFile) ? file($databaseFile, FILE_IGNORE_NEW_LINES) : [];
            if (in_array($text, $publishedLinks)) {
                file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$userId&text=" . urlencode("⚠️ الرابط مكرر!"));
            } else {
                $caption = "💎 **إضافة جديدة وحصرية!** 💎\n━━━━━━━━━━━━━\n\n📢 **النوع:** واتساب 🇾🇪\n👤 **بواسطة:** $username\n🔗 **الرابط:** $text\n\n━━━━━━━━━━━━━\n📡 @YemenLinksBot | @XFY_F";
                file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($caption) . "&parse_mode=Markdown");
                file_put_contents($databaseFile, $text . PHP_EOL, FILE_APPEND);
                file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$userId&text=" . urlencode("✅ تم النشر بنجاح!"));
            }
        }
    } else {
        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$userId&text=" . urlencode("❌ أرسل رابط واتساب صحيح فقط!"));
    }
}

// --- 4. معالجة الأزرار ---
if (isset($update["callback_query"])) {
    $callbackData = $update["callback_query"]["data"];
    $fromId = $update["callback_query"]["from"]["id"];
    if ($fromId == $adminId) {
        if ($callbackData == 'stats') {
            $u = count(file($usersFile)); $l = count(file($databaseFile));
            file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?callback_query_id=" . $update["callback_query"]["id"] . "&text=" . urlencode("👥 $u | 🔗 $l"));
        }
        // ... (بقية الأزرار)
    }
}
