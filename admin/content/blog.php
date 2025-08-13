<?php
// JOIN + alias nama kategori
$sql = "SELECT blogs.*, categories.name AS category_name
        FROM blogs
        LEFT JOIN categories ON categories.id = blogs.id_category
        ORDER BY blogs.id DESC";
$query = mysqli_query($koneksi, $sql);
$rows  = $query ? mysqli_fetch_all($query, MYSQLI_ASSOC) : [];

function changeIsActive($isActive)
{
  if ((string)$isActive === '1' || (int)$isActive === 1) {
    return "<span class='badge bg-primary'>Publish</span>";
  }
  return "<span class='badge bg-warning'>Draft</span>";
}
?>

<div class="pagetitle">
  <h1>Data Blog</h1>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Data Blog</h5>
          <div class="mb-3 text-end">
            <a href="?page=tambah-blog" class="btn btn-sm btn-success">Tambah</a>
          </div>

          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th style="width:60px">No</th>
                <th style="width:200px">Gambar</th>
                <th>Kategori</th>
                <th>Judul</th>
                <th style="width:120px">Status</th>
                <th style="width:160px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-center">Belum ada data.</td></tr>
              <?php else: ?>
                <?php foreach ($rows as $i => $row): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>

                    <td>
                      <?php if (!empty($row['image'])): ?>
                        <img class="img-fluid" src="uploads/<?= htmlspecialchars($row['image'], ENT_QUOTES); ?>"
                             alt="thumb" style="max-width:180px;height:auto;">
                      <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($row['category_name'] ?? '-', ENT_QUOTES); ?></td>
                    <td><?= htmlspecialchars($row['title'] ?? '', ENT_QUOTES); ?></td>
                    <td><?= changeIsActive($row['is_active'] ?? 0); ?></td>

                    <td>
                      <a href="?page=tambah-blog&edit=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                      <a onclick="return confirm('Apakah anda yakin akan menghapus data ini?')"
                         href="?page=tambah-blog&delete=<?= (int)$row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>

