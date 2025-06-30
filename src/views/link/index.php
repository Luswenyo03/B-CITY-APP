<?php
$clients = $clients ?? [];
$contacts = $contacts ?? [];
?>

<h2>Link New Client</h2>

<div>
    <button class="tablink" onclick="openTab(event, 'General')">General</button>
    <button class="tablink" onclick="openTab(event, 'Contact')">Contact</button>
</div>

<div id="General" class="tab" style="display:none;">
    <h3>General Info</h3>
    <form id="generalForm">
        <label>Name: <input type="text" name="name" id="clientName" required></label><br>
        <label>Client Code: <input type="text" name="client_code" id="clientCode" readonly></label><br>
        <button type="submit">Generate Code</button>
    </form>
</div>

<div id="Contact" class="tab" style="display:none;">
    <h3>Available Contacts</h3>
    <table border="1" cellpadding="5">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $contact): ?>
                <tr>
                    <td><?= htmlspecialchars($contact['name']) ?></td>
                    <td><?= htmlspecialchars($contact['email']) ?></td>
                    <td>
                        <!-- Link Contact button -->
                        <form method="POST" action="?controller=link&action=linkContact" style="display:inline;">
                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                            <button type="submit">Link Contact</button>
                        </form>

                        <!-- Unlink Contact link -->
                        <?php foreach ($clients as $client): ?>
                            <a href="#" 
                               class="unlink-contact" 
                               data-client-id="<?= htmlspecialchars($client['id']) ?>" 
                               data-contact-id="<?= htmlspecialchars($contact['id']) ?>"
                               style="color: red; margin-left: 8px;">
                               Unlink
                            </a>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function openTab(evt, tabName) {
    document.querySelectorAll('.tab').forEach(tab => {
        tab.style.display = 'none';
    });
    document.getElementById(tabName).style.display = 'block';
    document.querySelectorAll('.tablink').forEach(btn => {
        btn.classList.remove('active');
    });
    evt.currentTarget.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    // Open General tab by default
    document.querySelector('.tablink').click();

    // Handle General form submission via AJAX
    document.getElementById('generalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('clientName').value.trim();
        if (!name) return;

        fetch(`?controller=link&action=checkOrCreateClient`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `name=${encodeURIComponent(name)}`
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('clientCode').value = data.client_code || '';
        })
        .catch(() => {
            document.getElementById('clientCode').value = '';
        });
    });

    // Handle Unlink click
    function unlinkItem(clientId, contactId) {
        fetch(`?controller=link&action=unlinkContact&client_id=${clientId}&contact_id=${contactId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        }).then(response => {
            if (response.ok) location.reload();
        });
    }

    document.querySelectorAll('.unlink-contact').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const clientId = this.getAttribute('data-client-id');
            const contactId = this.getAttribute('data-contact-id');
            unlinkItem(clientId, contactId);
        });
    });
});
</script>

<style>
.tab { margin-top: 15px; }
.tablink.active { font-weight: bold; }
</style>
