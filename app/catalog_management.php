<?php

declare(strict_types=1);

require_once __DIR__ . '/student_accounts.php';
require_once __DIR__ . '/catalog.php';

function catalog_quantity(mixed $value): int
{
    $quantity = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]) : false;
    if ($quantity === false) { throw new InvalidArgumentException('Enter a whole copy quantity from 1 to 100.'); }
    return $quantity;
}

function catalog_book_input(array $input, bool $creating): array
{
    foreach (['title', 'description', 'authors'] as $field) {
        if (!isset($input[$field]) || !is_string($input[$field])) { throw new InvalidArgumentException('Enter a title, description and author names.'); }
    }
    $authors = array_values(array_filter(array_map('trim', preg_split('/\R/u', $input['authors']) ?: []), static fn ($value) => $value !== ''));
    if (count($authors) < 1 || count($authors) > 20) { throw new InvalidArgumentException('Enter between 1 and 20 authors, one per line.'); }
    foreach ($authors as $name) { required_text($name, 1, 100, 'Author name'); }
    return ['title' => required_text($input['title'], 1, 255, 'Title'),
        'description' => required_text($input['description'], 1, 2000, 'Description'),
        'authors' => $authors, 'quantity' => $creating ? catalog_quantity($input['quantity'] ?? null) : 0];
}

function catalog_decode_cover(string $bytes): GdImage
{
    if ($bytes === '' || strlen($bytes) > 2097152) { throw new InvalidArgumentException('Cover must be no larger than 2 MB.'); }
    $size = @getimagesizefromstring($bytes);
    if (!$size || !in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true) || $size[0] > 4000 || $size[1] > 4000) {
        throw new InvalidArgumentException('Choose a valid JPEG or PNG no larger than 4000 × 4000 pixels.');
    }
    $image = @imagecreatefromstring($bytes);
    if (!$image) { throw new InvalidArgumentException('The cover image could not be decoded.'); }
    return $image;
}

function catalog_uploaded_cover(?array $file): ?GdImage
{
    if ($file === null || ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE) { return null; }
    if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('The cover upload failed. Choose the image again (maximum 2 MB).');
    }
    $bytes = file_get_contents($file['tmp_name'], false, null, 0, 2097153);
    if ($bytes === false) { throw new InvalidArgumentException('The cover could not be read.'); }
    return catalog_decode_cover($bytes);
}

function catalog_write_cover(GdImage $image): string
{
    $directory = dirname(__DIR__) . '/private/covers';
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) { throw new RuntimeException('Cover storage unavailable'); }
    $name = 'upload-' . bin2hex(random_bytes(16)) . '.jpg';
    $path = $directory . '/' . $name;
    // Flatten transparency onto paper white and discard metadata by re-encoding.
    $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
    imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
    imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
    if (!imagejpeg($canvas, $path, 88)) { if (is_file($path)) { unlink($path); } throw new RuntimeException('Cover save failed'); }
    return $name;
}

function catalog_manage(PDO $pdo, array $input, mixed $token, ?array $upload = null): int
{
    for ($attempt = 0; ; $attempt++) {
        try { return catalog_manage_once($pdo, $input, $token, $upload); }
        catch (PDOException $exception) {
            if ($attempt >= 2 || !in_array((int) ($exception->errorInfo[1] ?? 0), [1205, 1213], true)) { throw $exception; }
            usleep(random_int(10000, 50000));
        }
    }
}

function catalog_manage_once(PDO $pdo, array $input, mixed $token, ?array $upload = null): int
{
    if (!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    foreach (['role', 'account_status', 'available_copies', 'cover_path'] as $forged) {
        if (array_key_exists($forged, $input)) { throw new InvalidArgumentException('Unexpected form fields. Refresh and try again.'); }
    }
    $action = $input['action'] ?? '';
    if (!is_string($action) || !in_array($action, ['create', 'edit', 'add_copies', 'cover', 'archive_book', 'archive_copy'], true)) {
        throw new InvalidArgumentException('Choose a valid catalog action.');
    }
    $actorId = $_SESSION['user_id'] ?? null;
    if (!is_int($actorId)) { throw new DomainException('Sign in as a librarian.'); }
    $bookId = $action === 'create' ? 0 : filter_var($input['book_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($bookId === false) { throw new InvalidArgumentException('Choose an existing book.'); }
    $data = in_array($action, ['create', 'edit'], true) ? catalog_book_input($input, $action === 'create') : null;
    $quantity = $action === 'add_copies' ? catalog_quantity($input['quantity'] ?? null) : ($data['quantity'] ?? 0);
    $newCover = null;
    // Authenticate before spending resources on image decoding, then recheck under lock.
    require_active_librarian(current_user($pdo));
    $pdo->exec("SET time_zone = '+00:00'");
    if ($action === 'create' || $action === 'cover') {
        $image = catalog_uploaded_cover($upload);
        if ($action === 'cover' && $image === null) { throw new InvalidArgumentException('Choose a new cover image.'); }
        if ($image !== null) { $newCover = catalog_write_cover($image); }
    }
    try {
        $pdo->beginTransaction();
        $actorQuery = $pdo->prepare('SELECT role, account_status, session_version FROM users WHERE user_id = ? FOR UPDATE');
        $actorQuery->execute([$actorId]);
        $actor = $actorQuery->fetch() ?: null;
        require_active_librarian($actor);
        if ((int) $actor['session_version'] !== ($_SESSION['session_version'] ?? null)) { throw new DomainException('Your session expired. Sign in again.'); }
        if ($action === 'create') {
            $pdo->prepare('INSERT INTO books (title, description, cover_path) VALUES (?, ?, ?)')->execute([$data['title'], $data['description'], $newCover]);
            $bookId = (int) $pdo->lastInsertId();
        } else {
            $statement = $pdo->prepare('SELECT book_id, archived_at FROM books WHERE book_id = ? FOR UPDATE');
            $statement->execute([$bookId]);
            $book = $statement->fetch();
            if (!$book || $book['archived_at'] !== null) { throw new InvalidArgumentException('This book is archived or no longer available to edit.'); }
        }
        if ($data !== null) {
            if ($action === 'edit') { $pdo->prepare('UPDATE books SET title = ?, description = ? WHERE book_id = ?')->execute([$data['title'], $data['description'], $bookId]); }
            $pdo->prepare('DELETE FROM book_authors WHERE book_id = ?')->execute([$bookId]);
            foreach ($data['authors'] as $index => $name) {
                // Names alone do not establish author identity; preserve old author rows/history.
                $pdo->prepare('INSERT INTO authors (author_name) VALUES (?)')->execute([$name]);
                $authorId = (int) $pdo->lastInsertId();
                $pdo->prepare('INSERT INTO book_authors (book_id, author_id, credit_order) VALUES (?, ?, ?)')->execute([$bookId, $authorId, $index + 1]);
            }
        }
        if ($action === 'create' || $action === 'add_copies') {
            for ($i = 0; $i < $quantity; $i++) {
                $code = 'CP-' . strtoupper(bin2hex(random_bytes(12)));
                $pdo->prepare('INSERT INTO book_copies (book_id, copy_code) VALUES (?, ?)')->execute([$bookId, $code]);
            }
        }
        if ($action === 'cover') { $pdo->prepare('UPDATE books SET cover_path = ? WHERE book_id = ?')->execute([$newCover, $bookId]); }
        if (in_array($action, ['archive_book', 'archive_copy'], true)) {
            $copies = $pdo->prepare('SELECT copy_id, archived_at FROM book_copies WHERE book_id = ? ORDER BY copy_id FOR UPDATE');
            $copies->execute([$bookId]);
            $rows = $copies->fetchAll();
            $copyId = $action === 'archive_copy' ? filter_var($input['copy_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;
            if ($action === 'archive_copy' && (!$copyId || !array_filter($rows, static fn ($row) => (int) $row['copy_id'] === $copyId && $row['archived_at'] === null))) {
                throw new InvalidArgumentException('Choose an active copy of this book.');
            }
            $requests = $pdo->prepare('SELECT request_id, decision FROM borrow_requests WHERE book_id = ? ORDER BY request_id FOR UPDATE');
            $requests->execute([$bookId]);
            foreach ($requests->fetchAll() as $request) {
                if ($action === 'archive_book' && $request['decision'] === 'pending') { throw new InvalidArgumentException('Resolve pending requests before archiving this book.'); }
            }
            $loans = $pdo->prepare('SELECT l.loan_id, l.copy_id, l.returned_at, l.cancelled_at FROM loans l JOIN book_copies c ON c.copy_id = l.copy_id WHERE c.book_id = ? ORDER BY l.loan_id FOR UPDATE');
            $loans->execute([$bookId]);
            foreach ($loans->fetchAll() as $loan) {
                if (($copyId === null || (int) $loan['copy_id'] === $copyId) && $loan['returned_at'] === null && $loan['cancelled_at'] === null) {
                    throw new InvalidArgumentException('Resolve the outstanding allocation or loan before archiving.');
                }
            }
            if ($copyId !== null) { $pdo->prepare('UPDATE book_copies SET archived_at = UTC_TIMESTAMP() WHERE copy_id = ?')->execute([$copyId]); }
            else { $pdo->prepare('UPDATE books SET archived_at = UTC_TIMESTAMP() WHERE book_id = ?')->execute([$bookId]); }
        }
        record_audit_event($pdo, $actorId, 'catalog.' . $action, 'books', $bookId,
            $action === 'archive_copy' ? 'Archived copy ' . $copyId . '.' : 'Catalog action: ' . $action . '; copies added: ' . $quantity . '.');
        $pdo->commit();
        return $bookId;
    } catch (Throwable $exception) {
        $rolledBack = false;
        if ($pdo->inTransaction()) { $pdo->rollBack(); $rolledBack = true; }
        if ($rolledBack && $newCover !== null) { unlink(dirname(__DIR__) . '/private/covers/' . $newCover); }
        // On an ambiguous connection failure preserve the new file for operator reconciliation.
        throw $exception;
    }
}
