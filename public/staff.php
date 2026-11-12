<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/staff_accounts.php';
require_once dirname(__DIR__).'/views/layout.php';
require_once dirname(__DIR__).'/views/request_ui.php';
start_application_session(); header('Cache-Control: no-store');
try {
    $db=database_connection(application_config()['database']);
    if (current_user($db)===null) { redirect('/login.php'); }
    staff_actor($db);
    if (!in_array($_SERVER['REQUEST_METHOD'],['GET','POST'],true)) { http_response_code(405); header('Allow: GET, POST'); exit; }
    $q=$_GET['q']??''; $page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>10000]]);
    if (!is_string($q) || mb_strlen($q)>100 || $page===false) { throw new InvalidArgumentException('Choose a valid search and page.'); }
    $errors=[]; $values=['username'=>'','display_name'=>'','role'=>'librarian']; $failed=null; $roleValue=''; $statusValue='';
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        try {
            $action=$_POST['action']??null;
            $allowed=$action==='create'?['action','username','display_name','role','csrf_token']:['action','user_id','role','status','version','csrf_token'];
            if (array_diff(array_keys($_POST),$allowed) || !in_array($action,['create','update'],true)) { throw new InvalidArgumentException('Unexpected form fields. Refresh the page.'); }
            if ($action==='create') {
                foreach ($values as $key=>$value) { $values[$key]=is_string($_POST[$key]??null)?$_POST[$key]:''; }
                try { normalize_username($_POST['username']??null); } catch(InvalidArgumentException $e) {$errors['username']=$e->getMessage();}
                if (!is_string($_POST['display_name']??null) || mb_strlen(trim($values['display_name']))<1 || mb_strlen(trim($values['display_name']))>100) { $errors['display_name']='Enter a display name of 1–100 characters.'; }
                if (!in_array($values['role'],['librarian','administrator'],true)) { $errors['role']='Choose a staff role.'; }
                if ($errors) { throw new InvalidArgumentException('Check the highlighted fields.'); }
                staff_create($db,$_POST['username'],$_POST['display_name'],$_POST['role'],$_POST['csrf_token']??null);
                flash_set('success','Staff account created. Password not set—sign-in unavailable until the local operator assigns it.');
            } else {
                $id=filter_var($_POST['user_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
                $version=filter_var($_POST['version']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
                if ($id===false || $version===false) { throw new InvalidArgumentException('Choose a valid staff account.'); }
                $failed=$id; $roleValue=is_string($_POST['role']??null)?$_POST['role']:''; $statusValue=is_string($_POST['status']??null)?$_POST['status']:'';
                $result=staff_update($db,$id,$_POST['role']??null,$_POST['status']??null,$version,$_POST['csrf_token']??null);
                if (!isset($_SESSION['user_id'])) { redirect('/login.php'); }
                flash_set('success',$result==='unchanged'?'Account already has these settings. No change was recorded.':'Staff access updated. Previous sessions were revoked.');
            }
            redirect('/staff.php?'.http_build_query(['q'=>$q,'page'=>$page]));
        } catch(InvalidArgumentException $e) { http_response_code(400); if (!$errors) {$errors[$failed!==null?'staff-'.$failed:'']=$e->getMessage();} }
    }
    $rows=staff_list($db,$page,$q,$pagination); $page=$pagination['page'];
    if ($failed!==null && !in_array($failed,array_map('intval',array_column(array_slice($rows,0,20),'user_id')),true) && isset($errors['staff-'.$failed])) {
        $errors=[''=>$errors['staff-'.$failed]];
    }
    $html='<section class="panel"><h1>Staff accounts</h1>'.form_error_summary($errors).'<p>Manage website staff access. Administrators do not have circulation permissions. Accounts and history are preserved.</p><form method="get"><label for="q">Search username or name</label><input id="q" name="q" maxlength="100" value="'.h($q).'"><button>Search</button> <a href="/staff.php">Clear</a></form><details class="workspace-create"'.(($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??null)==='create')?' open':'').'><summary>Create staff account</summary><p>New accounts need a password assigned privately by the local operator before sign-in.</p><form method="post" novalidate><input type="hidden" name="action" value="create"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'">';
    foreach (['username'=>'Username (3–32 letters, numbers or underscores)','display_name'=>'Display name (1–100 characters)'] as $key=>$label) {
        $html.='<label for="'.h($key).'">'.h($label).'</label><input id="'.h($key).'" name="'.h($key).'" required maxlength="'.($key==='username'?32:100).'" value="'.h($values[$key]).'">'.field_error_html($errors,$key);
    }
    $html.='<label for="role">Role</label><select id="role" name="role">';
    foreach (['librarian','administrator'] as $role) {$html.='<option value="'.$role.'"'.($values['role']===$role?' selected':'').'>'.ucfirst($role).'</option>';}
    $html.='</select>'.field_error_html($errors,'role').'<button>Create staff account</button></form></details>';
    if (!$rows) {$html.='<p>No staff accounts on this page. Clear the search or return to the first page.</p>';}
    foreach (array_slice($rows,0,20) as $row) {
        $id=(int)$row['user_id']; $selectedRole=$failed===$id?$roleValue:$row['role']; $selectedStatus=$failed===$id?$statusValue:$row['account_status'];
        $html.='<article class="management-section" id="staff-'.$id.'" tabindex="-1"><h2>'.h($row['display_name']).'</h2><p>'.h($row['username']).' · '.h(ucfirst($row['role'])).' · '.h(ucfirst($row['account_status'])).'</p><p>'.((bool)$row['password_set']?'Password set.':'Password not set—sign-in unavailable.').'</p>';
        if (in_array($row['account_status'],['active','suspended'],true)) {
            $html.='<form method="post"><input type="hidden" name="action" value="update"><input type="hidden" name="user_id" value="'.$id.'"><input type="hidden" name="version" value="'.h($row['session_version']).'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><label for="role-'.$id.'">Role for '.h($row['username']).'</label><select id="role-'.$id.'" name="role">';
            foreach (['librarian','administrator'] as $role) {$html.='<option value="'.$role.'"'.($selectedRole===$role?' selected':'').'>'.ucfirst($role).'</option>';}
            $html.='</select><label for="status-'.$id.'">Access for '.h($row['username']).'</label><select id="status-'.$id.'" name="status">';
            foreach (['active','suspended'] as $status) {$html.='<option value="'.$status.'"'.($selectedStatus===$status?' selected':'').'>'.ucfirst($status).'</option>';}
            $html.='</select><p>Changes revoke existing sessions. Changing your own role or access signs you out.</p><button aria-label="'.h('Save access for '.$row['username']).'">Save staff access</button></form>';
        }
        $html.='</article>';
    }
    $html .= pagination_html($pagination, static fn(int $target): string => '?'.http_build_query(['q'=>$q,'page'=>$target]), 'Staff pages', 'accounts');
    render_page('Staff accounts',$html.'</section>');
} catch(DomainException $e) { http_response_code(403); render_page('Access denied','<p role="alert">'.h($e->getMessage()).'</p>'); }
catch(InvalidArgumentException $e) { http_response_code(400); render_page('Check staff accounts','<p role="alert">'.h($e->getMessage()).'</p><a href="/staff.php">Staff accounts</a>'); }
catch(Throwable $e) { $ref=bin2hex(random_bytes(8)); error_log('Staff failure ['.$ref.'] code='.$e->getCode()); http_response_code(500); render_page('Staff unavailable','<p role="alert">Unable to complete the action. Refresh before retrying. Reference: '.h($ref).'</p>'); }
