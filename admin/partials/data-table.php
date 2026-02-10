<?php
/**
 * Reusable Data Table Component
 * Renders tables with sortable columns and action buttons
 *
 * @param array $columns Column definitions ['key' => 'Label']
 * @param array $data Data rows (associative arrays)
 * @param array $actions Action buttons configuration
 */

function renderDataTable($columns, $data, $actions = []) {
    ?>
    <div class="table-wrapper">
        <table id="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $label): ?>
                        <th class="sortable" data-column="<?= htmlspecialchars($key) ?>">
                            <?= htmlspecialchars($label) ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if (!empty($actions)): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= count($columns) + (!empty($actions) ? 1 : 0) ?>" class="text-center text-muted">
                            No data available
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($columns as $key => $label): ?>
                                <td>
                                    <?php
                                    $value = $row[$key] ?? '';

                                    // Special formatting for common column types
                                    if ($key === 'is_admin' || $key === 'is_available') {
                                        echo $value ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>';
                                    } elseif ($key === 'status') {
                                        $statusClass = '';
                                        switch ($value) {
                                            case 'open':
                                            case 'pending':
                                                $statusClass = 'badge-warning';
                                                break;
                                            case 'in_progress':
                                                $statusClass = 'badge-info';
                                                break;
                                            case 'completed':
                                            case 'accepted':
                                                $statusClass = 'badge-success';
                                                break;
                                            case 'cancelled':
                                            case 'rejected':
                                                $statusClass = 'badge-danger';
                                                break;
                                            default:
                                                $statusClass = 'badge-secondary';
                                        }
                                        echo '<span class="badge ' . $statusClass . '">' . htmlspecialchars($value) . '</span>';
                                    } elseif ($key === 'budget' || $key === 'hourly_rate' || $key === 'proposed_rate') {
                                        echo '$' . number_format((float)$value, 2);
                                    } elseif (strpos($key, 'created_at') !== false || strpos($key, 'updated_at') !== false) {
                                        echo date('M d, Y', strtotime($value));
                                    } elseif (strlen($value) > 50 && (strpos($key, 'bio') !== false || strpos($key, 'message') !== false || strpos($key, 'description') !== false)) {
                                        echo '<span class="truncate" title="' . htmlspecialchars($value) . '">' . htmlspecialchars(substr($value, 0, 50)) . '...</span>';
                                    } else {
                                        echo htmlspecialchars($value);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>

                            <?php if (!empty($actions)): ?>
                                <td class="actions">
                                    <div class="btn-group">
                                        <?php foreach ($actions as $action): ?>
                                            <?php
                                            $type = $action['type'] ?? 'button';
                                            $label = $action['label'] ?? 'Action';
                                            $class = $action['class'] ?? 'btn-secondary';
                                            $onclick = $action['onclick'] ?? '';
                                            $href = $action['href'] ?? '#';

                                            // Replace {id} placeholder with actual row ID
                                            if (isset($row['id'])) {
                                                $onclick = str_replace('{id}', $row['id'], $onclick);
                                                $href = str_replace('{id}', $row['id'], $href);
                                            }

                                            // Replace other placeholders
                                            foreach ($row as $k => $v) {
                                                $onclick = str_replace('{' . $k . '}', $v, $onclick);
                                                $href = str_replace('{' . $k . '}', $v, $href);
                                            }

                                            if ($type === 'link'):
                                            ?>
                                                <a href="<?= htmlspecialchars($href) ?>" class="btn btn-small <?= htmlspecialchars($class) ?>">
                                                    <?= htmlspecialchars($label) ?>
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-small <?= htmlspecialchars($class) ?>" onclick="<?= htmlspecialchars($onclick) ?>">
                                                    <?= htmlspecialchars($label) ?>
                                                </button>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
