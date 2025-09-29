<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр: <?=esc($file['original_name'])?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?=esc($file['original_name'])?></h1>
            <a href="/files" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Назад
            </a>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Данные файла</h5>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addRowModal">
                    <i class="fas fa-plus me-1"></i>Добавить строку
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <?php foreach ($headers as $header): ?>
                                <th><?=esc($header)?></th>
                                <?php endforeach; ?>
                                <th width="100">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row):
    $rowData = json_decode($row['row_data'], true);
    ?>
		                            <tr id="row-<?=$row['id']?>">
		                                <?php foreach ($rowData as $value): ?>
		                                <td><?=esc($value)?></td>
		                                <?php endforeach; ?>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-row"
                                                data-id="<?=$row['id']?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRowModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-row"
                                                data-id="<?=$row['id']?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pager['total'] > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pager['total']; $i++): ?>
                        <li class="page-item <?=$i == $pager['current'] ? 'active' : ''?>">
                            <a class="page-link" href="/files/view/<?=$file['id']?>?page=<?=$i?>"><?=$i?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addRowModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить новую строку</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addRowForm">
                    <div class="modal-body">
                        <?php foreach ($headers as $header): ?>
                        <div class="mb-3">
                            <label class="form-label"><?=esc($header)?></label>
                            <input type="text" class="form-control" name="<?=esc($header)?>" required>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRowModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать строку</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editRowForm">
                    <input type="hidden" name="row_id" id="editRowId">
                    <div class="modal-body" id="editRowFields">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#addRowForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '/files/add-row/<?=$file['id']?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#addRowModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + response.error);
                    }
                },
                error: function() {
                    alert('Ошибка при добавлении строки');
                }
            });
        });

        $('.edit-row').on('click', function() {
            var rowId = $(this).data('id');
            var rowData = $('#row-' + rowId + ' td').map(function() {
                return $(this).text().trim();
            }).get();

            $('#editRowId').val(rowId);
            $('#editRowFields').empty();
            <?php foreach ($headers as $index => $header): ?>
            $('#editRowFields').append(
                '<div class="mb-3">' +
                '<label class="form-label"><?=esc($header)?></label>' +
                '<input type="text" class="form-control" name="<?=esc($header)?>" value="' + rowData[<?=$index?>] + '" required>' +
                '</div>'
            );
            <?php endforeach; ?>
        });

        $('#editRowForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var rowId = $('#editRowId').val();

            $.ajax({
                url: '/files/update-row/' + rowId,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editRowModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + response.error);
                    }
                },
                error: function() {
                    alert('Ошибка при обновлении строки');
                }
            });
        });

        $('.delete-row').on('click', function() {
            if (!confirm('Вы уверены, что хотите удалить эту строку?')) {
                return;
            }

            var rowId = $(this).data('id');

            $.ajax({
                url: '/files/delete-row/' + rowId,
                type: 'POST',
                success: function(response) {
                    if (response.success) {
                        $('#row-' + rowId).remove();
                        location.reload();
                    } else {
                        alert('Ошибка: ' + response.error);
                    }
                },
                error: function() {
                    alert('Ошибка при удалении строки');
                }
            });
        });
    });
    </script>
</body>
</html>