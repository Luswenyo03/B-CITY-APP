<?php
// Initialize $contacts and $clients arrays if not already defined to avoid errors
$contacts = $contacts ?? [];
$clients = $clients ?? [];
?>

<!-- Container for header messages, initially hidden -->
<div id="header-message" class="info-message" style="display: none;"></div>

<!-- Page title -->
<h2 id="pageTitle">Contacts List</h2>

<!-- Display any message from backend in a safe manner -->
<?php if (!empty($message)): ?>
    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<!-- Main container for the contacts list -->
<div id="contactListContainer">

    <!-- If no contacts found, show message -->
    <?php if (empty($contacts)): ?>
        <p>No contact(s) found</p>
    <?php else: ?>
        <!-- Contacts table -->
        <table>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th class="center">No. of Linked Clients</th>
                <th>Action</th>
            </tr>

            <!-- Loop through each contact -->
            <?php foreach ($contacts as $contact): ?>
                <tr class="contact-row"
                    data-contact-id="<?php echo $contact['id']; ?>"
                    data-contact-name="<?php echo htmlspecialchars($contact['name']); ?>"
                    data-contact-surname="<?php echo htmlspecialchars($contact['surname']); ?>">
                    <!-- Display surname and name -->
                    <td><?php echo htmlspecialchars($contact['surname'] . ' ' . $contact['name']); ?></td>
                    <!-- Display email -->
                    <td><?php echo htmlspecialchars($contact['email']); ?></td>
                    <!-- Display number of linked clients -->
                    <td class="center"><?php echo $contact['client_count']; ?></td>
                    <td>
                        <!-- Button to toggle link clients form -->
                        <button type="button" onclick="toggleLinkClients(<?php echo $contact['id']; ?>); event.stopPropagation();">Link Clients</button>
                        |
                        <!-- Button to view detailed contact info -->
                        <button type="button" class="view-contact-btn"
                            data-contact-id="<?php echo $contact['id']; ?>"
                            data-contact-name="<?php echo htmlspecialchars($contact['name']); ?>"
                            data-contact-surname="<?php echo htmlspecialchars($contact['surname']); ?>">
                            View
                        </button>
                    </td>
                </tr>

                <!-- Hidden row for linking clients form -->
                <tr id="linkClientsRow_<?php echo $contact['id']; ?>" class="hidden">
                    <td colspan="4">
                        <form method="POST" action="?controller=contact&action=linkClients">
                            <!-- Hidden field to store contact ID -->
                            <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                            <label>Select clients to link:</label><br>
                            <!-- Multi-select box to pick clients -->
                            <select name="client_ids[]" multiple size="5" style="width: 100%;">
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['id'] ?>">
                                        <?= htmlspecialchars($client['name']) ?> - <?= htmlspecialchars($client['client_code']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                            <!-- Submit button to link selected clients -->
                            <button type="submit">Link Selected Clients</button>
                            <!-- Cancel button to hide the form -->
                            <button type="button" onclick="toggleLinkClients(<?php echo $contact['id']; ?>)">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Link to create a new contact -->
    <a href="?controller=contact&action=create">Create New Contact</a>
</div>

<!-- Contact Details section (hidden by default) -->
<div id="contactDetails" class="hidden" style="margin-top: 30px;">
    <!-- Button to go back to contacts list -->
    <button id="backToContactListBtn" style="margin-bottom: 10px;">&larr; Back to Contacts List</button>

    <h3>Contact Details</h3>

    <!-- Tabs to switch between general info and linked clients -->
    <div>
        <button class="tablink" onclick="openTab(event, 'ContactGeneralTab')">General</button>
        <button class="tablink" onclick="openTab(event, 'ContactClientsTab')">Clients</button>
    </div>

    <!-- General info tab content -->
    <div id="ContactGeneralTab" class="tab" style="display: none; margin-top:10px;">
        <label>Surname: <input type="text" id="detailSurname" readonly></label><br>
        <label>Name: <input type="text" id="detailName" readonly></label><br>
        <label>Email: <input type="text" id="detailEmail" readonly></label><br>
    </div>

    <!-- Linked clients tab content -->
    <div id="ContactClientsTab" class="tab" style="display: none; margin-top:10px;">
        <table border="1" cellpadding="5" style="width:100%;">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Client Code</th>
           
                </tr>
            </thead>
            <!-- This tbody will be populated dynamically with linked clients -->
            <tbody id="linkedClientsList"></tbody>
        </table>
    </div>
</div>











<script>
// Toggles the display of the client linking form for a specific contact
function toggleLinkClients(contactId) {
    const row = document.getElementById('linkClientsRow_' + contactId);
    if (row) {
        row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row';
    }
}

// Opens a specific tab and highlights the active tab button
function openTab(evt, tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab').forEach(tab => tab.style.display = 'none');

    // Show the selected tab content
    document.getElementById(tabName).style.display = 'block';

    // Remove active class from all tab buttons
    document.querySelectorAll('.tablink').forEach(btn => btn.classList.remove('active'));

    // Add active class to clicked tab button
    evt.currentTarget.classList.add('active');
}

// Show detailed info for a contact and fetch linked clients via AJAX
function showContactDetails(contactId, name, surname, email) {
    // Hide the contacts list and create link
    document.getElementById('contactListContainer').style.display = 'none';
    document.querySelector('a[href="?controller=contact&action=create"]').style.display = 'none';

    // Show the contact details container
    const details = document.getElementById('contactDetails');
    details.classList.remove('hidden');

    // Fetch linked clients for the contact from the backend
    fetch(`?controller=contact&action=getLinkedClients&contact_id=${contactId}`)
        .then(res => res.json())
        .then(data => {
            const contact = data.contact;
            const clients = data.clients;

            // Populate general info fields
            document.getElementById('detailSurname').value = contact.surname;
            document.getElementById('detailName').value = contact.name;
            document.getElementById('detailEmail').value = contact.email;

            // Populate linked clients table
            const clientList = document.getElementById('linkedClientsList');
            clientList.innerHTML = '';
            if (clients.length) {
                clients.forEach(client => {
                    clientList.innerHTML += `
                        <tr>
                            <td>${client.name}</td>
                            <td>${client.client_code}</td>
                            <td>
                                <a href="#" class="unlink-client" data-contact-id="${contactId}" data-client-id="${client.id}">Unlink</a>
                            </td>
                        </tr>
                    `;
                });
                // Bind click handlers for unlink actions
                bindUnlinkClientLinks();
            } else {
                // Display message if no linked clients
                clientList.innerHTML = '<tr><td colspan="3">No clients linked.</td></tr>';
            }
        });
}

// Attach event listeners to unlink client links to handle unlinking via AJAX
function bindUnlinkClientLinks() {
    document.querySelectorAll('.unlink-client').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const contactId = this.getAttribute('data-contact-id');
            const clientId = this.getAttribute('data-client-id');

            // Send unlink request to backend
            fetch('?controller=contact&action=unlinkContact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `contact_id=${contactId}&client_id=${clientId}`
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    // Refresh contact details on success
                    showContactDetails(contactId);
                } else {
                    alert('Failed to unlink client.');
                }
            });
        });
    });
}

// Setup event listeners after DOM has fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Add click event on all 'View' buttons to open contact details
    document.querySelectorAll('.view-contact-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const contactId = btn.getAttribute('data-contact-id');
            const name = btn.getAttribute('data-contact-name');
            const surname = btn.getAttribute('data-contact-surname');

            // Email is extracted from the second cell in the row
            const email = btn.closest('tr').querySelector('td:nth-child(2)').textContent;

            // Show the details and open the General tab by default
            showContactDetails(contactId, name, surname, email);
            openTab({ currentTarget: document.querySelector('.tablink') }, 'ContactGeneralTab');
        });
    });

    // Back button event to return to the contacts list
    document.getElementById('backToContactListBtn').addEventListener('click', () => {
        // Hide details, show list and create link
        document.getElementById('contactDetails').classList.add('hidden');
        document.getElementById('contactListContainer').style.display = '';
        document.querySelector('a[href="?controller=contact&action=create"]').style.display = '';
        document.getElementById('pageTitle').textContent = 'Contacts List';
    });
});
</script>
