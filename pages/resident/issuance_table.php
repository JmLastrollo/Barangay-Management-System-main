<td>
    <?php if ($row['status'] == 'Ready for Pickup' && $row['payment_status'] == 'Paid'): ?>
        
        <?php if ($row['download_count'] == 0 && strtotime($row['print_expiry']) > time()): ?>
            <a href="../../backend/resident_print_secure.php?token=<?= $row['print_token'] ?>" 
               target="_blank" 
               class="btn btn-success btn-sm"
               onclick="return confirm('Warning: You can only open/print this ONCE. Ensure your printer is ready. Proceed?');">
               <i class="bi bi-printer"></i> Print Now
            </a>
            <br>
            <small class="text-danger" style="font-size: 10px;">Expires: <?= date('M d, h:i A', strtotime($row['print_expiry'])) ?></small>
        
        <?php elseif ($row['download_count'] > 0): ?>
            <span class="badge bg-secondary">Already Printed</span>
        
        <?php else: ?>
            <span class="badge bg-danger">Link Expired</span>
        <?php endif; ?>

    <?php else: ?>
        <span class="text-muted">Waiting for Approval/Payment</span>
    <?php endif; ?>
</td>