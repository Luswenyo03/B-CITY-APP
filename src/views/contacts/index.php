<?php
$contacts = $contacts ?? [];
?>
<h2>Contacts List</h2>

<?php if (empty($contacts)): ?>
    <p>No contact(s) found</p>
<?php else: ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th class="center">No. of Linked Clients</th>
            <th>Action</th>
        </tr>
        <?php foreach ($contacts as $contact): ?>
            <tr id="row_<?php echo $contact['id']; ?>_<?php echo $contact['client_count'] > 0 ? $contact['id'] : 0; ?>">
                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                <td class="center"><?php echo $contact['client_count']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="?controller=contact&action=create">Create New Contact</a>
