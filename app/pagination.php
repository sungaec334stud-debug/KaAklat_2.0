<?php
declare(strict_types=1);

function pagination_state(int $total, int $page, int $size, int $maximum = 10000): array
{
    if ($total < 0 || $page < 1 || $page > $maximum || $size < 1 || $maximum < 1) {
        throw new InvalidArgumentException('Choose a valid page.');
    }
    $pages = max(1, min($maximum, (int) ceil($total / $size)));
    return ['total' => $total, 'page' => min($page, $pages), 'total_pages' => $pages];
}

// SQL and ordering are internal constants; callers validate filters and authorize first.
function pagination_rows(PDO $db, string $sql, array $parameters, string $order, int $page, int $size, ?array &$pagination = null, int $maximum = 10000, bool $clamp = true): array
{
    pagination_state(0, $page, $size, $maximum);
    $count = $db->prepare('SELECT COUNT(*) FROM (' . $sql . ') pagination_matches');
    $count->execute($parameters);
    $pagination = pagination_state((int) $count->fetchColumn(), $page, $size, $maximum);
    // Retain the existing lookahead row for service callers using has-next checks.
    $statement = $db->prepare($sql . ' ORDER BY ' . $order . ' LIMIT ' . ($size + 1)
        . ' OFFSET ' . ((($clamp ? $pagination['page'] : $page) - 1) * $size));
    $statement->execute($parameters);
    return $statement->fetchAll();
}
