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

$query = <<<EOF
SELECT 
  p.post_title AS title,
  REPLACE(p.guid, 'http://student.in/xyz/','http://www.hascode.com/') AS url,
  IF(att.guid IS NOT NULL, att.guid, 'http://www.hascode.com/wp-content/themes/l2aelba-2/images/head.png') AS image,
  CONCAT(SUBSTRING_INDEX(SUBSTR(p.post_content, 1, 600), '<!--more-->', 1), "") AS excerpt
FROM wp_posts p
INNER JOIN wp_term_relationships rel
 ON p.ID=rel.object_id
INNER JOIN wp_term_taxonomy tax
 ON rel.term_taxonomy_id=tax.term_taxonomy_id
INNER JOIN wp_terms t
 ON tax.term_id=t.term_id
LEFT JOIN wp_posts att 
 ON att.post_parent=p.ID
 AND att.post_type='attachment'
LEFT JOIN wp_postmeta meta
 ON att.ID=meta.meta_value
 AND meta.meta_key='_thumbnail_id'
WHERE p.post_type='post'
AND p.post_status='publish'
AND t.name=?
GROUP BY p.ID
ORDER BY p.post_title ASC
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

$stmt->bind_result($title, $url, $image, $excerpt);

$root = array();

while ($stmt->fetch()) {
        array_push($root, array('title' => $title, 'url' => $url, 'image' => $image, 'excerpt' => strip_tags($excerpt)));
}
echo json_encode($root);
$stmt->close();
$mysqli->close();
?>
