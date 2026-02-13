<?php
/**
 * Guild Badge Partial
 * Displays a guild badge with icon, name, and rank
 *
 * Required variables:
 * - $guildName: Guild name (string)
 * - $guildColor: Guild color hex code (string)
 * - $rankName: Rank name (string)
 *
 * Optional variables:
 * - $guildIcon: Guild icon emoji/text (default: ⚔️)
 */

$guildIcon = $guildIcon ?? '⚔️';
?>
<div class="guild-badge-primary" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($guildColor); ?>1a 0%, <?php echo htmlspecialchars($guildColor); ?>0d 100%); border-color: <?php echo htmlspecialchars($guildColor); ?>40; color: <?php echo htmlspecialchars($guildColor); ?>">
    <span class="guild-icon"><?php echo htmlspecialchars($guildIcon); ?></span>
    <span class="guild-name"><?php echo htmlspecialchars($guildName); ?></span>
    <span class="guild-rank">
        <?php echo htmlspecialchars($rankName); ?>
    </span>
</div>
