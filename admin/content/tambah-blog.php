<?php
// ====== SETUP ======
$id        = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$titlePage = $id ? "Edit Blog" : "Tambah Blog";
$rowEdit   = null;

// Ambil kategori buat <select>
$cats = [];
$cq = mysqli_query($koneksi, "SELECT id, name FROM categories ORDER BY name ASC");
if ($cq) $cats = mysqli_fetch_all($cq, MYSQLI_ASSOC);

// ====== HAPUS ======
if (isset($_GET['delete'])) {
  $did = intval($_GET['delete']);
  // hapus file gambar
  $qg = mysqli_query($koneksi, "SELECT image FROM blogs WHERE id = $did");
  $rg = mysqli_fetch_assoc($qg);
  if ($rg && !empty($rg['image']) && file_exists(__DIR__."/../uploads/".$rg['image'])) {
    @unlink(__DIR__."/../uploads/".$rg['image']);
  }
  mysqli_query($koneksi, "DELETE FROM blogs WHERE id = $did");
  header("Location: ?page=blog&hapus=berhasil"); exit;
}

// ====== AMBIL DATA SAAT EDIT ======
if ($id) {
  $q = mysqli_query($koneksi, "SELECT * FROM blogs WHERE id = $id");
  $rowEdit = mysqli_fetch_assoc($q);
}

// ====== SIMPAN (INSERT/UPDATE) ======
if (isset($_POST['simpan'])) {
  $title      = $_POST['title'] ?? '';
  $id_cat     = intval($_POST['id_category'] ?? 0);
  $content    = $_POST['content'] ?? '';
  $is_active  = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

  // default: pakai gambar lama jika tidak upload
  $image_name = $rowEdit['image'] ?? '';

  // upload gambar jika ada
  if (!empty($_FILES['image']['name'])) {
    $tmp  = $_FILES['image']['tmp_name'];
    $mime = @mime_content_type($tmp);
    $ok   = ["image/png","image/jpg","image/jpeg","image/webp"];
    if (!in_array($mime, $ok)) {
      echo "<div class='alert alert-danger'>Format gambar harus PNG/JPG/JPEG/WEBP.</div>";
    } else {
      $upDir = __DIR__."/../uploads";
      if (!is_dir($upDir)) mkdir($upDir, 0777, true);
      $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
      $new   = time().'-'.uniqid().'.'.$ext;
      if (move_uploaded_file($tmp, $upDir.'/'.$new)) {
        // hapus lama saat edit
        if (!empty($image_name) && file_exists($upDir.'/'.$image_name)) {
          @unlink($upDir.'/'.$image_name);
        }
        $image_name = $new;
      } else {
        echo "<div class='alert alert-danger'>Upload gambar gagal.</div>";
      }
    }
  }

  // escape
  $t  = mysqli_real_escape_string($koneksi, $title);
  $c  = mysqli_real_escape_string($koneksi, $content);
  $im = mysqli_real_escape_string($koneksi, $image_name);

  if ($id) {
    // UPDATE
    $sql = "UPDATE blogs SET 
              id_category = $id_cat,
              title = '$t',
              content = '$c',
              image = '$im',
              is_active = $is_active
            WHERE id = $id";
    if (mysqli_query($koneksi, $sql)) {
      header("Location: ?page=blog&ubah=berhasil"); exit;
    }
  } else {
    // INSERT
    $sql = "INSERT INTO blogs (id_category, title, content, image, is_active)
            VALUES ($id_cat, '$t', '$c', '$im', $is_active)";
    if (mysqli_query($koneksi, $sql)) {
      header("Location: ?page=blog&tambah=berhasil"); exit;
    }
  }
}
?>

<div class="pagetitle">
  <h1><?= $titlePage; ?></h1>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title"><?= $titlePage; ?></h5>

          <form method="post" enctype="multipart/form-data">

            <div class="mb-3">
              <label class="form-label">Kategori</label>
              <select name="id_category" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($cats as $cat): ?>
                  <option value="<?= (int)$cat['id']; ?>"
                    <?= isset($rowEdit['id_category']) && (int)$rowEdit['id_category'] === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Judul</label>
              <input type="text" name="title" class="form-control"
                     value="<?= htmlspecialchars($rowEdit['title'] ?? '', ENT_QUOTES); ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Konten</label>
              <textarea id="summernote" name="content" class="form-control" rows="8"
                        placeholder="Tulis konten blog di sini..."><?= htmlspecialchars($rowEdit['content'] ?? '', ENT_QUOTES); ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Gambar</label>
              <input type="file" name="image" class="form-control">
              <?php if (!empty($rowEdit['image'])): ?>
                <img class="mt-2" src="uploads/<?= htmlspecialchars($rowEdit['image'], ENT_QUOTES); ?>" alt="" width="140">
              <?php endif; ?>
              <small class="text-muted d-block mt-1">Format: PNG/JPG/JPEG/WEBP. Disarankan landscape.</small>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                <?= !empty($rowEdit['is_active']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_active">Publish</label>
            </div>

            <div class="mt-2">
              <button class="btn btn-primary" type="submit" name="simpan">Simpan</button>
              <a href="?page=blog" class="btn btn-secondary">← Kembali</a>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</section>
