<?php
declare(strict_types=1);

/** Only these internal keys may select SQL ordering or lifecycle predicates. */
function circulation_sort_choices(string $kind): array
{
    return match($kind) {
        'review'=>['oldest'=>'Oldest request first','newest'=>'Newest request first','shortest'=>'Shortest requested period first','longest'=>'Longest requested period first'],
        'pickups'=>['earliest'=>'Nearest deadline first','latest'=>'Farthest deadline first'],
        'returns'=>['earliest'=>'Earliest due date first','latest'=>'Latest due date first'],
        default=>throw new InvalidArgumentException('Choose a valid circulation list.'),
    };
}

function circulation_status_choices(string $kind): array
{
    return match($kind) {
        'review'=>['all'=>'All pending requests'],
        'pickups'=>['all'=>'All outstanding pickups','ready'=>'Ready for pickup','expired'=>'Expired'],
        'returns'=>['all'=>'All outstanding loans','overdue'=>'Overdue','due_today'=>'Due today','active'=>'Active — not overdue'],
        default=>throw new InvalidArgumentException('Choose a valid circulation list.'),
    };
}

function circulation_list_options(string $kind, array $input): array
{
    $sorts=circulation_sort_choices($kind);$statuses=circulation_status_choices($kind);
    $sort=$input['sort'] ?? array_key_first($sorts);$status=$input['status'] ?? 'all';
    if(!is_string($sort)||!isset($sorts[$sort])||!is_string($status)||!isset($statuses[$status])) {
        throw new InvalidArgumentException('Choose a valid status and sort order. Clear the filters to start again.');
    }
    return ['status'=>$status,'sort'=>$sort];
}
