<?php
define('__ROOT__', dirname(dirname(__FILE__))); 
require_once(__ROOT__.'/wp-config.php'); 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_errno) {
    exit();
}

$tag = mysql_real_escape_string($_GET['tag']);

//TODO: image url: IF(m.meta_key='_thumbnail_id', m.meta_value, 'http://www.hascode.com/wp-content/themes/l2aelba-2/images/head.png') AS image

$query = <<<EOF
SELECT
 p.post_title AS title,
 SUBSTRING_INDEX(p.post_content, '<!--more-->', 1) AS content,
 REPLACE(p.guid, 'http://student.in/xyz/','http://www.hascode.com/') AS url,
 'http://www.hascode.com/wp-content/themes/l2aelba-2/images/head.png' AS image
FROM wp_terms t
LEFT JOIN wp_term_relationships r
 ON t.term_id=r.term_taxonomy_id
LEFT JOIN wp_term_taxonomy tx
 ON t.term_id=tx.term_id
LEFT JOIN wp_posts p
 ON r.object_id=p.ID
LEFT JOIN wp_postmeta m
 ON p.ID=m.post_id
WHERE p.post_type='post'
AND p.post_status='publish'
AND t.name = ?
GROUP BY p.ID
ORDER BY p.post_title ASC;
EOF;


if (!($stmt = $mysqli->prepare($query))) {
	exit();
}

if (!$stmt->bind_param("s", $tag)) {
	exit();
}

if (!$stmt->execute()) {
	exit();
}

$stmt->bind_result($title, $content, $url, $image);

$root = array();

while ($stmt->fetch()) {
	$entry = array('title' => $title, 'content' => $content, 'url' => $url, 'image' => $image);
	array_push($root, $entry);
}
echo json_encode($root);
$stmt->close();
$mysqli->close();
?>
