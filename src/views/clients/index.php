<?php
$clients = $clients ?? [];
$contacts = $contacts ?? [];
?>

<div id="header-message" class="info-message" style="display: none;"></div>
<h2>Clients List</h2>
<?php if (!empty($message)): ?>
    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (empty($clients)): ?>
    <p>No client(s) found</p>
<?php else: ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Client Code</th>
            <th class="center">No. of Linked Contacts</th>
            <th>Action</th>
        </tr>
        <?php foreach ($clients as $client): ?>
            <tr id="row_<?php echo $client['id']; ?>">
                <td><?php echo htmlspecialchars($client['name']); ?></td>
                <td><?php echo htmlspecialchars($client['client_code']); ?></td>
                <td class="center"><?php echo $client['contact_count']; ?></td>
                <td>
                    <a href="#" onclick="toggleLinkContacts(<?php echo $client['id']; ?>); return false;">Link Contacts</a>
                </td>
            </tr>
            <!-- Unlink Contacts Row -->
            <?php if (!empty($client['linked_contacts'])): ?>
                <tr id="unlinkContactsRow_<?php echo $client['id']; ?>" class="hidden">
                    <td colspan="4">
                        <div>Linked Contacts:</div>
                        <ul>
                            <?php foreach ($client['linked_contacts'] as $contact): ?>
                                <li data-contact-id="<?php echo $contact['id']; ?>">
                                    <?php echo htmlspecialchars($contact['name']); ?>
                                    <a href="#" class="unlink-contact" data-client-id="<?php echo $client['id']; ?>" data-contact-id="<?php echo $contact['id']; ?>">Unlink</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            <?php endif; ?>
            <!-- Link Contacts Form -->
            <tr id="linkContactsRow_<?php echo $client['id']; ?>" class="hidden">
                <td colspan="4">
                    <form method="POST" action="?controller=client&action=linkContacts">
                        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                        <label>Select contacts to link:</label><br>
                        <select name="contact_ids[]" multiple size="5" style="width: 100%;">
                            <?php foreach ($contacts as $contact): ?>
                                <option value="<?php echo $contact['id']; ?>">
                                    <?php echo htmlspecialchars($contact['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <br>
                        <button type="submit">Link Selected Contacts</button>
                        <button type="button" onclick="toggleLinkContacts(<?php echo $client['id']; ?>)">Cancel</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="?controller=client&action=create">Create New Client</a>

<a href="?controller=link&action=index">Link New Client</a>


<script>
function toggleLinkContacts(clientId) {
    const row = document.getElementById('linkContactsRow_' + clientId);
    row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row';
}

function toggleUnlinkContacts(clientId) {
    const row = document.getElementById('unlinkContactsRow_' + clientId);
    row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.unlink-contact').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const clientId = this.getAttribute('data-client-id');
            const contactId = this.getAttribute('data-contact-id');
            unlinkItem('client', clientId, contactId);
        });
    });
});
</script>

<style>
.hidden {
    display: none;
}
.info-message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 4px;
}
</style>