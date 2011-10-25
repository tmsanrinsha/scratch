<?php
//Wikiで#を使ってリスト表示していたものをh6. <番号>.に変えるプログラム
$handle = @fopen("./inputfile.txt", "r");
if ($handle) {
    $i = 1;
    while (($buffer = fgets($handle, 4096)) !== false) {
        echo preg_replace('/^#[^!]/', "h6. $i.", $buffer, 1, $count);
        //echo "$buffer";
        $i += $count;
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}
?>
