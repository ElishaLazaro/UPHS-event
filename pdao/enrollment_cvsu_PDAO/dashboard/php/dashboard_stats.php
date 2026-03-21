<?php

session_start();

include '../../php/ConnectToDb.php';

if (!$_SESSION['loggedin']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]);
    exit();
}

$type = $_GET['type'] ?? 'all';

function qAll($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) return [];
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    $result->free();
    return $rows;
}

function qVal($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) return 0;
    $row = $result->fetch_row();
    $result->free();
    return $row ? $row[0] : 0;
}

switch ($type) {

    case 'gender':
        $rows = qAll($conn, "SELECT sex, COUNT(*) AS cnt FROM enrollees GROUP BY sex");
        $male = 0;
        $female = 0;
        $total = 0;
        foreach ($rows as $r) {
            $total += $r['cnt'];
            if (strtolower($r['sex']) === 'male')   $male   = (int)$r['cnt'];
            if (strtolower($r['sex']) === 'female') $female = (int)$r['cnt'];
        }
        echo json_encode(['total' => $total, 'male' => $male, 'female' => $female]);
        break;

    case 'disability':
        $rows = qAll($conn, "
            SELECT disability_type, COUNT(*) AS cnt
            FROM enrollees
            WHERE disability_type IS NOT NULL AND disability_type != ''
            GROUP BY disability_type
            ORDER BY cnt DESC
        ");
        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = $r['disability_type'];
            $data[] = (int)$r['cnt'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data, 'total' => array_sum($data)]);
        break;

    case 'age':
        $rows = qAll($conn, "
            SELECT
                CASE
                    WHEN age BETWEEN 0  AND 6  THEN '0-6'
                    WHEN age BETWEEN 7  AND 12 THEN '7-12'
                    WHEN age BETWEEN 13 AND 17 THEN '13-17'
                    WHEN age BETWEEN 18 AND 24 THEN '18-24'
                    WHEN age BETWEEN 25 AND 35 THEN '25-35'
                    ELSE '36+'
                END AS age_group,
                COUNT(*) AS cnt
            FROM enrollees
            GROUP BY age_group
            ORDER BY MIN(age)
        ");
        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = $r['age_group'];
            $data[] = (int)$r['cnt'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
        break;

    case 'level':
        $rows = qAll($conn, "
            SELECT educational_attainment AS lvl, COUNT(*) AS cnt
            FROM enrollees
            WHERE educational_attainment IS NOT NULL AND educational_attainment != ''
            GROUP BY educational_attainment
            ORDER BY cnt DESC
            LIMIT 10
        ");
        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = $r['lvl'];
            $data[] = (int)$r['cnt'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
        break;

    case 'barangay':
        $rows = qAll($conn, "
            SELECT barangay, COUNT(*) AS cnt
            FROM enrollees
            WHERE barangay IS NOT NULL AND barangay != ''
            GROUP BY barangay
            ORDER BY cnt DESC
            LIMIT 20
        ");
        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = $r['barangay'];
            $data[] = (int)$r['cnt'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
        break;

    case 'yearly':
        $rows = qAll($conn, "
            SELECT YEAR(application_date) AS yr, COUNT(*) AS cnt
            FROM enrollees
            WHERE application_date IS NOT NULL
            GROUP BY yr
            ORDER BY yr ASC
        ");
        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = $r['yr'];
            $data[] = (int)$r['cnt'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
        break;

    default:
        $gRows = qAll($conn, "SELECT sex, COUNT(*) AS cnt FROM enrollees GROUP BY sex");
        $male = 0;
        $female = 0;
        $total = 0;
        foreach ($gRows as $r) {
            $total += $r['cnt'];
            if (strtolower($r['sex']) === 'male')   $male   = (int)$r['cnt'];
            if (strtolower($r['sex']) === 'female') $female = (int)$r['cnt'];
        }

        $disTotal = (int)qVal($conn, "
            SELECT COUNT(*) FROM enrollees
            WHERE disability_type IS NOT NULL AND disability_type != ''
        ");

        $yRows = qAll($conn, "
            SELECT YEAR(application_date) AS yr, COUNT(*) AS cnt
            FROM enrollees WHERE application_date IS NOT NULL
            GROUP BY yr ORDER BY yr ASC
        ");
        $yLabels = [];
        $yData = [];
        foreach ($yRows as $r) {
            $yLabels[] = $r['yr'];
            $yData[] = (int)$r['cnt'];
        }

        $aRows = qAll($conn, "
            SELECT
                CASE
                    WHEN age BETWEEN 0  AND 6  THEN '0-6'
                    WHEN age BETWEEN 7  AND 12 THEN '7-12'
                    WHEN age BETWEEN 13 AND 17 THEN '13-17'
                    WHEN age BETWEEN 18 AND 24 THEN '18-24'
                    WHEN age BETWEEN 25 AND 35 THEN '25-35'
                    ELSE '36+'
                END AS age_group,
                COUNT(*) AS cnt
            FROM enrollees
            GROUP BY age_group ORDER BY cnt DESC LIMIT 3
        ");
        $topAges = array_map(fn($r) => ['group' => $r['age_group'], 'count' => (int)$r['cnt']], $aRows);

        $lRows = qAll($conn, "
            SELECT educational_attainment AS lvl, COUNT(*) AS cnt
            FROM enrollees WHERE educational_attainment IS NOT NULL AND educational_attainment != ''
            GROUP BY lvl ORDER BY cnt DESC LIMIT 3
        ");
        $topLevels = array_map(fn($r) => ['level' => $r['lvl'], 'count' => (int)$r['cnt']], $lRows);

        $bRows = qAll($conn, "
            SELECT barangay, COUNT(*) AS cnt
            FROM enrollees WHERE barangay IS NOT NULL AND barangay != ''
            GROUP BY barangay ORDER BY cnt DESC LIMIT 3
        ");
        $topBarangays = array_map(fn($r) => ['barangay' => $r['barangay'], 'count' => (int)$r['cnt']], $bRows);

        echo json_encode([
            'total'         => $total,
            'male'          => $male,
            'female'        => $female,
            'dis_total'     => $disTotal,
            'yearly'        => ['labels' => $yLabels, 'data' => $yData],
            'top_ages'      => $topAges,
            'top_levels'    => $topLevels,
            'top_barangays' => $topBarangays,
        ]);
        break;
}

$conn->close();
