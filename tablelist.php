<?php
/**
 * 生成mysql数据字典
 */
header ( "Content-type: text/html; charset=utf-8" );
 
// 配置数据库
$dbserver = "127.0.0.1";
$dbusername = "root";
$dbpassword = "caicai";
$database = $_GET["db"] ?? 'ok';
$dsn = 'mysql:dbname='.$database.';host='.$dbserver;
// 其他配置
$title = $database.'.数据字典';

try {
    $dbh = new PDO($dsn, $dbusername, $dbpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'utf8';"));
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
$sql = 'show tables';
$table_list = $dbh->prepare($sql);
$table_list->execute();

// 取得所有的表名
while ( $row = $table_list->fetch(PDO::FETCH_NUM) ) {
    $tables [] ['TABLE_NAME'] = $row [0];
}
$table_count = count($tables);

 
// 循环取得所有表的备注及表中列消息
foreach ( $tables as $k => $v ) {
    $sql = 'SELECT ';
    $sql .='*, concat(truncate(data_length/1024/1024,2),\'MB\') as data_size, concat(truncate(index_length/1024/1024,2),\'MB\') as index_size ';
    $sql .='FROM ';
    $sql .= 'INFORMATION_SCHEMA.TABLES ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
    $table_result = $dbh->prepare($sql);
    $table_result->execute();
    while ( $t = $table_result->fetch(PDO::FETCH_ASSOC) ) {
        $tables [$k] ['TABLE_COMMENT'] = $t ['TABLE_COMMENT'];
        $tables [$k] ['TABLE_ROWS'] = $t ['TABLE_ROWS'];
        $tables [$k] ['data_size'] = $t ['data_size'];
        $tables [$k] ['index_size'] = $t ['index_size'];
    }
     
    $sql = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";
    $fields = array();
    $field_result = $dbh->prepare($sql);
    $field_result->execute();
    while ( $t = $field_result->fetch(PDO::FETCH_ASSOC) ) {
        $fields [] = $t;
    }
    $tables [$k] ['COLUMN'] = $fields;
}
$dsn = NULL;

$html = '';
// 循环所有表
foreach ( $tables as $k => $v ) {
    // $html .= '<p><h2>'. $v['TABLE_COMMENT'] . '&nbsp;</h2>';
    $html .= '<table  border="1" cellspacing="0" cellpadding="0" align="center">';
    $html .= '<caption>' . $v ['TABLE_NAME'] . '  ' . $v ['TABLE_COMMENT'] . '<captioninfo>' . '        <BR>Rows:' . $v ['TABLE_ROWS'] . ' Size:' . $v ['data_size'] . ' IndexSize:' . $v ['index_size'] . '</captioninfo>' . '</caption>';
    $html .= '<tbody>
                     <tr>
                         <th>字段名</th>
                         <th>注释</th>
                         <th>数据类型</th>
                         <th>主键</th>
                         <th>允许为空</th>
                         <th>默认值</th>
                         <th>自动递增</th>
                     </tr>';
    $html .= '';
     
    foreach ( $v ['COLUMN'] as $f ) {
        $html .= '<tr>';
        $html .= '<td class="c1">' . $f ['COLUMN_NAME'] . '</td>';
        $html .= '<td class="c2">' . $f ['COLUMN_COMMENT'] . '</td>';
        $html .= '<td class="c3">' . $f ['COLUMN_TYPE'] . '</td>';
        $html .= '<td class="c4">' . $f ['COLUMN_KEY'] . '</td>';
        $html .= '<td class="c5">' . $f ['IS_NULLABLE'] . '</td>';
        $html .= '<td class="c6">' . $f ['COLUMN_DEFAULT'] . '</td>';
        $html .= '<td class="c7">' . ($f ['EXTRA'] == 'auto_increment' ? '是' : '&nbsp;') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></p>';
}
 
// 输出
echo '<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>' . $title . '</title>
<style>
body,td,th {font-family:"宋体"; font-size:12px;}
table{border-collapse:collapse;border:1px solid #CCC;background:#6089D4;}
table caption{text-align:left; background-color:#fff; line-height:2em; font-size:18px; font-weight:bold; }
table captioninfo{text-align:left; background-color:#fff; line-height:1em; font-size:12px; font-weight:none; }
table th{text-align:left; font-weight:bold;height:26px; line-height:25px; font-size:16px; border:3px solid #fff; color:#ffffff; padding:5px;}
table td{height:25px; font-size:12px; border:3px solid #fff; background-color:#f0f0f0; padding:5px;}
.c1{ width: 150px;}
.c2{ width: 300px;}
.c3{ width: 130px;}
.c4{ width: 35px;}
.c5{ width: 70px;}
.c6{ width: 100px;}
.c7{ width: 70px;}
</style>
</head>
<body>';
echo '<h1 style="text-align:center;">' . $title . '</h1>';
echo '<div style="background:#f3f3f3;text-align:center;">（注：共'.$table_count.'张表，按ctrl+F查找关键字）</div>';
echo $html;
echo '</body></html>';
 
?>
