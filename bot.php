<?php
$botToken = "8628823665:AAFRpccVFIxqT7OzbaTcFMjGpaX5kHVD6YE";
$chatId = "@XFY_F";

$links = [
    "🔗 قروبات واتساب صنعاء بيع وشراء 🇾🇪\nhttps://chat.whatsapp.com/Example1",
    "🔗 وظائف شاغرة في اليمن محدثة يومياً 💼\nhttps://chat.whatsapp.com/Example2",
    "🔗 قروبات واتساب عمر والتقنية 📱\nhttps://chat.whatsapp.com/Example3"
];

$randomLink = $links[array_rand($links)];
$caption = "🔥 **تحديث الروابط اليومية** 🔥\n\n" . $randomLink . "\n\n#روابط_واتساب #اليمن #XFY_F";

$url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($caption) . "&parse_mode=Markdown";

file_get_contents($url);
echo "تم النشر!";
