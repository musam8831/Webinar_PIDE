<?php
// Simple tests for reports date range computation (mirrors computeRangeFromRequest logic)
function computeRange(array $g): ?array {
    $period = $g['period'] ?? null;
    if (!$period) return null;
    $now = new DateTime('2026-01-30'); // fixed for tests
    try {
        switch ($period) {
            case 'today':
                $start = (clone $now)->setTime(0,0,0);
                $end   = (clone $now)->setTime(23,59,59);
                return ['start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')];
            case 'week':
                if (empty($g['week_date'])) return null;
                $d = new DateTime($g['week_date']);
                $wd = (int)$d->format('N');
                $start = (clone $d)->modify('-'.($wd-1).' days')->setTime(0,0,0);
                $end = (clone $start)->modify('+6 days')->setTime(23,59,59);
                return ['start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')];
            case 'month':
                if (empty($g['month'])) return null;
                $dt = new DateTime($g['month'].'-01');
                $start = $dt->setTime(0,0,0);
                $end = (clone $dt)->modify('last day of this month')->setTime(23,59,59);
                return ['start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')];
            case 'year':
                if (empty($g['year'])) return null;
                $y = (int)$g['year'];
                $start = new DateTime(sprintf('%04d-01-01 00:00:00',$y));
                $end   = new DateTime(sprintf('%04d-12-31 23:59:59',$y));
                return ['start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')];
            case 'custom':
                if (empty($g['start_date']) || empty($g['end_date'])) return null;
                $s = new DateTime($g['start_date']);
                $e = new DateTime($g['end_date']);
                if ($s > $e) return null;
                $s->setTime(0,0,0);
                $e->setTime(23,59,59);
                return ['start'=>$s->format('Y-m-d H:i:s'),'end'=>$e->format('Y-m-d H:i:s')];
            default:
                return null;
        }
    } catch (Exception $ex) { return null; }
}

$tests = [];
$tests[] = [['period'=>'today'], ['start'=>'2026-01-30 00:00:00','end'=>'2026-01-30 23:59:59']];
$tests[] = [['period'=>'week','week_date'=>'2026-01-30'], ['start'=>'2026-01-26 00:00:00','end'=>'2026-02-01 23:59:59']];
$tests[] = [['period'=>'month','month'=>'2026-01'], ['start'=>'2026-01-01 00:00:00','end'=>'2026-01-31 23:59:59']];
$tests[] = [['period'=>'year','year'=>'2025'], ['start'=>'2025-01-01 00:00:00','end'=>'2025-12-31 23:59:59']];
$tests[] = [['period'=>'custom','start_date'=>'2026-01-10','end_date'=>'2026-01-15'], ['start'=>'2026-01-10 00:00:00','end'=>'2026-01-15 23:59:59']];

$passed = 0;
foreach ($tests as [$input, $expect]) {
    $out = computeRange($input);
    if ($out === null) {
        echo "FAIL: input=".json_encode($input)." => null\n";
        continue;
    }
    if ($out['start'] === $expect['start'] && $out['end'] === $expect['end']) {
        echo "PASS: ".json_encode($input)."\n";
        $passed++;
    } else {
        echo "FAIL: ".json_encode($input)."\n  got: ".json_encode($out)."\n  expected: ".json_encode($expect)."\n";
    }
}

echo "\nSummary: $passed/".count($tests)." passed.\n";
if ($passed !== count($tests)) exit(1);
exit(0);
