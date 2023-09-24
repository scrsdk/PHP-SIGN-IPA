<?php

// Путь к папке для сохранения подписанных ipa и plist файлов
$savePath = 'save/u/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certificate = $_FILES['certificate']['tmp_name'];
    $mobileprovision = $_FILES['mobileprovision']['tmp_name'];
    $ipa = $_FILES['ipa']['tmp_name'];
    $pass = $_POST['pass'];

    // Подписываем .ipa файл с помощью zsign
    $signedIpa = $savePath . '/signed_' . $_FILES['ipa']['name'];
    exec("zsign -k $certificate -m $mobileprovision -s \"$pass\" -o $signedIpa $ipa");

    // Создаем .plist файл
    $plist = <<<PLIST
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>https://ВАШСАЙТ/{$signedIpa}</string>
                </dict>
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>com.example.app</string>
                <key>bundle-version</key>
                <string>1.0</string>
                <key>kind</key>
                <string>software</string>
                <key>title</key>
                <string>App</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
PLIST;

    $plistPath = $savePath . '/app.plist';
    file_put_contents($plistPath, $plist);

    // Отправляем ссылку на установку через plist
    $installLink = "itms-services://?action=download-manifest&url=https://ВАШСАЙТ/{$plistPath}"; // Смените ссылку на сайт
    echo "Подписанный .ipa доступен по ссылке: <a href=\"$installLink\">ссылка на установку</a><br>";

    // Сохраняем ссылку на сервере
    $ipaLink = $savePath . '/link.txt';
    file_put_contents($ipaLink, $installLink);
    echo "Ссылка на установку сохранена на сервере.";
}

?>