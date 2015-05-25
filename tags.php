<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/wp-config.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_errno) {
    exit();
}

$query = <<<EOF
SELECT 
  LOWER(t.name) AS name,
  CONCAT('http://www.hascode.com/',IF(tax.taxonomy='post_tag', 'tag', 'category'),'/', t.slug) AS url,
  COUNT(p.ID) AS articles
FROM wp_terms t
INNER JOIN wp_term_taxonomy tax
ON t.term_id=tax.term_id
INNER JOIN wp_term_relationships rel
ON tax.term_taxonomy_id=rel.term_taxonomy_id
INNER JOIN wp_posts p
ON rel.object_id=p.ID
WHERE p.post_type='post'
AND p.post_status='publish'
GROUP BY 1
ORDER BY t.name ASC
EOF;

if (!($stmt = $mysqli->prepare($query))) {
        exit();
}

if (!$stmt->execute()) {
        exit();
}

$stmt->bind_result($name, $url, $articles);

$root = array();

while ($stmt->fetch()) {
        array_push($root, array('name' => $name, 'url' => $url, articles => $articles));
}
echo json_encode($root);
$stmt->close();
$mysqli->close();
?>
