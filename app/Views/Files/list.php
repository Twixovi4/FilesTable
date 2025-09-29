<?=$this->extend("templates/default");?>
<?=$this->section("title");?>Файлы<?=$this->endSection();?>

<?=$this->section("content");?>
<button type="button" id="modalUploadButton" class="btn btn-dark mb-3" data-bs-toggle="collapse" data-bs-target="#uploadFormContainer">
    <i class="fas fa-upload me-2"></i>Загрузить файл
</button>

<div class="collapse mb-4" id="uploadFormContainer">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Загрузка Excel файла</h5>
        </div>
        <div class="card-body">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="file" class="form-control" id="excelFile" name="excel_file" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Поддерживаемые форматы: XLSX, XLS, CSV</div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Загрузить
                </button>
                <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#uploadFormContainer">
                    Отмена
                </button>
            </form>
            <div id="uploadMessage" class="mt-2"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Загруженные файлы</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Имя файла</th>
                        <th>Дата загрузки</th>
                        <th>Дата изменения</th>
                        <th>Строк</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?=esc($file['original_name'])?></td>
                        <td><?=date('d.m.Y H:i', strtotime($file['created_at']))?></td>
                        <td><?=date('d.m.Y H:i', strtotime($file['updated_at']))?></td>
                        <td><?=$file['row_count']?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/files/view/<?=$file['id']?>" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/files/download/<?=$file['id']?>" class="btn btn-outline-success">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="/files/export/excel/<?=$file['id']?>" class="btn btn-outline-info">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                                <a href="/files/export/pdf/<?=$file['id']?>" class="btn btn-outline-danger">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button class="btn btn-outline-danger delete-file" data-id="<?=$file['id']?>">
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
                    <a class="page-link" href="/files?page=<?=$i?>"><?=$i?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Вы уверены, что хотите удалить этот файл? Все данные будут потеряны.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Удалить</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: '/files/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#uploadMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#uploadForm')[0].reset();
                    $('#uploadFormContainer').collapse('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    $('#uploadMessage').html('<div class="alert alert-danger">' + response.error + '</div>');
                }
            },
            error: function() {
                $('#uploadMessage').html('<div class="alert alert-danger">Ошибка при загрузке файла</div>');
            }
        });
    });

    $('#uploadFormContainer').on('show.bs.collapse', function () {
        $('#uploadMessage').html('');
    });

    let fileToDelete = null;
    $('.delete-file').on('click', function() {
        fileToDelete = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (fileToDelete) {
            $.ajax({
                url: '/files/delete/' + fileToDelete,
                type: 'POST',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Ошибка при удалении файла: ' + response.error);
                    }
                },
                error: function() {
                    alert('Ошибка при удалении файла');
                }
            });
        }
        $('#deleteModal').modal('hide');
    });
});
</script>
<?=$this->endSection();?>