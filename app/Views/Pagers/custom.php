<div class="pagination">
    <?php if ($pager->hasPrevious()) : ?>
        <a href="<?= $pager->getFirst() ?>" class="page-link">Первая</a>
        <a href="<?= $pager->getPrevious() ?>" class="page-link">Назад</a>
    <?php endif ?>

    <?php foreach ($pager->links() as $link) : ?>
        <a href="<?= $link['uri'] ?>" class="page-link <?= $link['active'] ? 'active' : '' ?>">
            <?= $link['title'] ?>
        </a>
    <?php endforeach ?>

    <?php if ($pager->hasNext()) : ?>
        <a href="<?= $pager->getNext() ?>" class="page-link">Вперед</a>
        <a href="<?= $pager->getLast() ?>" class="page-link">Последняя</a>
    <?php endif ?>
</div>