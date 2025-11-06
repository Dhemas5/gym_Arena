<!-- Step 2: Pilih Kelas -->
<div id="step2" class="section-card hidden">
  <h2 class="section-title">Pilih Kelas (Opsional)</h2>
  <div class="alert">
    💡 Pilih kelas tambahan yang ingin Anda ikuti. Anda bisa melewati langkah ini jika hanya ingin gym biasa.
  </div>

  <div class="class-grid">
    <?php foreach ($class_options as $option): ?>
      <div class="class-option" onclick="toggleClass('<?php echo $option['value']; ?>', <?php echo $option['price']; ?>)">
        <input type="checkbox" name="class" value="<?php echo $option['value']; ?>">
        <div class="class-name"><?php echo $option['name']; ?></div>
        <div class="class-price">Rp <?php echo number_format($option['price'], 0, ',', '.'); ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="display: flex; gap: 15px;">
    <button class="btn btn-primary" onclick="nextStep(1)" style="background: rgba(66, 165, 245, 0.2);">← Kembali</button>
    <button class="btn btn-primary" onclick="nextStep(3)">Lanjut ke Data Diri</button>
  </div>
</div>