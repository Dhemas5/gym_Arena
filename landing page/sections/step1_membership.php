<!-- Step 1: Pilih Membership -->
<div id="step1" class="section-card">
  <h2 class="section-title">Pilih Paket Membership</h2>
  <div class="membership-grid">
    <?php foreach ($membership_options as $option): ?>
      <div class="membership-option" onclick="selectMembership('<?php echo $option['value']; ?>', <?php echo $option['price']; ?>)">
        <input type="radio" name="membership" value="<?php echo $option['value']; ?>">
        <div class="membership-name"><?php echo $option['name']; ?></div>
        <div class="membership-price">Rp <?php echo number_format($option['price'], 0, ',', '.'); ?></div>
        <div class="membership-period"><?php echo $option['period']; ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <button class="btn btn-primary" onclick="nextStep(2)" id="btnStep1" disabled>Lanjut ke Pilih Kelas</button>
</div>