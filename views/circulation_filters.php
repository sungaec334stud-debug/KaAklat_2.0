<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/circulation_filters.php';

function circulation_filter_form(string $kind,string $query,array $options): string
{
    $route=match($kind){'review'=>'/review_requests.php','pickups'=>'/pickups.php','returns'=>'/returns.php'};
    $label=match($kind){'review'=>'Search pending requests by book or student','pickups'=>'Search book or student','returns'=>'Search book, student or copy code'};
    $html='<form method="get" action="'.$route.'" class="circulation-controls'.($kind==='review'?' circulation-controls-review':'').'">'
        .'<div class="circulation-filter-search"><label for="q">'.$label.'</label><input id="q" name="q" maxlength="100" value="'.h($query).'"></div>';
    if($kind!=='review'){
        $html.='<div><label for="queue-status">Status</label><select id="queue-status" name="status">';
        foreach(circulation_status_choices($kind)as$value=>$text){$html.='<option value="'.$value.'"'.($options['status']===$value?' selected':'').'>'.h($text).'</option>';}
        $html.='</select></div>';
    }
    $html.='<div><label for="queue-sort">Sort by</label><select id="queue-sort" name="sort">';
    foreach(circulation_sort_choices($kind)as$value=>$text){$html.='<option value="'.$value.'"'.($options['sort']===$value?' selected':'').'>'.h($text).'</option>';}
    $hint=match($kind){'review'=>'Borrowing-period order uses the number of days requested by the student.','pickups'=>'Status uses the collection deadline. Account eligibility is checked again at handover. Closed allocations are not listed.','returns'=>'Active includes loans due today. A loan becomes overdue after its due date has passed.'};
    return $html.'</select></div><div class="circulation-filter-actions"><button>Apply</button><a href="'.$route.'">Clear</a></div><p class="circulation-filter-hint">'.h($hint).'</p></form>';
}
